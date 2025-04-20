<?php

namespace App\Helpers;

use App\Models\DefineList;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class Rbl6
{
    protected static $lists6 = [
        'White6' => 'White6',
        'Grey6' => 'Grey6',
        'Black6' => 'Black6'
    ];

    public function iPv6MaskToByteArray($subnetMask) {
        $addr = str_repeat("f", $subnetMask / 4);
        switch ($subnetMask % 4) {
            case 0:
                break;
            case 1:
                $addr .= "8";
                break;
            case 2:
                $addr .= "c";
                break;
            case 3:
                $addr .= "e";
                break;
        }
        $addr = str_pad($addr, 32, '0');
        $addr = pack("H*" , $addr);
        return $addr;
    }

    public function iPv6CidrMatch($address, $subnetAddress, $subnetMask)
    {
        $binMask = $this->iPv6MaskToByteArray($subnetMask);
        return ($address & $binMask) == $subnetAddress;
    }

    public function expandIPv6($ip)
    {
        $hex = bin2hex(inet_pton($ip));
        return implode(':', str_split($hex, 4));
    }

    /**
     * Compresses an IPv6 address
     *
     * RFC 2373 allows you to compress zeros in an address to '::'. This
     * function expects a valid IPv6 address and compresses successive zeros
     * to '::'
     *
     * Example:  FF01:0:0:0:0:0:0:101   -> FF01::101
     *           0:0:0:0:0:0:0:1        -> ::1
     *
     * When $ip is an already compressed address and $force is false, the method returns
     * the value as is, even if the address can be compressed further.
     *
     * Example: FF01::0:1 -> FF01::0:1
     *
     * To enforce maximum compression, you can set the second argument $force to true.
     *
     * Example: FF01::0:1 -> FF01::1
     *
     * @param String  $ip    a valid IPv6-address (hex format)
     * @param boolean $force if true the address will be compressed as best as possible (since 1.2.0)
     *
     * @return String the compressed IPv6-address (hex format)
     * @access public
     * @see    Uncompress()
     * @static
     * @author elfrink at introweb dot nl
     */
    public function compress($ip, $force = false)
    {
        if (false !== strpos($ip, '::')) { // its already compressed
            if(true == $force) {
                $ip = $this->expandIPv6($ip);
            } else {
                return $ip;
            }
        }

        $prefix = '';

        $ipp = explode(':', $ip);

        for ($i = 0; $i < count($ipp); $i++) {
            if ($ipp[$i] != '') {
                $ipp[$i] = dechex(hexdec($ipp[$i]));
            } else {
                unset($ipp[$i]);
            }
        }

        // dump($ipp);

        $cip = ':' . join(':', $ipp) . ':';

        // dump($cip);

        preg_match_all("/(:0)(:0)+/", $cip, $zeros);

        if (count($zeros[0]) > 0) {
            $match = '';

            foreach ($zeros[0] as $zero) {
                if (strlen($zero) > strlen($match)) {
                    $match = $zero;
                }
            }

            $cip = preg_replace('/' . $match . '/', ':', $cip, 1);
        }

        if ($cip != "::") {
            $cip = preg_replace('/((^:)|(:$))/', '', $cip);
            $cip = preg_replace('/((^:)|(:$))/', '::', $cip);
        }

        return $cip.$prefix;
    }


    public function getRange($cidr, $type = "")
    {
        list($ip, $mask) = explode('/', $cidr);

        if (!$this->isIp($ip)) {
            return false;
        }

        if (!is_numeric($mask) || $mask < 1 || $mask > 128) {
            return false;
        }


        // Split in address and prefix length
        //list($addr_given_str, $prefixlen) = explode('/', $prefix);

        // Parse the address into a binary string
        $addr_given_bin = inet_pton($ip);

        // Convert the binary string to a string with hexadecimal characters
        $addr_given_hex = bin2hex($addr_given_bin);

        // Overwriting first address string to make sure notation is optimal
        //$addr_given_str = inet_ntop($addr_given_bin);

        // Calculate the number of 'flexible' bits
        $flexbits = 128 - $mask;

        // Build the hexadecimal strings of the first and last addresses
        $addr_hex_first = $addr_given_hex;
        $addr_hex_last = $addr_given_hex;

        // We start at the end of the string (which is always 32 characters long)
        $pos = 31;
        while ($flexbits > 0) {
            // Get the characters at this position
            $orig_first = substr($addr_hex_first, $pos, 1);
            $orig_last = substr($addr_hex_last, $pos, 1);

            // Convert them to an integer
            $origval_first = hexdec($orig_first);
            $origval_last = hexdec($orig_last);

            // First address: calculate the subnet mask. min() prevents the comparison from being negative
            $mask = 0xf << (min(4, $flexbits));

            // AND the original against its mask
            $new_val_first = $origval_first & $mask;

            // Last address: OR it with (2^flexbits)-1, with flexbits limited to 4 at a time
            $new_val_last = $origval_last | (pow(2, min(4, $flexbits)) - 1);

            // Convert them back to hexadecimal characters
            $new_first = dechex($new_val_first);
            $new_last = dechex($new_val_last);

            // And put those character back in their strings
            $addr_hex_first = substr_replace($addr_hex_first, $new_first, $pos, 1);
            $addr_hex_last = substr_replace($addr_hex_last, $new_last, $pos, 1);

            // We processed one nibble, move to previous position
            $flexbits -= 4;
            $pos -= 1;
        }

        // Convert the hexadecimal strings to a binary string
        $addr_bin_first = hex2bin($addr_hex_first);
        $addr_bin_last = hex2bin($addr_hex_last);

        // And create an IPv6 address from the binary string
        $addr_str_first = inet_ntop($addr_bin_first);
        $addr_str_last = inet_ntop($addr_bin_last);

        $addr_str_first = $this->expandIPv6($addr_str_first);
        $addr_str_last = $this->expandIPv6($addr_str_last);

        if ($type == 'low') {
            return $addr_str_first;
        } elseif ($type == 'high') {
            return $addr_str_last;
        } elseif ($type == 'string') {
            return $addr_str_first . " -> " . $addr_str_last;
        } elseif ($type == 'range') {
            return $addr_str_first . " - " . $addr_str_last;
        }else {
            return array('low' => $addr_str_first, 'high' => $addr_str_last);
        }
        //end
    }

    public function isInCidr($ip, $cidr): bool
    {
        list ($subnetAddress, $subnetMask) = explode('/', $cidr);

        $ip = inet_pton($ip);
        $subnetAddress = inet_pton($subnetAddress);

        return $this->iPv6CidrMatch($ip, $subnetAddress, $subnetMask);
    }

    public function stats(): array
    {
        $lists = $this->getLists();

        $data = [];

        foreach ($lists as $list) {
            $model = app('App\Models\\' . $list);

            $data[$list] = [
                'count' => number_format ($model->count() ,  0 ,  "," ,  "." ).' rows',
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
        return self::$lists6;
    }

    public function isIp($ip): bool
    {
        //echo var_dump(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4));
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    public function isPrivateIp($ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    public function filterIp($ip, $list = null): array
    {
        $okip = array();

        if (is_null($list)) {
            $resip = array('ip' => -1, 'error' => __('invalid list.'));

            return array('resip' => $resip, 'okip' => $okip);
        } else {
            $iprbl = $this->ip2cidr($ip);
        }

        // dump($iprbl);

        $model = app('App\Models\\' . $list);

        if ($iprbl['ip'] != '-1') {
            //

            // check if already in DNS
            list($g1, $g2, $g3, $g4, $g5, $g6, $g7, $g8) = explode(":", $iprbl['ip']);

            $newip = implode(
                '.',
                array_reverse(str_split(str_replace(':', '', $iprbl['ip']), 1))
            );

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

            if ($iprbl['cidr']=='128') {
                $testIp = $newip.'.'.$rblhost->host;

                // dump($testIp);

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
            if ($ret = $model::isInDb6($iprbl['ip'], $iprbl['cidr'])) {
                // dump('isInDb ret=');
                // dump($ret);

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
            //echo "$ip type cidr<br />";

            list ($ip2, $cidr) = explode('/', $ip);

            $cidr = trim($cidr);

            if (!is_numeric($cidr) ||  $cidr < 1 || $cidr > 128) {
                return array('ip' => '-1', 'error' => __(":ip has invalid prefix", ['ip' => $ip]));
            }

            $ip2 = trim($ip2);

            if (!self::isIp($ip2)) {
                return array('ip' => '-1', 'error' => __("[:ip is invalid]", ['ip' => $ip]));
            }

            $ip2 = $this->expandIPv6($ip2);

            $testip = self::getRange("$ip2/$cidr");

            //dump($testip);
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

        if (self::isIp($ip)) {
            // echo "$ip type ipv6<br />";

            $ip = $this->expandIPv6($ip);

            if (self::isPrivateIp($ip)) {
                return array('ip' => '-1', 'error' => __("\":ip\" is type private", ['ip' => $ip]));
            }

            if ($cidr == null) {
                $cidr=128;
            }
        } else {
            return array('ip' => '-1', 'error'=> __("\":ip\" is not ipv6", ['ip' => $ip]));
        }

        // dump(__METHOD__.' '.__LINE__.' result=');
        // dump(array('ip' => $ip, 'cidr' => $cidr));

        return array('ip' => $ip, 'cidr' => $cidr);

        //end ip2cidr
    }

    public function hostnameRange($iplong, $mask): string
    {
        $ip = inet_ntop($iplong);
        $cidr = $ip.'/'.$mask;

        $result = '';

        $c4 = new Rbl4();

        if ($range = $this->getRange($cidr)) {
            $hostname = [
                'low' => '',
                'high' => ''
            ];

            if ($host = $c4->getHostByIp($range['low'])) {
                $hostname['low'] = $host;
            }

            if ($host = $c4->getHostByIp($range['high'])) {
                $hostname['high'] = $host;
            }

            $result = ': '.$range['low'].'->'.$range['high'].' ('.$hostname['low'].'->'.$hostname['high'].')';
        }

        return $result;
    }

    public function isMultiple($ip, $prefix, $searchId, $searchList): array
    {
        $bc = inet_pton($this->getRange($ip.'/'.$prefix, 'high'));
        $result = array();

        // dump("search $ip/$prefix");

        $lists = $this->getLists();

        $lastIp = DB::raw("(INET6_ATON(LastIPv6MatchingCIDR(INET6_NTOA(`iplong`), `mask`)))");
        $startIp = inet_pton($ip);

        foreach($lists as $list) {
            //
            $model = app('App\Models\\' . $list);

            $data = $model::
            where([
                ['iplong', '<=', $startIp],
                [$lastIp, '>=', $bc]
            ])
                ->orWhere([
                    ['iplong', '>=', $startIp],
                    [$lastIp, '<=', $bc]
                ])
                ->orderBy('ip1', 'asc')
                ->orderBy('ip2', 'asc')
                ->orderBy('ip3', 'asc')
                ->orderBy('ip4', 'asc')
                ->orderBy('ip5', 'asc')
                ->orderBy('ip6', 'asc')
                ->orderBy('ip7', 'asc')
                ->orderBy('ip8', 'asc')
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
                            URL::route('rbl.show6', ['id' => $row['id'], 'list' => $list]).
                            '">'.
                            $row['long2ip'].'/'.$row['mask'].'</a>'.'<br>'.
                            $this->getRange($row['long2ip'].'/'.$row['mask'], 'string'),
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

    public function findDoubles($ipInfo, $list, $cidr = null)
    {
        //dump(__METHOD__);
        //dump($cidr);

        $model = app('App\Models\\' . $list);

        $searchMask = $ipInfo->mask;
        if (is_null($cidr) && $ipInfo->mask > 32) {
            $searchMask = $ipInfo->mask - 32;
        } elseif (is_null($cidr) && $ipInfo->mask > 16) {
            $searchMask = $ipInfo->mask - 16;
        }

        // dump($ipInfo->long2ip.'/'.$searchMask);

        $bc = inet_pton($this->getRange($ipInfo->long2ip.'/'.$searchMask, 'high'));

        // dump($bc);

        $data = $model::
            where([
                ['iplong', '>=', $ipInfo->getRawOriginal('iplong')],
                ['iplong', '<=', $bc]
            ])
            ->orderBy('ip1', 'asc')
            ->orderBy('ip2', 'asc')
            ->orderBy('ip3', 'asc')
            ->orderBy('ip4', 'asc')
            ->orderBy('ip5', 'asc')
            ->orderBy('ip6', 'asc')
            ->orderBy('ip7', 'asc')
            ->orderBy('ip8', 'asc')
            ->get();

        return $data;
    }

    public function searchOthers($list, $searchIp, $cidr = null): array
    {
        $model = app('App\Models\\' . $list);

        $searchMask = $cidr;

        $bc = inet_pton($this->getRange($searchIp->long2ip.'/'.$searchMask, 'high'));

        $rows = $model::
            where([
                ['iplong', '>=', $searchIp->getRawOriginal('iplong')],
                ['iplong', '<=', $bc]
            ])
            ->orderBy('ip1', 'asc')
            ->orderBy('ip2', 'asc')
            ->orderBy('ip3', 'asc')
            ->orderBy('ip4', 'asc')
            ->orderBy('ip5', 'asc')
            ->orderBy('ip6', 'asc')
            ->orderBy('ip7', 'asc')
            ->orderBy('ip8', 'asc')
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
                    URL::route('rbl.show6', ['id' => $row['id'], 'list' => $list]).
                    '">'.
                    $row['long2ip'].'/'.$row['mask'].'</a>'.'<br>'.
                    $this->getRange($row['long2ip'].'/'.$row['mask'], 'string'),
                'inetnum' => $row['inetnum'],
                'netname' => $row['netname'],
                'orgname' => $row['orgname'],
                'country' => $row['country'],
                'checked' => $row['checked']
            ];
        }

        return $resultData;
    }

    public function rangeToRegex($ip, $mask)
    {
        //keep the common part from start and end

        $search = '';
        $range = $this->getRange($ip.'/'.$mask);

        $start = str_split($range['low'], 1);
        $end = str_split($range['high'], 1);

        // dump($range);
        // dump($start);
        // dump($end);

        for ($i = 0; $i <= 38; $i++) {
            if ($start[$i] == $end[$i]) {
                $search .= $start[$i];
            } else {
                //stop
                break;
            }
        }

        // dump($search);

        $search = $this->compress($search);

        // dump($search);

        //TODO do correct regex
        /*$g = explode(':', $ip);

        // dump($g);


        foreach ($g as $item) {
            if (trim($item) != '') {
                $search .= $item.':';
            }
        }*/

        // dump($search);

        return $search;
    }

    public function checkDns($checkIp): array
    {
        $dns = [];

        $checkIp = $this->expandIPv6($checkIp);

        // check if already in DNS
        $newip = implode(
            '.',
            array_reverse(str_split(str_replace(':', '', $checkIp), 1))
        );

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
}
