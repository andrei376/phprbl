<?php /** @noinspection PhpUnusedAliasInspection */

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


use App\Models\DefineList;
use App\Models\White;
use App\Models\Grey;
use App\Models\Black;
use Illuminate\Support\Facades\URL;

/*
 *
 *
INSERT INTO `rbl8`.`blacks`(`id`,`ip1`, `ip2`, `ip3`, `ip4`, `iplong`, `mask`, `inetnum`, `netname`, `country`, `orgname`, `geoipcountry`, `delete`, `active`, `date_added`, `last_check`, `checked`) SELECT `id`,`ip1`, `ip2`, `ip3`, `ip4`, `iplong`, `mask`, `inetnum`, `netname`, `country`, `orgname`, `geoipcountry`, `delete`, `active`, `date_added`, `last_check`, `checked` FROM `rbl`.`black`;

INSERT INTO `rbl8`.`whites`(`id`,`ip1`, `ip2`, `ip3`, `ip4`, `iplong`, `mask`, `inetnum`, `netname`, `country`, `orgname`, `geoipcountry`, `delete`, `active`, `date_added`, `last_check`, `checked`) SELECT `id`,`ip1`, `ip2`, `ip3`, `ip4`, `iplong`, `mask`, `inetnum`, `netname`, `country`, `orgname`, `geoipcountry`, `delete`, `active`, `date_added`, `last_check`, `checked` FROM `rbl`.`white`;

INSERT INTO `rbl8`.`greys`(`id`,`ip1`, `ip2`, `ip3`, `ip4`, `iplong`, `mask`, `inetnum`, `netname`, `country`, `orgname`, `geoipcountry`, `delete`, `active`, `date_added`, `last_check`, `checked`) SELECT `id`,`ip1`, `ip2`, `ip3`, `ip4`, `iplong`, `mask`, `inetnum`, `netname`, `country`, `orgname`, `geoipcountry`, `delete`, `active`, `date_added`, `last_check`, `checked` FROM `rbl`.`grey`;


INSERT INTO `rbl8`.`hits`(`list`, `list_id`, `year`, `month`, `day`, `count`) SELECT CONCAT('App\\Models\\',`lista`), `lista_ip`, `an`, `luna`, `zi`, `count` FROM `rbl`.`hits`;

INSERT INTO `rbl8`.`rbl_logs`(`user`, `date`, `type`, `read`, `message`) SELECT `user`, `data`, `type`, `citit`, `mesaj` FROM `rbl`.`Log`;
 */


class Rbl4
{
    protected static $lists4 = [
        'White' => 'White',
        'Grey' => 'Grey',
        'Black' => 'Black'
    ];

    public function stats(): array
    {
        $lists = $this->getLists();

        $data = [];

        foreach ($lists as $list) {
            $model = app('App\Models\\' . $list);

            $data[$list] = [
                'count' => $model::countIp(),
                'sync' => DefineList::isSync($list),

                'check_count' => $model::checkCount(),
                'netname_count' => $model::netnameCount(),
                'delete_count' => $model::deleteCount(),
                'inactive_count' => $model::inactiveCount()
            ];
        }

        return $data;
    }

    public static function getLists(): array
    {
        return self::$lists4;
    }

    public function hostnameRange($iplong, $mask): string
    {
        $ip = long2ip($iplong);
        $cidr = $ip.'/'.$mask;

        $result = '';

        if ($range = $this->getRange($cidr)) {
            $hostname = [
                'low' => '',
                'high' => ''
            ];

            if ($host = $this->getHostByIp($range['low'])) {
                $hostname['low'] = $host;
            }

            if ($host = $this->getHostByIp($range['high'])) {
                $hostname['high'] = $host;
            }

            $result = ': '.$range['low'].'->'.$range['high'].' ('.$hostname['low'].'->'.$hostname['high'].')';
        }

        return $result;
    }

    public function getOptimalRange($ipNum, $ipEndNum): ?array
    {
        $optimalRange = null;

        for ($prefixSize = 32; $prefixSize >= 0; $prefixSize--) {
            $maskRange = self::getRange(long2ip($ipNum).'/'.$prefixSize);

            if ((ip2long($maskRange['low']) === $ipNum) && (ip2long($maskRange['high']) <= $ipEndNum)) {
                $optimalRange = array('ipNum' => $ipNum, 'prefixSize' => $prefixSize, 'ipHigh' => ip2long($maskRange['high']));
            } else {
                break;
            }
        }

        return $optimalRange;
    }

    public function suggestCidr($ip1, $ip2): string
    {
        $message = '<br>'.__('Suggested CIDR:');
        $message2 = '';

        $ipCurNum = $ip1;
        $ipEndNum = $ip2;

        while ($ipCurNum <= $ipEndNum) {
            $optimalRange = self::getOptimalRange($ipCurNum, $ipEndNum);

            if ($optimalRange === null) {
                break;
            }

            $message .= '<br />'.long2ip($optimalRange['ipNum']).'/'.$optimalRange['prefixSize'];

            $message2 .= '<br />'.long2ip($optimalRange['ipNum']).'/'.$optimalRange['prefixSize'].' = '.long2ip($optimalRange['ipNum']).'->'.long2ip($optimalRange['ipHigh']);

            $ipCurNum = $optimalRange['ipHigh'] + 1;
        }

        $message .= '<br />'.$message2.'<br />';
        return $message;
    }

    public function isInCidr($ip, $cidr): bool
    {
        $ipinput = (ip2long($ip));
        $testip = self::getRange($cidr);
        $ip1 = ip2long($testip['low']);
        $ip2 = ip2long($testip['high']);

        //dump(__METHOD__);
        //dump("test ip=$ip in cidr=$cidr");
        //dump("ipinput=$ipinput, ip1=$ip1, ip2=$ip2");
        //dump((($ip1 <= $ipinput) && ($ipinput <= $ip2)));

        return (($ip1 <= $ipinput) && ($ipinput <= $ip2));
    }

    public function getRange($cidr, $type = "")
    {
        list($ip, $mask) = explode('/', $cidr);

        if (!$this->isIp($ip)) {
            return false;
        }

        if (!is_numeric($mask) || $mask < 1 || $mask > 32) {
            return false;
        }

        $ip = ip2long($ip);
        $nm = 0xffffffff << (32 - $mask);
        $nw = ($ip & $nm);
        $bc = $nw | (~$nm);

        if ($type == 'low') {
            return long2ip($nw);
        } elseif ($type == 'high') {
            return long2ip($bc);
        } elseif ($type == 'string') {
            return long2ip($nw) . " -> " . long2ip($bc);
        } elseif ($type == 'range') {
            return long2ip($nw) . " - " . long2ip($bc);
        }else {
            return array('low' => long2ip($nw), 'high' => long2ip($bc));
        }
        //end
    }

    /*private static function countSetbits($int): int
    {
        dump(__METHOD__.' int=');
        dump($int);

        $int = $int - (($int >> 1) & 0x55555555);
        $int = ($int & 0x33333333) + (($int >> 2) & 0x33333333);
        return (($int + ($int >> 4) & 0xF0F0F0F) * 0x1010101) >> 24;
    }*/

    private static function validNetMask($netmask): bool
    {
        //dump(__METHOD__.' netmask=');
        //dump($netmask);

        $netmask = ip2long($netmask);
        $neg = ((~(int)$netmask) & 0xFFFFFFFF);
        return (($neg + 1) & $neg) === 0;
    }

    public static function maskToCIDR($netmask): int
    {
        //dump(__METHOD__.' netmask=');
        //dump($netmask);

        if (self::validNetMask($netmask)) {
            //$bits = self::countSetBits(ip2long($netmask));
            $long = ip2long($netmask);
            $base = ip2long('255.255.255.255');

            $bits = 32 - log(($long ^ $base) + 1, 2);
            $bits = intval($bits);


            //dump(__METHOD__.' bits=');
            //dump($bits);

            return $bits;
        }
        else {
            return 0;
        }
    }

    public function isIp($ip): bool
    {
        //echo var_dump(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4));
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    public function isPrivateIp($ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    public function filterIp($ip, $list = null): array
    {
        //$resip = array();
        $okip = array();

        //
        if (is_null($list)) {
            $resip = array('ip' => -1, 'error' => __('invalid list.'));

            return array('resip' => $resip, 'okip' => $okip);
        } else {
            $iprbl = $this->ip2cidr($ip);
        }

        $model = app('App\Models\\' . $list);

        //dump('ip='.$ip);
        //dump('list='.$list);


        if ($iprbl['ip'] != '-1') {
            //

            // check if already in DNS
            list($g1, $g2, $g3, $g4) = explode(".", $iprbl['ip']);
            $newip = $g4.".".$g3.".".$g2.".".$g1;


            //get dns host for list
            try {
                $rblhost = DefineList::where(['name' => $list])->select(['name', 'host'])->firstOrFail();
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' list not found, name='.$list.
                    ', error: '.$e->getMessage().
                    "\n"
                );

                $resip = array('ip' => -1, 'error' => 'List info not found.');

                return array('resip' => $resip, 'okip' => $okip);
            }

            //dump($rblhost);

            //pr($rbl_dns);

            //pr($rbl);
            //pr($this->rbl_dns);

            if ($iprbl['cidr']=='32') {
                $testIp = $newip.'.'.$rblhost->host;

                //dump($testIp);

                if (@dns_get_record($testIp, DNS_A)) {
                    $iprbl['error'] = __(":ip already in DNS :rbl (:geo)", [
                        'ip' => $ip,
                        'rbl' => $rblhost->host,
                        'geo' => @geoip_country_name_by_name($iprbl['ip'])
                    ]);

                    $iprbl['ip'] = '-2';
                    $resip = $iprbl;

                    return array('resip' => $resip, 'okip' => $okip);
                    //$break=true;
                    //break;
                }
            }

            // check if already in db
            if ($ret = $model::isInDb($iprbl['ip'], $iprbl['cidr'])) {
                //dump('isInDb ret=');
                //dump($ret);

                if($ret == '-1') {
                    $iprbl['error'] = __(":ip already in DB :list (:geo)", [
                        'ip' => $ip,
                        'list' => $list,
                        'geo' => @geoip_country_name_by_name($iprbl['ip'])
                    ]);

                    $iprbl['ip'] = '-2';
                    $resip = $iprbl;

                    return array('resip' => $resip, 'okip' => $okip);
                } elseif(is_numeric($ret) && $ret > 0) {
                    $okip['old_id'] = $ret['ret'];
                }
            } else {
                $okip['old_id'] = '';
            }


            //all ok

            $okip['init'] = $ip;
            $okip['ip'] = $iprbl['ip'];
            $okip['cidr'] = $iprbl['cidr'];
            $okip['res'] = $iprbl['ip'].'/'.$iprbl['cidr'];
            $range = $this->getRange($okip['res']);
            $okip['res'] .= ' ('.$range['low'].' -> '.$range['high'].')'." (".@geoip_country_name_by_name($iprbl['ip']).")";

            $resip = $iprbl;
            //endif ($iprbl['ip'] != '-1')
        } else {
            $resip = $iprbl;
        }

        return array('resip' => $resip, 'okip' => $okip);
    }

    public function ip2cidr($ip): array
    {
        $cidr = null;

        if (strpos($ip, '/')) {
            $tmp = explode('/', $ip);
            if (strpos($tmp[1], '.')) {
                //echo "\"$ip\" type mask<br />";

                list ($ip2, $mask) = explode('/', $ip);

                //echo "new ip=\"$ip2\", mask=\"$mask\"<br />";

                $cidr = self::maskToCIDR(trim($mask));

                //dump(__METHOD__.' cidr=');
                //dump($cidr);

                if ($cidr == 0) {
                    return array('ip' => '-1', 'error' => __(":ip has invalid netmask", ['ip' => $ip]));
                }

                $testip = self::getRange("$ip2/$cidr");

                //dump(__METHOD__.' testip=');
                //dump($testip);

                if (!self::isInCidr($ip2, $testip['low']."/".$cidr)) {
                    return array(
                        'ip' => '-1',
                        'error' => __(
                            ":ip netmask, error test=:low/:cidr, :ip2 not in range :low -> :high", [
                                'ip' => $ip,
                                'low' => $testip['low'],
                                'cidr' => $cidr,
                                'ip2' => $ip2,
                                'high' => $testip['high']
                            ])
                    );
                }

                $ip = $testip['low'];
            } else {
                //echo "$ip type cidr<br />";

                list ($ip2, $cidr) = explode('/', $ip);

                $cidr = trim($cidr);

                if (!is_numeric($cidr) ||  $cidr < 1 || $cidr > 32) {
                    return array('ip' => '-1', 'error' => __(":ip has invalid prefix", ['ip' => $ip]));
                }

                $ip2 = trim($ip2);

                $testip = self::getRange("$ip2/$cidr");

                if (!self::isInCidr($ip2, $testip['low']."/".$cidr)) {
                    return array(
                        'ip' => '-1',
                        'error' => __(":ip netmask, error test=:low/:cidr, :ip2 not in range :low -> :high", [
                            'ip' => $ip,
                            'low' => $testip['low'],
                            'cidr' => $cidr,
                            'ip2' => $ip2,
                            'high' => $testip['high']
                        ])
                    );
                }

                $ip = $testip['low'];
            }
        }

        if (strpos($ip, '-')) {
            //dump("$ip type range");

            list($ip1, $ip2) = explode('-', $ip);

            $ip1 = ip2long(trim($ip1));
            $ip2 = ip2long(trim($ip2));

            if ($ip2 < $ip1) {
                $ip3 = $ip1;
                $ip1 = $ip2;
                $ip2 = $ip3;
            }

            //dump("ip1=$ip1");
            //dump("ip2=$ip2");

            //echo "nr hosts=".($ip2-$ip1)."<br />";

            $prefix=round(log(($ip2-$ip1),2), 0, PHP_ROUND_HALF_UP);

            //echo "nr hosts=".($ip2-$ip1)."<br />";
            //echo "prefix=".$prefix."<br />";
            $cidr = intval(32 - $prefix);

            if ($cidr < 1 || $cidr > 32) {
                $sug=self::suggestCidr($ip1, $ip2);

                return array('ip' => '-1', 'error' => __(":ip range :ip1 -> :ip2, error cidr=:cidr:sug", [
                    'ip' => $ip,
                    'ip1' => long2ip($ip1),
                    'ip2' => long2ip($ip2),
                    'cidr' => $cidr,
                    'sug' => $sug
                ]));
            }

            $testip = self::getRange(long2ip($ip1)."/$cidr");

            //echo "testiplow=".$testip['low'].'<br />';
            //echo "testiphigh=".$testip['high'].'<br />';

            $testip1 = ip2long($testip['low']);
            $testip2 = ip2long($testip['high']);

            if ($ip1 != $testip1 && ($ip1 - 1) != $testip1) {
                $sug=self::suggestCidr($ip1, $ip2);

                return array('ip' => '-1', 'error' => __(":ip range :ip1 -> :ip2, error test1=:low/:cidr, :low != :ip1:sug", [
                    'ip' => $ip,
                    'ip1' => long2ip($ip1),
                    'ip2' => long2ip($ip2),
                    'low' => $testip['low'],
                    'cidr' => $cidr,
                    'sug' => $sug
                ]));
            }

            if ($ip2 != $testip2 && ($ip2 + 1) != $testip2) {
                $sug=self::suggestCidr($ip1, $ip2);

                return array('ip' => '-1', 'error' => __(":ip range :ip1 -> :ip2, error test2=:low/:cidr, :high != :ip2:sug", [
                    'ip' => $ip,
                    'ip1' => long2ip($ip1),
                    'ip2' => long2ip($ip2),
                    'low' => $testip['low'],
                    'cidr' => $cidr,
                    'high' => $testip['high'],
                    'sug' => $sug
                ]));
            }

            $ip = long2ip($testip1);
        }

        if (self::isIp($ip)) {
            //echo "$ip type ipv4<br />";

            if (self::isPrivateIp($ip)) {
                return array('ip' => '-1', 'error' => __("\":ip\" is type private", ['ip' => $ip]));
            }

            if ($cidr == null) {
                $cidr=32;
            }
        } else {
            return array('ip' => '-1', 'error'=> __("\":ip\" is not ipv4", ['ip' => $ip]));
        }

        //dump(__METHOD__.' '.__LINE__.' result=');
        //dump(array('ip' => $ip, 'cidr' => $cidr));

        return array('ip' => $ip, 'cidr' => $cidr);

        //end ip2cidr
    }

    public function arraySize($a)
    {
        $size = 0;
        if (is_array($a)) {
            foreach ($a as $v) {
                $size += is_array($v) ? $this->arraySize($v) : strlen($v);
            }
        } elseif (is_string($a)) {
            $size += strlen($a);
        }

        return $size;
    }

    public function getHostByIp($ip)
    {
        $cacheKey = $ip.'gethostname';

        //check cache
        if ($value = Cache::get($cacheKey)) {
            return $value;
        } else {
            $whois = gethostbyaddr($ip);

            $size = $this->arraySize($whois);

            if ($size < 1000 * 1000) {
                Cache::put($cacheKey, $whois, now()->addDay());
            } else {
                Log::debug(
                    __METHOD__.
                    " error saving to cache (size = $size)\n"
                );
            }
            return $whois;
        }
    }

    public function isMultiple($ip, $prefix, $searchId, $searchList): array
    {
        $bc = (ip2long($ip)+(pow(2,32-$prefix)-1));
        $result = array();

        //dump("search $ip/$prefix");

        $lists = $this->getLists();

        $lastIp = DB::raw("(`iplong`+(pow(2,32-`mask`)-1))");

        foreach($lists as $list) {
            //
            $model = app('App\Models\\' . $list);

            $data = $model::
                where([
                    ['iplong', '<=', ip2long($ip)],
                    [$lastIp, '>=', $bc]
                ])
                ->orWhere([
                    ['iplong', '>=', ip2long($ip)],
                    [$lastIp, '<=', $bc]
                ])
                ->orderBy('ip1', 'asc')
                ->orderBy('ip2', 'asc')
                ->orderBy('ip3', 'asc')
                ->orderBy('ip4', 'asc')
                ->get()->toArray();

            //dump($data);

            if (!empty($data)) {
                $result = array_merge($result, [$list => $data]);
            }
        }

        $resultData = [];



        foreach ($result as $list => $rows) {
            foreach ($rows as $row) {
                if ($searchId !=  $row['id'] || $list != $searchList) {
                    $resultData[] = [
                        'list' => $list,
                        'ip' => '<a href="'.
                            URL::route('rbl.show4', ['id' => $row['id'], 'list' => $list]).
                            '">'.
                            long2ip($row['iplong']).'/'.$row['mask'].'</a>'.'<br>'.
                            $this->getRange(long2ip($row['iplong']).'/'.$row['mask'], 'string'),
                        'inetnum' => $row['inetnum'],
                        'netname' => $row['netname'],
                        'orgname' => $row['orgname'],
                        'country' => $row['country'],
                        'checked' => $row['checked']
                    ];
                }
            }
        }

        return $resultData;
    }

    public function searchOthers($list, $searchIp, $cidr = 24): array
    {
        $model = app('App\Models\\' . $list);

        $conditions =[
            ['ip1', '=', $searchIp->ip1],
            ['ip2', '=', $searchIp->ip2],
        ];

        if ($cidr == 24) {
            $conditions[] = ['ip3', '=', $searchIp->ip3];
        }

        $rows = $model::
            where($conditions)
            ->orderBy('ip1', 'asc')
            ->orderBy('ip2', 'asc')
            ->orderBy('ip3', 'asc')
            ->orderBy('ip4', 'asc')
            ->get();

        if (empty($rows)) {
            return [];
        }

        $resultData = [];

        foreach ($rows as $row) {
            $resultData[] = [
                'list' => $list,
                'ip' => ($row['id'] == $searchIp->id ? '<span class="badge bg-success mr-1" style="min-width: 2rem;">'.__('self').'</span>' : '').
                    '<a href="'.
                    URL::route('rbl.show4', ['id' => $row['id'], 'list' => $list]).
                    '">'.
                    long2ip($row['iplong']).'/'.$row['mask'].'</a>'.'<br>'.
                    $this->getRange(long2ip($row['iplong']).'/'.$row['mask'], 'string'),
                'inetnum' => $row['inetnum'],
                'netname' => $row['netname'],
                'orgname' => $row['orgname'],
                'country' => $row['country'],
                'checked' => $row['checked']
            ];
        }

        return $resultData;
    }

    public function findDoubles($ipInfo, $list, $cidr = null)
    {
        //dump(__METHOD__);
        //dump($cidr);

        $model = app('App\Models\\' . $list);

        if (is_null($cidr)) {
            //search /16
            $data = $model::
                where([
                    ['ip1', '=', $ipInfo->ip1],
                    ['ip2', '=', $ipInfo->ip2]
                ])
                ->orderBy('ip1', 'asc')
                ->orderBy('ip2', 'asc')
                ->orderBy('ip3', 'asc')
                ->orderBy('ip4', 'asc')
                ->get();
        } else {
            $data = $model::
                where([
                    ['iplong', '>=', $ipInfo->iplong],
                    ['iplong', '<=', ($ipInfo->iplong+(pow(2,32-$ipInfo->mask)-1))]
                ])
                ->orderBy('ip1', 'asc')
                ->orderBy('ip2', 'asc')
                ->orderBy('ip3', 'asc')
                ->orderBy('ip4', 'asc')
                ->get();
        }

        return $data;
    }

    public function checkDns($checkIp): array
    {
        $dns = [];

        // check if already in DNS
        list($g1, $g2, $g3, $g4) = explode(".", $checkIp);
        $newip = $g4.".".$g3.".".$g2.".".$g1;

        $lists = $this->getLists();

        foreach ($lists as $list) {
            //get dns host for list
            try {
                $rblhost = DefineList::where(['name' => $list])->select(['name', 'host'])->firstOrFail();
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' list not found, name='.$list.
                    ', error: '.$e->getMessage().
                    "\n"
                );

                return $dns;
            }

            //dump($rblhost);

            $testIp = $newip.'.'.$rblhost->host;

            //dump($testIp);

            $dns[ $list ] = [
                'found' => false,
                'ip' => $testIp
            ];

            if (@dns_get_record($testIp, DNS_A)) {
                $dns[ $list ]['found'] = true;
            }
        }

        return $dns;
    }

    public function uptime(): ?string
    {
        if (!function_exists('posix_times')) {
            return null;
        }

        if (!$times = posix_times() ) {
            return null;
        } else {
            $now = $times['ticks']/1000;
            $days = intval($now / (60*60*24*100));
            $remainder = $now % (60*60*24*100);
            $hours = intval($remainder / (60*60*100));
            $remainder = $remainder % (60*60*100);
            $minutes = intval($remainder / (60*100));

            $writeDays = "days";
            $writeHours = "hours";
            $writeMins = "minutes";

            if ($days == 1) {
                $writeDays = "day";
            }
            if ($hours == 1) {
                $writeHours = "hour";
            }
            if ($minutes == 1) {
                $writeMins = "minute";
            }

            return ("$days $writeDays, $hours $writeHours, $minutes $writeMins");
        }
    }

    private function str2arrayInt($str): array
    {
        for ($i = 0, $len = mb_strlen($str); $i < $len; ++$i) {
            $gen[] = intval(mb_substr($str, $i, 1));
        }

        return $gen;
    }

    private function groupRegex($start, $end): string
    {
        //$regex = [];
        $result = '';

        if ($start == 0 && $end == 255) {
            return '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)';
        }

        //make 3 digits
        //$s = str_pad($start, 3, '0', STR_PAD_LEFT);
        //$e = str_pad($end, 3, '0', STR_PAD_LEFT);

        //dump($s);
        //dump($e);

        // $s = $this->str2array($s);
        // $e = $this->str2array($e);

        //dump($s);
        //dump($e);

        // 4-7 (?:[4-7]))
        // 8-15 (?:[8-9]|1[0-5])
        // 0-127 (?:[0-9]|[1-9][0-9]|1(?:[0-1][0-9]|2[0-7])))$
        // 32-63 (?:3[2-9]|[4-5][0-9]|6[0-3]))
        // 64-127 (?:6[4-9]|[7-9][0-9]|1(?:[0-1][0-9]|2[0-7])))

        if (mb_strlen($start) == mb_strlen($end)) {
            if (mb_strlen($start) == 1) {
                return '['.$start.'-'.$end.']';
            }

            if (mb_strlen($start) == 2) {
                //192.0.0.32 -> 192.0.0.63
                //^(192\.0\.0\.(?:3[2-9]|[4-5][0-9]|6[0-3]))$


                //192.0.0.32/28
                //192.0.0.32 -> 192.0.0.47
                //32-47 (?:3[2-9]|4[0-7]))$


                $a = $this->str2arrayInt($start);
                $b = $this->str2arrayInt($end);

                // dump($a);
                // dump($b);

                //32-39 (?:3[2-9]))
                if ($a[0] == $b[0]) {
                    return $a[0].'['.$a[1].'-'.$b[1].']';
                }

                // 32-63 (?:3[2-9]|[4-5][0-9]|6[0-3]))
                // 32-47 (?:3[2-9]|4[0-7]))$

                $result .= $a[0].'['.$a[1].'-9]|';

                if (($a[0]+1) <= ($b[0]-1)) {
                    $result .= '['.($a[0]+1).'-'.($b[0]-1).'][0-9]|';
                }

                $result .= $b[0].'[0-'.$b[1].']';
            }

            if (mb_strlen($start) == 3) {
                //(?:1(?:2[8-9]|[3-9][0-9])|2(?:[0-4][0-9]|5[0-5])))$
                //192.0.0.128 -> 192.0.0.255

                $a = $this->str2arrayInt($start);
                $b = $this->str2arrayInt($end);

                if ($a[0] == $b[0] && $a[1] == $b[1]) {
                    return $a[0].$a[1].'['.$a[2].'-'.$b[2].']';
                }
                // dump($a);
                // dump($b);

                $result .= '(?:'.$a[0].'';

                $result .= '(?:'.$a[1].'['.$a[2].'-9]';

                //.(?:1(?:4[4-9]|5[0-9]))\

                //((?:1(?:4[4-9]|ASD[5-4][0-9])|5[0-9]))
                //.(?:1(?:4[4-9]|5[0-9]))\

                if ($a[1]+1 <= 9) {
                    if ($a[1]+1 <= $b[1]) {
                        if ($b[1] < 9 && $a[0] == $b[0]) {
                            if ($b[0] == 2 && $b[1] == 5) {
                                $between = '['.($a[1] + 1).'-'.($b[1]-1).']';
                            } else {
                                $between = '[' . ($a[1] + 1) . '-' . $b[1] . ']';
                            }
                        } else {
                            $between = '[' . ($a[1] + 1) . '-9]';
                        }
                        if (($a[1]+1) >= ($b[1] -1)) {
                            $between = ($a[1]+1);
                        }
                        if ($b[2] < 9 && ($a[1]+1) > ($b[1]-1)) {
                            $result .= '|' . $between . '[0-'.$b[2].']';
                        } else {
                            $result .= '|' . $between . '[0-9]';
                        }
                    } else {
                        $result .= '|[' . ($a[1] + 1) . '-' . ($b[0] > 1 ? 9 : ((($b[1] - 1) < ($a[1] + 1)) ? $b[1] : $b[1] - 1)) . '][0-' . ((($b[1] - 1) > ($a[1] + 1)) ? 9 : $b[2]) . ']';
                    }
                }

                $result .= ')';

                //187.144.0.0 -> 187.159.255.255
                if ($b[0] > $a[0]) {
                    $result .= ')|';

                    $result .= '(?:' . $b[0] . '';

                    if ($b[1] == 0) {
                        //
                        $result .= '(?:';
                    } else {
                        $result .= '(?:[0-' . ($b[1] - 1) . '][0-9]|';
                    }

                    $result .= $b[1] . '[0-' . $b[2] . ']))';
                } else {
                    $result .= '|(?:'.$b[0].$b[1] . '[0-' . $b[2] . ']))';
                }

                //.(?:1(?:4[4-9]|5[0-9]))\
                //((?:1(?:6[0-9]|[7-8][0-9])))

                //(?:1(?:[6-8][0-9]|9[0-1]))\

                //((?:1(?:6[0-9]|[7-8][0-9])9[0-1])))
            }
        }

        if (mb_strlen($start) < mb_strlen($end)) {
            // 8-15 (?:[8-9]|1[0-5])
            // 64-127 (?:6[4-9]|[7-9][0-9]|1(?:[0-1][0-9]|2[0-7])))

            $a = $this->str2arrayInt($start);
            $b = $this->str2arrayInt($end);

            // dump($a);
            // dump($b);

            if (mb_strlen($start) == 1 && mb_strlen($end) == 2) {
                $result .= '(['.$a[0].'-9])|(';

                //8-15 (?:[8-9]|1[0-5])
                //0-31 (?:[0-9]|[1-2][0-9]|3[0-1]))$   ([0-9]|([1-2][0-9]|3[0-1]))"
                if (($b[0]-1) > 1) {
                    $result .= '[1-'.($b[0]-1).'][0-9]|';
                }

                $result .= $b[0].'[0-'.$b[1].'])';
            }

            if (mb_strlen($start) == 1 && mb_strlen($end) == 3) {
                //first part/range
                $result .= '(?:['.$a[0].'-9]|';
                $result .= '['.($a[0]+1).'-9][0-9])';

                $result .= '|';

                //second part/range

                //first digit
                $result .= '(?:[1-'.$b[0].']';

                //second digit
                $result .= '(?:[0-'.($b[1]-1).'][0-9]|';

                //third digit
                $result .= $b[1].'[0-'.$b[2].'])';

                $result .= ')';
            }

            if (mb_strlen($start) == 2) {
                // 64-127 (?:6[4-9]|[7-9][0-9]|1(?:[0-1][0-9]|2[0-7])))

                $result .= '('.$a[0].'['.$a[1].'-9]|';

                if ($a[0]+1 <= 9) {
                    $result .= '['.($a[0]+1).'-9][0-9]';
                }

                //.(6[4-9]|[7-9][0-9]|)"
                $result .= ')|('.$b[0].'';

                if ($b[1] < 9) {
                    if ($b[1] == 0) {
                        $result .= '';
                    } else {
                        $result .= '([0-' . ($b[1] - 1) . '][0-9]|';
                    }
                }

                $result .= $b[1].'[0-'.$b[2].'])';
                if ($b[1] < 9) {
                    if ($b[1] != 0) {
                        $result .= ')';
                    }
                }
            }
        }







        //dump($regex);
        //dump($result);

        return '('.$result.')';
    }

    private function rangeRegex($start, $end): string
    {
        $a = explode(".", $start);
        $b = explode(".", $end);

        //dump($a);
        //dump($b);

        $regex = [];

        for ($i = 0; $i <= 3; $i++) {
            if ($a[$i] == $b[$i]) {
                $regex[] = $a[$i];
            } else {
                $regex[] = $this->groupRegex($a[$i], $b[$i]);
            }
        }

        return implode('\\.', $regex);
    }

    public function rangeToRegex($iplong, $mask)
    {
        $ipAddr = long2ip($iplong);

        list($g1, $g2, $g3, $g4) = explode(".", $ipAddr);

        if ($mask == 32) {
            $search = $ipAddr;
            $search = str_replace('.','\\.', $search);
        } elseif ($mask == 24) {
            $search = $g1.'\\.'.$g2.'\\.'.$g3.'\\.[0-9]{1,3}.*';
        } elseif ($mask == 16) {
            $search = $g1.'\\.'.$g2.'\\.[0-9]{1,3}\\.[0-9]{1,3}.*';
        } else {
            //create special regex
            $range = $this->getRange($ipAddr.'/'.$mask);

            //dump($range);
            //$search = $g1.'\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}.*';

            $search = $this->rangeRegex($range['low'], $range['high']);
        }

        //dump ($search);

        return $search;
    }
}
