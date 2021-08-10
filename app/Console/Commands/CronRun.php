<?php
/** @noinspection PhpStrictComparisonWithOperandsOfDifferentTypesInspection */
/** @noinspection DuplicatedCode */

namespace App\Console\Commands;

use App\Helpers\Rbl4;
use App\Helpers\Rbl6;
use App\Helpers\WhoisLib;
use App\Models\Black;
use App\Models\Black6;
use App\Models\DefineList;
use App\Models\Grey;
use App\Models\Grey6;
use App\Models\Hit;
use App\Models\RblLog;
use App\Models\Setup;
use App\Models\Syslog;
use App\Models\White;
use App\Models\White6;
use App\Models\Whois;
use DateTime;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CronRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CronRun';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export DNS and DB maintenance';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        set_time_limit(1200);
        $time_start = microtime(true);

        /*
        $this->error('test');
        $this->newLine(3);
        $this->line('Display this on the screen');
        */

        $seconds = 301;

        if (function_exists('posix_times')) {
            $times = posix_times();

            $seconds = $times['ticks']/1000/100;
        }

        $c4 = new Rbl4();

        if ($seconds <= 300) {
            $this->error(__('[Server started recently, wait 5 minutes. Uptime: :uptime]', ['uptime' => $c4->uptime()]));
            return -1;
        }

        try {
            //$this->line(date('H:i:s').' re-enable functions in handle()');
            $this->checkRegex();

            $this->hits();

            $this->cleanup();

            $this->check();

            $this->export();
            //
        } catch (Exception $e) {
            $this->line(
                __(
                    "[ERROR! message: :msg, trace: \n :trace]",
                    [
                        'msg' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]
                )
            );

            return -1;
        }

        $time_end = microtime(true);
        $time = round($time_end - $time_start, 4);

        $file = storage_path('logs/cron.log');

        $str = \file($file);

        if (!empty($str)) {
            $this->line(__('[:method took :time seconds]', ['time' => $time, 'method' => __METHOD__]));
        }

        return 0;
    }

    /**
     * wrapper for the function that read the rbldns log file
     *
     */
    public function hits(): void
    {
        $time_start = microtime(true);

        DB::enableQueryLog();

        $debug = !$this->readRblLog();

        if ($debug) {
            $time_end = microtime(true);
            $time = round($time_end - $time_start, 4);

            $logs = DB::getQueryLog();

            $this->line("SQL LOG: ".print_r($logs, true));

            $this->line(__('[:method took :time seconds]', ['time' => $time, 'method' => __METHOD__]));
        }
    }

    /**
     * reads the rbldns log file
     *
     * if valid ip query is found, it is marked in hits table
     *
     * @return bool
     */
    private function readRblLog(): bool
    {
        set_time_limit(600);

        $rbllog = Setup::whereName('rbllog')->first();

        //$this->line(print_r($rbllog, true));

        if (@is_null($rbllog->value)) {
            $this->error(__('[Please create variable rbllog with the RBLDNSD log path]'));
            $this->newLine();
            return false;
        }

        $rbllog = $rbllog->value.date("Y-m-d",time());

        //$this->line($rbllog);

        $test = RblLog::whereMessage($rbllog.' ok')->first();

        if (!is_null($test)) {
            //$this->error(__('[Already read file: :file]', ['file' => $rbllog]));

            return true;
        }

        if (!file_exists($rbllog)) {
            //if not found it's ok, it means log rotate didn't run yet.
            //$this->error(__('[File not found. file=:file]', ['file' => $rbllog]));
            return true;
        }

        try {
            $log = file($rbllog);
        } catch (Exception $e) {
            $this->error(__('[Read file exception=:msg]', ['msg' => $e->getMessage()]));
            $this->newLine();
            $log = false;
        }

        if ($log === false) {
            $this->error(__('[Error reading log file=:file]', ['file' => $rbllog]));
            $this->newLine();
            return false;
        }

        $hits = [];
        $dnsClients = [];

        $rblv4 = new Rbl4();
        $rblv6 = new Rbl6();

        try {
            $rblhost = DefineList::select(['name', 'host'])->get();
        } catch (Exception $e) {
            $this->error(__METHOD__.' error: '.$e->getMessage());
            $this->newLine();

            return false;
        }

        //$this->line('rblhost=' .print_r($rblhost->toArray(), true));

        $resultTypes = array();
        $banIp = array();
        $noerrorIp = array();
        $noerrorIps = array();

        $nxdomainIp = array();
        $nxdomainIps = array();

        foreach ($log as $lineText) {
            //1390823752 127.0.0.1 black.vlnet.ro ANY IN: NOERROR/3/168
            list($time, $clientIp, $queryIp, $type, $type2, $result) = explode(" ", $lineText);

            if (@is_null($result)) {
                $this->error(__('[Error parsing line: :line]', ['line' => trim($lineText)]));
                $this->newLine();
                return false;
            }

            list($res1, $res2, $res3) = explode('/', $result);

            if (!isset($resultTypes[$res1])) {
                $resultTypes[$res1] = 0;
            }

            $resultTypes[$res1] += 1;

            switch ($res1) {
                case 'NXDOMAIN':
                    //$this->line(__('[NXDOMAIN found, skip line :line]', ['line' => trim($lineText)]));
                    //NXDOMAIN means not in dns, so nothing to do
                    $nxdomainIp[$clientIp][$res1]['reason'] = $res1;
                    $nxdomainIp[$clientIp][$res1]['count'] = ($nxdomainIp[$clientIp][$res1]['count'] ?? 0) + 1;
                    $nxdomainIp[$clientIp][$res1]['ip'] = $clientIp;
                    $nxdomainIp[$clientIp][$res1]['host_ip'] = $rblv4->getHostByIp($clientIp);// gethostbyaddr($clientIp);
                    $nxdomainIp[$clientIp][$res1]['lines'][] = trim($lineText);

                    $nxdomainIps[$clientIp] = $clientIp;
                    //next foreach
                    continue 2;


                case 'REFUSED':
                    $this->error(__('[WARNING! found REFUSED at line :line]', ['line' => trim($lineText)]));
                    $this->newLine();
                    //nothing to do here

                    $banIp[$clientIp][$res1]['reason'] = $res1;
                    $banIp[$clientIp][$res1]['count'] = ($banIp[$clientIp][$res1]['count'] ?? 0) + 1;
                    $banIp[$clientIp][$res1]['ip'] = $clientIp;
                    $banIp[$clientIp][$res1]['host_ip'] = $rblv4->getHostByIp($clientIp);// gethostbyaddr($clientIp);
                    $banIp[$clientIp][$res1]['lines'][] = trim($lineText);

                    //next foreach
                    continue 2;


                case 'NOERROR':
                    //this is a correct line
                    $noerrorIp[$clientIp][$res1]['reason'] = $res1;
                    $noerrorIp[$clientIp][$res1]['count'] = ($noerrorIp[$clientIp][$res1]['count'] ?? 0) + 1;
                    $noerrorIp[$clientIp][$res1]['ip'] = $clientIp;
                    $noerrorIp[$clientIp][$res1]['host_ip'] = $rblv4->getHostByIp($clientIp);// gethostbyaddr($clientIp);
                    $noerrorIp[$clientIp][$res1]['lines'][] = trim($lineText);

                    $noerrorIps[$clientIp] = $clientIp;
                    break;

                default:
                    $this->error(__('[ERROR! MUST CREATE CASE FOR THIS CODE, THIS LINE IS IGNORED. Unknown result code :code found at line :line]', ['code' => $res1, 'line' => trim($lineText)]));
                    $this->newLine();
                    //ignore this line, create a case for it

                    //next foreach
                    continue 2;
            }

            //only A query is useful
            if ($type != 'A') {
                if (!in_array($type, ['AAAA', 'MX', 'NS', 'SOA', 'TXT'])) {
                    $this->error(__('[ERROR, LINE IGNORED! type1 :type query unknown at line :line]', ['type' => $type, 'line' => trim($lineText)]));
                    $this->newLine();
                }
                continue;
            }

            if ($type2 != 'IN:') {
                $this->error(__('[ERROR, LINE IGNORED! type2 :type2 query unknown at line :line]', ['type2' => $type2, 'line' => trim($lineText)]));
                $this->newLine();
                continue;
            }

            $skip = true;

            $ipList = '';
            $queriedIp = '';

            foreach ($rblhost as $list) {
                if (stripos($queryIp, $list->host) !== false && $queryIp != $list->host) {
                    $skip = false;
                    $ipv6 = false;

                    list($host, $ip) = array_merge(array(true), explode('.'.$list->host, $queryIp));

                    // $this->line('queryIp='.$queryIp);
                    // $this->line('Ip='.$ip);
                    // $this->line('len Ip='.strlen($ip));


                    if (strlen($ip) == 63) {
                        //ipv6
                        $ipv6 = true;
                        $queriedIp = implode(
                            ':',
                            str_split(strrev(str_replace('.', '', $ip)), 4)
                        );
                    } else {
                        //ipv4
                        list($g4, $g3, $g2, $g1) = explode('.', $ip);

                        $queriedIp = $g1 . '.' . $g2 . '.' . $g3 . '.' . $g4;
                    }

                    // $this->line('queriedIp='.$queriedIp);

                    if ($ipv6 && !$rblv6->isIp($queriedIp)) {
                        $this->error(__('[ERROR, LINE IGNORED! query ip: :queriedIp is not an IPv6 at line :line]', ['queriedIp' => $queriedIp, 'line' => trim($lineText)]));
                        $this->newLine();
                        $skip = true;
                        break;
                    } elseif (!$ipv6 && !$rblv4->isIp($queriedIp)) {
                        $this->error(__('[ERROR, LINE IGNORED! query ip: :queriedIp is not an IPv4 at line :line]', ['queriedIp' => $queriedIp, 'line' => trim($lineText)]));
                        $this->newLine();
                        $skip = true;
                        break;
                    }

                    $ipList = $list->name;
                    break;
                }

                unset($host);
            }

            if ($skip === true) {
                $this->error(__('[ERROR, LINE IGNORED! query: :queryIp has no IP at line :line]', ['queryIp' => $queryIp, 'line' => trim($lineText)]));
                $this->newLine();
                continue;
            }

            $queryDay = date("Y-m-d", $time);
            //$this->line('day='.$queryDay);

            //find $queriedIp in db
            $model = app('App\Models\\' . $ipList);

            if ($ipv6) {
                $found = $model::
                    where('iplong', '<=', inet_pton($queriedIp))
                    ->whereRaw('INET6_ATON(LastIPv6MatchingCIDR(INET6_NTOA(`iplong`), `mask`)) >= ?', [inet_pton($queriedIp)])
                    ->get();
            } else {
                $found = $model::
                    where('iplong', '<=', ip2long($queriedIp))
                    ->whereRaw('(`iplong`+(pow(2,32-`mask`)-1)) >= ?', [ip2long($queriedIp)])
                    ->get();
            }

            //$this->line("SQL LOG: ".print_r(DB::getQueryLog(), true));
            // $this->line('$queriedIp='.$queriedIp);

            // $this->line('found='. print_r($found->toArray(), true));

            if (count($found) == 0) {
                //$this->error('queriedIp='.$queriedIp.' list='.$ipList.' line='.$lineText);
                continue;
            }

            if (count($found) > 1) {
                $this->error(__('[WARNING, IGNORED LINE! found multiple rows in db :result for queriedIp: :queriedIp, line: :line]', [
                    'result' => print_r($found->toArray(), true),
                    'queriedIp' => $queriedIp,
                    'line' => trim($lineText)
                ]));
                continue;
            }

            if (@is_null($hits[$queryDay])) {
                $hits[$queryDay] = array();
            }

            //$this->line('found='.print_r($found, true));
            $ipListId = $found[0]->id;

            //$this->line('$ipListId='.$ipListId);

            if (@is_null($hits[$queryDay][$ipList])) {
                $hits[$queryDay][$ipList] = array();
            }

            if (@is_null($hits[$queryDay][$ipList][$ipListId])) {
                $hits[$queryDay][$ipList][$ipListId] = 0;
            }

            $hits[$queryDay][$ipList][$ipListId] += 1;
            $dnsClients[$ipList][$clientIp] = $clientIp;

            //$this->line('test after '.trim($lineText));
            // $this->newLine();

            unset($res2);
            unset($res3);
            //end foreach log line
        }

        $this->line('hits='. print_r($hits, true));

        $stats = [];

        foreach ($hits as $day=>$row) {
            foreach ($row as $list => $ips) {
                foreach ($ips as $id => $count) {
                    list($year, $month, $hday) = explode('-', $day);

                    //find existing hit
                    $oldHit = Hit::
                        where('list', 'App\\Models\\'.$list)
                        ->where('year', $year)
                        ->where('month', $month)
                        ->where('day', $hday)
                        ->where('list_id', $id)
                        ->get();



                    //pr($old_hit);
                    //$this->line('old hit='. print_r($oldHit->toArray(), true));

                    if (count($oldHit) > 1) {
                        $this->error(__('[ERROR, QUIT! found multiple hits in db for id=:id, list=:list, day=:day, year=:year, month=:month, day=:hday, DB: :oldHit]', [
                            'id' => $id,
                            'list' => $list,
                            'day' => $day,
                            'year' => $year,
                            'month' => $month,
                            'hday' => $hday,
                            'oldHit' => print_r($oldHit->toArray(), true)
                        ]));
                        return false;
                    }

                    $hit['id'] = $oldHit[0]->id ?? '';
                    $hit['list'] = 'App\\Models\\'.$list;
                    $hit['year'] = $year;
                    $hit['month'] = $month;
                    $hit['day'] = $hday;
                    $hit['list_id'] = $id;
                    $hit['count'] = ($oldHit[0]->count ?? 0) + $count;

                    //$this->line('to save hit='. print_r($hit, true));

                    if (@is_null($stats[$list])) {
                        $stats[$list] = 0;
                    }

                    $stats[$list] += 1;

                    try {
                        Hit::updateOrCreate(['id' => $hit['id']], $hit);
                    } catch (Exception $e) {
                        $this->error(__('[ERROR, QUIT! saving hit=:hit, error=:error]', ['hit' => print_r($hit, true), 'error' => $e->getMessage()]));
                        return false;
                    }
                }
            }
        }

        //$this->line("SQL LOG: ".print_r(DB::getQueryLog(), true));


        if (!empty($noerrorIp)) {
            $this->newLine();
            $this->line(__('[NOERROR IPs found(:count): :noerr]', ['count' => count($noerrorIp),'noerr' => print_r($noerrorIp, true)]));
            $this->newLine();
        }

        if (!empty($nxdomainIp)) {
            $this->newLine();
            $this->line(__('[NXDOMAIN IPs found(:count): :nxdom]', ['count' => count($nxdomainIp), 'nxdom' => print_r($nxdomainIp, true)]));
            $this->newLine();
        }

        if (!empty($banIp)) {
            $this->newLine();
            $this->line(__('[REFUSED IPs found(:count): :badip]', ['count' => count($banIp),'badip' => print_r($banIp, true)]));
            $this->newLine();
        }

        $message = __('Response type:')."<br>";

        foreach ($resultTypes as $result => $value) {
            $message .= "$result -> $value<br>";
        }

        if (!empty($stats)) {
            $message .= '<br>'.__('Served IPs:').'<br>';

            foreach ($stats as $list => $hits) {
                $message .= "$list -> $hits<br>";
            }
        }

        if (!empty($dnsClients)) {
            $message .= '<br>'.__('[Served clients]').':<br>';

            foreach ($dnsClients as $list => $dnsClient) {
                $clients = '<br>';//nl2br(print_r($dnsClient, true));
                foreach ($dnsClient as $client) {
                    $clients .= $client.' - '.$rblv4->getHostByIp($client).'<br>';
                }

                $message .= "$list: $clients<br>";
            }
        }

        if (!empty($noerrorIps)) {
            $message .= '<br>'.__('[NOERROR clients]').':<br>';

            foreach ($noerrorIps as $ip) {
                $message .= $ip.' - '.$rblv4->getHostByIp($ip).'<br>';
            }
            $message .= '<br>';
        }

        if (!empty($nxdomainIps)) {
            $message .= '<br>'.__('[NXDOMAIN clients]').':<br>';

            foreach ($nxdomainIps as $ip) {
                $message .= $ip.' - '.$rblv4->getHostByIp($ip).'<br>';
            }
            $message .= '<br>';
        }

        RblLog::saveLog('crontab', __('[readlog]'), $rbllog.'<br>'.$message);

        RblLog::saveLog('crontab', __('[readlog]'), $rbllog.' ok');

        return true;
    }

    /**
     * DB maintenance/cleanup
     *
     * @throws Exception
     */
    public function cleanup(): void
    {
        $time_start = microtime(true);

        DB::enableQueryLog();
        DB::connection('mongodb')->enableQueryLog();

        $whois = !$this->cleanupWhois();

        $delete = !$this->cleanupDelete();

        $hits = !$this->cleanupHits();

        $syslog = !$this->cleanupSyslog();


        if ($whois || $delete || $hits || $syslog) {
            $time_end = microtime(true);
            $time = round($time_end - $time_start, 4);

            $logs = DB::getQueryLog();
            $mongoLogs = DB::connection('mongodb')->getQueryLog();

            $this->line("MONGO LOG: ".print_r($mongoLogs, true));
            $this->line("SQL LOG: ".print_r($logs, true));

            $this->line(__('[:method took :time seconds]', ['time' => $time, 'method' => __METHOD__]));
        }
    }

    /**
     * delete rows from whois table after 1 month
     *
     * (meaning cache whois information for 1 month)
     */
    private function cleanupWhois(): bool
    {
        try {
            Whois::where('mask', '<=', 8)->delete();

            $deleted = Whois::
                where('date', '<', DB::raw('DATE_SUB(NOW(),INTERVAL 1 MONTH)'))
                ->delete();

            //$this->line('$deleted='.print_r($deleted, true));
        } catch (Exception $e) {
            $this->line(__('[ERROR! purging whois table: :msg]', ['msg' => $e->getMessage()]));

            return false;
        }

        /*if ($deleted > 0) {
            RblLog::saveLog('crontab', __('[cleanup whois]'), __('[Deleted :deleted rows from whois table (older than 1 month)]', ['deleted' => $deleted]));
        }*/

        return true;
    }

    /**
     * cleanup rbl lists
     *
     * delete rows having delete=1,checked=1,active=0
     *
     * @return bool
     */
    private function cleanupDelete(): bool
    {
        //get lists
        $lists = DefineList::select(['name'])->get();

        // $this->line('$lists = '.print_r($lists->toArray(), true));

        foreach ($lists as $list) {
            $model = app('App\Models\\' . $list->name);

            $deleted = $model::where([
                ['delete', 1],
                ['checked', 1],
                ['active', 0]
            ])->delete();

            //$this->line('$deleted='.print_r($deleted, true));

            if ($deleted > 0) {
                RblLog::saveLog('crontab', __('[DNS delete]'), __('[Deleted :deleted rows from :list]', ['deleted' => $deleted, 'list' => $list->name]));

                DefineList::increaseSn('%');
            }
        }

        return true;
    }

    /**
     * delete rows from hits table which no longer exist in rbl lists
     *
     * (IPs deleted from lists)
     *
     * @return bool
     * @throws Exception
     */
    private function cleanupHits(): bool
    {
        $lists = DefineList::select(['name'])->get();

        //$this->line('$lists = '. print_r($lists->toArray(), true));

        foreach ($lists as $list) {
            $model = app('App\Models\\' . $list->name);

            try {
                $deleted = Hit::
                    where('list', 'App\\Models\\'.$list->name)
                    ->whereRaw('`list_id` NOT IN (SELECT `id` from '.$model->getTable().' WHERE `id`=`list_id`)')
                    ->delete();
            } catch (Exception $e) {
                $this->line(__('[ERROR! cleaning hits table: :msg]', ['msg' => $e->getMessage()]));
                return false;
            }

            //$this->line('$deleted='.print_r($deleted, true));

            if ($deleted > 0) {
                RblLog::saveLog('crontab', __('[cleanup hits]'), __('[Deleted :deleted rows from hits. IDs not found in list :list]', ['deleted' => $deleted, 'list' => $list->name]));
            }
        }

        return true;
    }

    /**
     * cleanup syslog table
     *
     * delete rows older than 7 years
     *
     * @return bool
     */
    private function cleanupSyslog(): bool
    {
        //run this only 1/day
        if (Cache::get('cleanupSyslog')) {
            // $this->line(__('[Already ran cleanupSyslog.exit.]'));
            return true;
        }

        Cache::put('cleanupSyslog', true, now()->addDays(1));

        //clean mongo db
        $this->cleanMongo();

        //delete rows older than 4 years
        $delOld = Syslog::
            where('time', '<', new DateTime('-4 years'))
            ->count();


        if ($delOld > 0) {
            RblLog::saveLog('crontab', __('[SYSLOG delete]'), __('[Deleted :deleted rows]', ['deleted' => $delOld]));
        }

        // return false;
        return true;
    }

    /**
     * delete garbage rows from mongo syslog
     */
    private function cleanMongo()
    {
        $regexps[] = '/^ mac_parse: 127\\.0\\.0\\..*$/i';
        $regexps[] = '/^ inet_addr_list_append: .*$/i';
        $regexps[] = '/^ dict_eval: const.*$/i';
        $regexps[] = '/^ dict_lookup: (inet_interfaces|mynetworks).*$/i';
        $regexps[] = '/^ dict_load_fp: .*$/i';
        $regexps[] = '/^ doveadm\(.*\): Fatal: connect\(.*\) failed: (Interrupted system call|Connection refused|Connection timed out)$/i';
        $regexps[] = '/^ Message [.A-Z0-9]+ from .* \(.*\) to .* is not spam,.*$/i';
        $regexps[] = '/^ [A-Z0-9]+: hold: header Received: from .* \(.*\)\?\?.*$/i';
        $regexps[] = '/^ connect to .*: (No route to host|Network is unreachable)$/i';
        $regexps[] = '/^ [A-Z0-9]+: conversation with .* timed out while sending MAIL FROM$/i';
        $regexps[] = '/^ [A-Z0-9]+: lost connection with .* while receiving the initial server greeting$/i';
        $regexps[] = '/^ NOQUEUE: reject: RCPT from .*: 450 4\.7\.1 <.*>: Recipient address rejected: greylisted, try again later.*$/i';
        $regexps[] = '/^ disconnect from .* (ehlo|commands)=[/0-9]+.*$/i';
        $regexps[] = '/^ SSL_accept error from .*: (Connection timed out|-1|lost connection|0|Connection reset by peer)$/i';
        $regexps[] = '/^ prepend Authentication-Results: .*; spf=(pass|none|neutral|temperror) \((mailfrom|helo|sender SPF authorized|no SPF record|SPF Temporary Error: DNS Error: exceeded max query lookup time|access neither permitted nor denied)\) smtp\.(mailfrom|helo)=.*\)$/i';
        $regexps[] = '/^ prepend Received-SPF: (Pass|None|Neutral|Temperror) \(mailfrom|no SPF record|helo\) identity=(mailfrom|no SPF record|helo); client-ip=.*; helo=.*$/i';
        $regexps[] = '/^ [A-Z0-9]+: client=.*, sasl_method=(CRAM-MD5|DIGEST-MD5|PLAIN), sasl_username=.*$/i';
        $regexps[] = '/^ Found ip-based phishing fraud from .* in [.A-Z0-9]+$/i';
        $regexps[] = '/^ message repeated [0-9]+ times: \[ Found ip-based phishing fraud from .* in [.A-Z0-9]+\]$/i';
        $regexps[] = '/^ login: .* (plaintext|CRAM-MD5|plaintext\+TLS|CRAM-MD5\+TLS) User logged in SESSIONID=<.*>$/i';
        $regexps[] = '/^ SASL sql plugin trying to open db \'.*\' on host \'.*\'$/i';
        $regexps[] = '/^ STARTTLS negotiation failed: .*\]$/i';
        $regexps[] = '/^ lost connection after (AUTH|STARTTLS|RSET|DATA \([0-9]+ bytes\)|HELO) from .*$/i';
        $regexps[] = '/^ timeout after (AUTH|DATA|CONNECT|EHLO|RSET|END-OF-MESSAGE|MAIL|UNKNOWN|STARTTLS|DATA \([0-9]+ bytes\)) from .*\]$/i';
        $regexps[] = '/^ Can\'t query (daily|bytecode|main).*\.ping\.clamav\.net$/i';
        $regexps[] = '/^ lmtp\(.*\).*: sieve: msgid=<.*>: stored mail into mailbox \'.*\'$/i';
        $regexps[] = '/^ lmtp\([0-9]+\): Disconnect from local: Client has quit the connection \(state=READY\)$/i';
        $regexps[] = '/^ Delivered: <.*> to mailbox: .*$/i';
        $regexps[] = '/^ Clamd::ERROR:: CLAM PING TIMED OUT! :: .*$/i';
        $regexps[] = '/^ [A-Z0-9]+: enabling PIX workarounds: disable_esmtp delay_dotcrlf for .*$/i';
        $regexps[] = '/^ If-Modified-Since: .*$/i';
        $regexps[] = '/^ process type:SERVICE name:.* path:\/usr\/lib\/cyrus\/bin\/.* age:[.0-9]+s pid:[0-9]+ exited, status [0-9]+$/i';
        $regexps[] = '/^ process type:(START|EVENT) name:.* path:\/usr\/sbin\/cyrus age:[.0-9]+s pid:[0-9]+ exited normally$/i';
        $regexps[] = '/^ process type:(START|SERVICE) name:.* path:\/usr\/lib\/cyrus\/bin\/.* age:[.0-9]+s pid:[0-9]+ exited normally$/i';
        $regexps[] = '/^ dupelim: eliminated duplicate message to .* \(delivery\)$/i';
        $regexps[] = '/^ Message [.A-Z0-9]+ from .* has valid watermark$/i';
        $regexps[] = '/^ --- Stopped at .*$/i';
        $regexps[] = '/^ message repeated [0-9]+ times: \[ STARTTLS negotiation failed: .*\]$/i';
        $regexps[] = '/^ improper command pipelining after (DATA|EHLO|HELO) from .*:.*$/i';
        $regexps[] = '/^ [A-Z0-9]+: conversation with .* timed out while receiving the initial server greeting$/i';
        $regexps[] = '/^ message repeated [0-9]+ times: \[ auth: cram-md5\(\?,.*\): Request timed out waiting for client to continue authentication \([0-9]+ secs\)\]$/i';
        $regexps[] = '/^ Connected to db\.[a-z]+\.clamav\.net \(IP: .*\)\.$/i';
        $regexps[] = '/^ message repeated [0-9]+ times: \[ auth-worker\([0-9]+\): sql\(.*\): unknown user \]$/i';
        //$regexps[] = '/^ $/i';
        //$regexps[] = '/^ $/i';
        //$regexps[] = '/^ $/i';
        //$regexps[] = '/^ $/i';
        //$regexps[] = '/^ $/i';

        foreach ($regexps as $regexp) {
            //$rows = Syslog::where('msg', 'regexp', $regexp)->get();
            $del = Syslog::where('msg', 'regexp', $regexp)->delete();

            //DEBUG
            /*if ($del > 2) {
                $this->line('del: ' . print_r($del, true));
                //$this->line('found: ' . print_r($rows->toArray(), true));
            }*/
        }

        $not[] = 'NOQUEUE: reject:.*';
        $not[] = 'Message [.A-Z0-9]+ from .* \(.*\) to .* is spam,.*';
        $not[] = 'badlogin: .* (user not found|authentication failure).*';
        $not[] = 'prepend Authentication-Results: .*; spf=(permerror|fail|softfail) .*';
        $not[] = 'prepend Received-SPF: (Permerror|Softfail) .*';
        $not[] = 'Message [.A-Z0-9]+ from .* is too big for spam checks .*\)';
        $not[] = '[A-Z0-9]+: reject: body .*: 5\.7\.1 SPAM';
        $not[] = 'Message [.A-Z0-9]+ from .* has no \(or invalid\) watermark or sender address';
        $not[] = 'Infected message [.A-Z0-9]+ came from .*';
        $not[] = 'too many errors after (RCPT|AUTH) from .*';
        $not[] = 'message repeated [0-9]+ times: \[ warning: .*: SASL CRAM-MD5 authentication failed: authentication failure\]';
        $not[] = '550 5\.7\.23 Message rejected due to: SPF fail.*';
        $not[] = '/var/spool/MailScanner/.*: .* FOUND';
        $not[] = '[A-Z0-9]+: reject: (RCPT|body) .*';
        // $not[] = '';
        // $not[] = '';
        // $not[] = '';
        // $not[] = '';
        // $not[] = '';


        $regex = '('.implode('|', $not).')';


        // $this->line('regex='. $regex);
        $rows = Syslog::
            where('time', '<', new DateTime('-1 day'))
            ->where('msg', 'not regexp', '/^ '.$regex.'$/i')->get();

        if (count($rows) > 0) {
            $this->line( 'extra found='.count($rows));
            $this->line('EXTRA, CHECK IT, CREATE RULES FOR THIS, EITHER DELETE OR IGNORE: '. print_r($rows->take(5)->toArray(), true));
        }
    }

    /**
     * check whois data for old IPs
     *
     * if different mark unchecked so a human checks the IP
     *
     */
    public function check(): void
    {
        $time_start = microtime(true);

        DB::enableQueryLog();

        $debug = !$this->checkOldWhois();

        $black = !$this->checkBlack();

        $hits = !$this->checkHits();

        $whois = !$this->checkWhois();

        if ($debug || $black || $hits || $whois) {
            $time_end = microtime(true);
            $time = round($time_end - $time_start, 4);

            $logs = DB::getQueryLog();

            $this->line("SQL LOG: ".print_r($logs, true));

            $this->line(__('[:method took :time seconds]', ['time' => $time, 'method' => __METHOD__]));
        }
    }

    private function checkWhois(): bool
    {
        $whoisLib = new WhoisLib();

        //get lists
        $lists = DefineList::select(['name'])->get();

        foreach ($lists as $list) {
            $model = app('App\Models\\' . $list->name);

            $toWhois = $model::
                where('last_check', '<', DB::raw('DATE_SUB(NOW(),INTERVAL 1 MONTH)'))
                ->where('active', 1)
                ->where('checked', 0)
                ->where('delete', 0)
                ->orderBy('date_added', 'asc')
                ->get();

            foreach ($toWhois as $toCheck) {
                if (is_null($toCheck)) {
                    //nothing to do
                    continue;
                }

                $ipWhois = $toCheck->long2ip . '/' . $toCheck->mask;

                $whoisLib->searchCache($ipWhois);
            }
        }

        return true;
    }
    /**
     * update last_check field
     *
     * mark unchecked if whois data is changed
     *
     * mark unchecked all IPs in white list = force human to recheck white IPs
     *
     * @return bool
     */
    private function checkOldWhois(): bool
    {
        $whoisLib = new WhoisLib();

        //get lists
        $lists = DefineList::select(['name'])->get();

        foreach ($lists as $list) {
            $model = app('App\Models\\' . $list->name);

            //special case for white
            if (stripos($list->name, 'white') !== false) {
                $toCheck = $model::
                    where('last_check', '<', DB::raw('DATE_SUB(NOW(),INTERVAL 7 MONTH)'))
                    ->where('active', 1)
                    ->where('checked', 1)
                    ->where('delete', 0)
                    ->get();

                //$this->line('$toCheck = '.print_r($toCheck->toArray(), true));

                foreach ($toCheck as $ip) {
                    $ip->last_check = date('Y-m-d H:i:s');
                    $ip->checked = 0;

                    $ipWhois = $ip->long2ip . '/' . $ip->mask;

                    //$this->line('ip='.$ipWhois);

                    $whoisLib->searchCache($ipWhois);
                    //$this->line('to save ip='. print_r($ip->toArray(), true));

                    try {
                        $ip->saveOrFail();
                    } catch (Exception $e) {
                        $this->line(__("[ERROR! saving update white ip, msg: :msg, trace:\n :trace]", ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]));
                        return false;
                    }
                }

                continue;
            }

            $toCheck = $model::
                where('last_check', '<', DB::raw('DATE_SUB(NOW(),INTERVAL 7 MONTH)'))
                ->where('active', 1)
                ->where('checked', 1)
                ->where('delete', 0)
                ->orderBy('date_added', 'asc')
                ->first();

            if (is_null($toCheck)) {
                //nothing to do
                continue;
            }

            //$this->line('$toCheck = '.print_r($toCheck->toArray(), true));

            $ipWhois = $toCheck->long2ip . '/' . $toCheck->mask;

            //$this->line('ip='.$ipWhois);

            $whoisData = $whoisLib->searchCache($ipWhois);

            //$this->line('whoisData='.print_r($whoisData, true));

            if ($whoisData['inetnum'] != $toCheck->inetnum ||
                $whoisData['netname'] != $toCheck->netname ||
                $whoisData['country'] != $toCheck->country ||
                $whoisData['orgname'] != $toCheck->orgname
            ) {
                //whois is changed, update last check and mark unchecked so a human checks the ip
                $toCheck->last_check = date('Y-m-d H:i:s');
                $toCheck->checked = 0;

                //$this->line('to save ip='. print_r($toCheck->toArray(), true));

                try {
                    $toCheck->saveOrFail();
                } catch (Exception $e) {
                    $this->line(__("[ERROR! saving update :list ip, msg: :msg, trace:\n :trace]", ['list' => $list->name, 'msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]));
                    return false;
                }
            } else {
                //$this->line($ipWhois.' no changes');

                //all ok, update last check
                $toCheck->last_check = date('Y-m-d H:i:s');

                try {
                    $toCheck->saveOrFail();
                } catch (Exception $e) {
                    $this->line(__("[ERROR! saving update :list ip, msg: :msg, trace:\n :trace]", ['list' => $list->name, 'msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]));
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * export lists to DNS
     *
     */
    public function export(): void
    {
        $time_start = microtime(true);

        if (function_exists('posix_getpwuid')) {
            $user = posix_getpwuid(posix_getuid());
            $user = $user['name'];

            if ($user != 'www-data') {
                $this->error(__('[Not running as user www-data. exit.]'));

                return;
            }
            //$this->line('user=' . print_r($user, true));
        }

        DB::enableQueryLog();

        $white = !$this->exportWhite();

        $white6 = !$this->exportWhite6();

        $grey = !$this->exportGrey();

        $grey6 = !$this->exportGrey6();

        $black = !$this->exportBlack();

        $black6 = !$this->exportBlack6();

        if ($white || $white6 || $grey || $grey6 || $black || $black6) {
            $time_end = microtime(true);
            $time = round($time_end - $time_start, 4);

            $logs = DB::getQueryLog();

            $this->line("SQL LOG: ".print_r($logs, true));

            $this->line(__('[:method took :time seconds]', ['time' => $time, 'method' => __METHOD__]));
        }
    }

    /**
     * export ipv4 White list to DNS
     *
     * @return bool
     */
    private function exportWhite(): bool
    {
        $checkList = 'White';
        $model = app('App\Models\\' . $checkList);

        //check sync
        $isSync = DefineList::isSync($checkList);

        if ($isSync) {
            //$this->info(__('[:list is synced. exit.]', ['list' => $checkList]));
            return true;
        }

        //get definition
        $setup = DefineList::where('name', $checkList)->first();

        if (is_null($setup)) {
            $this->error(__('[Missing :list list definition. Please create it in SETUP menu]', ['list' => $checkList]));
            return false;
        }

        //$this->line($checkList.' def='. print_r($setup->toArray(), true));

        $rblFile = $setup->list;

        //$this->line('Export to file '.$rblFile);

        //check file is writeable
        $canWrite = @fopen($rblFile.'.new', 'w');

        //$this->line('file check='. var_export($canWrite, true));

        if ($canWrite === false) {
            $this->error(__("[ERROR! can't open file to write, file=:file]", ['file' => $rblFile.'.new']));
            return false;
        }
        fclose($canWrite);

        $header = "
\$NS ".$setup->soansttl." ".$setup->nss."
\$SOA ".$setup->soansttl." ".$setup->primaryns." ".$setup->email." ".$setup->currentsn." ".$setup->refresh." ".$setup->retry." ".$setup->expire." ".$setup->minttl."
\$TTL ".$setup->minttl."

:2:whitelisted


";

        $written = @File::append($rblFile.'.new', $header);

        if ($written === false) {
            $this->error(__("[ERROR! can't append to file, file=:file, text: :line]", ['file' => $rblFile.'.new', 'line' => $header]));
            return false;
        }


        //get ips
        $ips = $model::
            where('active', 1)
            ->where('delete', 0)
            ->select(['iplong', 'mask'])
            ->get();

        //$this->line('ips='. print_r($ips->toArray(), true));


        if (!$this->writeIp2File($ips, $rblFile, $checkList)) {
            return false;
        }

        //rename
        if (@File::move($rblFile.'.new', $rblFile) === false) {
            $this->error(__('[ERROR! renaming file :file]', ['file' => $rblFile]));
            return false;
        }

        //sync
        DefineList::makeSync($checkList);

        return true;
    }

    /**
     * export ipv6 White list to DNS
     *
     * @return bool
     */
    private function exportWhite6(): bool
    {
        $checkList = 'White6';
        $model = app('App\Models\\' . $checkList);

        //check sync
        $isSync = DefineList::isSync($checkList);

        if ($isSync) {
            // $this->info(__('[:list is synced. exit.]', ['list' => $checkList]));
            return true;
        }

        //get definition
        $setup = DefineList::where('name', $checkList)->first();

        if (is_null($setup)) {
            $this->error(__('[Missing :list list definition. Please create it in SETUP menu]', ['list' => $checkList]));
            return false;
        }

        // $this->line($checkList.' def='. print_r($setup->toArray(), true));

        $rblFile = $setup->list;

        // $this->line('Export to file '.$rblFile);

        //check file is writeable
        $canWrite = @fopen($rblFile.'.new', 'w');

        //$this->line('file check='. var_export($canWrite, true));

        if ($canWrite === false) {
            $this->error(__("[ERROR! can't open file to write, file=:file]", ['file' => $rblFile.'.new']));
            return false;
        }
        fclose($canWrite);

        $header = "
\$NS ".$setup->soansttl." ".$setup->nss."
\$SOA ".$setup->soansttl." ".$setup->primaryns." ".$setup->email." ".$setup->currentsn." ".$setup->refresh." ".$setup->retry." ".$setup->expire." ".$setup->minttl."
\$TTL ".$setup->minttl."

:2:whitelisted


";

        $written = @File::append($rblFile.'.new', $header);

        if ($written === false) {
            $this->error(__("[ERROR! can't append to file, file=:file, text: :line]", ['file' => $rblFile.'.new', 'line' => $header]));
            return false;
        }


        //get ips
        $ips = $model::
        where('active', 1)
            ->where('delete', 0)
            ->select(['iplong', 'mask'])
            ->get();

        // $this->line('ips='. print_r($ips->toArray(), true));


        if (!$this->writeIp2File($ips, $rblFile, $checkList)) {
            return false;
        }

        //rename
        if (@File::move($rblFile.'.new', $rblFile) === false) {
            $this->error(__('[ERROR! renaming file :file]', ['file' => $rblFile]));
            return false;
        }

        //sync
        DefineList::makeSync($checkList);

        return true;
    }

    /**
     * export ipv4 Grey list to DNS
     *
     * first export White negated, so a whitelisted ip is never greylisted
     *
     * @return bool
     */
    private function exportGrey(): bool
    {
        $checkList = 'Grey';
        $model = app('App\Models\\' . $checkList);

        //check sync
        $isSync = DefineList::isSync($checkList);

        if ($isSync) {
            //$this->info(__('[:list is synced. exit.]', ['list' => $checkList]));
            return true;
        }

        //get definition
        $setup = DefineList::where('name', $checkList)->first();

        if (is_null($setup)) {
            $this->error(__('[Missing :list list definition. Please create it in SETUP menu]', ['list' => $checkList]));
            return false;
        }

        //$this->line($checkList.' def='. print_r($setup->toArray(), true));

        $rblFile = $setup->list;

        //$this->line('Export to file '.$rblFile);

        //check file is writeable
        $canWrite = @fopen($rblFile.'.new', 'w');

        //$this->line('file check='. var_export($canWrite, true));

        if ($canWrite === false) {
            $this->error(__("[ERROR! can't open file to write, file=:file]", ['file' => $rblFile.'.new']));
            return false;
        }
        fclose($canWrite);

        $header = "
\$NS ".$setup->soansttl." ".$setup->nss."
\$SOA ".$setup->soansttl." ".$setup->primaryns." ".$setup->email." ".$setup->currentsn." ".$setup->refresh." ".$setup->retry." ".$setup->expire." ".$setup->minttl."
\$TTL ".$setup->minttl."

:3:greylisted


";

        $written = @File::append($rblFile.'.new', $header);

        if ($written === false) {
            $this->error(__("[ERROR! can't append to file, file=:file, text: :line]", ['file' => $rblFile.'.new', 'line' => $header]));
            return false;
        }

        //get white ips, to negate
        $ips = White::
            where('active', 1)
            ->where('delete', 0)
            ->select(['iplong', 'mask'])
            ->get();

        //$this->line('ips='. print_r($ips->toArray(), true));


        if (!$this->writeIp2File($ips, $rblFile, 'White', true)) {
            return false;
        }

        $ips = $model::
            where('active', 1)
            ->where('delete', 0)
            ->select(['iplong', 'mask'])
            ->get();

        //$this->line('ips='. print_r($ips->toArray(), true));

        if (!$this->writeIp2File($ips, $rblFile, $checkList)) {
            return false;
        }

        //chmod($rblFile.'.new', 0444);
        //rename
        if (@File::move($rblFile.'.new', $rblFile) === false) {
            $this->error(__('[ERROR! renaming file :file]', ['file' => $rblFile]));
            return false;
        }

        //sync
        DefineList::makeSync($checkList);

        return true;
    }

    private function writeIp2File($ips, $rblFile, $list, $negate = false): bool
    {
        $line = "#ips from $list";
        $written = @File::append($rblFile.'.new', $line."\n");

        if ($written === false) {
            $this->error(__("[ERROR! can't append to file, file=:file, text: :line]", ['file' => $rblFile.'.new', 'line' => $line]));
            return false;
        }

        foreach ($ips as $ip) {
            $line = $ip->long2ip;

            if ($ip->mask != 32 || stripos($ip->long2ip, ':') !== false) {
                $line = $ip->long2ip.'/'.$ip->mask;
            }

            if ($negate) {
                $line = '!'.$line;
            }


            $written = @File::append($rblFile.'.new', $line."\n");

            if ($written === false) {
                $this->error(__("[ERROR! can't append to file, file=:file, text: :line]", ['file' => $rblFile.'.new', 'line' => $line]));
                return false;
            }
        }

        $line = "\n";
        $written = @File::append($rblFile.'.new', $line."\n");

        if ($written === false) {
            $this->error(__("[ERROR! can't append to file, file=:file, text: :line]", ['file' => $rblFile.'.new', 'line' => $line]));
            return false;
        }

        return true;
    }

    /**
     * export ipv4 Black list to DNS
     *
     * first export White negated, so a whitelisted ip is never blacklisted
     *
     * @return bool
     */
    private function exportBlack(): bool
    {
        $checkList = 'Black';
        $model = app('App\Models\\' . $checkList);

        //check sync
        $isSync = DefineList::isSync($checkList);

        if ($isSync) {
            //$this->info(__('[:list is synced. exit.]', ['list' => $checkList]));
            return true;
        }

        //get definition
        $setup = DefineList::where('name', $checkList)->first();

        if (is_null($setup)) {
            $this->error(__('[Missing :list list definition. Please create it in SETUP menu]', ['list' => $checkList]));
            return false;
        }

        //$this->line($checkList.' def='. print_r($setup->toArray(), true));

        $rblFile = $setup->list;

        //$this->line('Export to file '.$rblFile);

        //check file is writeable
        $canWrite = @fopen($rblFile.'.new', 'w');

        //$this->line('file check='. var_export($canWrite, true));

        if ($canWrite === false) {
            $this->error(__("[ERROR! can't open file to write, file=:file]", ['file' => $rblFile.'.new']));
            return false;
        }
        fclose($canWrite);

        $header = "
\$NS ".$setup->soansttl." ".$setup->nss."
\$SOA ".$setup->soansttl." ".$setup->primaryns." ".$setup->email." ".$setup->currentsn." ".$setup->refresh." ".$setup->retry." ".$setup->expire." ".$setup->minttl."
\$TTL ".$setup->minttl."

:4:blacklisted


";

        $written = @File::append($rblFile.'.new', $header);

        if ($written === false) {
            $this->error(__("[ERROR! can't append to file, file=:file, text: :line]", ['file' => $rblFile.'.new', 'line' => $header]));
            return false;
        }

        //get white ips, to negate
        $ips = White::
            where('active', 1)
            ->where('delete', 0)
            ->select(['iplong', 'mask'])
            ->get();

        //$this->line('ips='. print_r($ips->toArray(), true));


        if (!$this->writeIp2File($ips, $rblFile, 'White', true)) {
            return false;
        }

        $ips = $model::
            where('active', 1)
            ->where('delete', 0)
            ->select(['iplong', 'mask'])
            ->get();

        //$this->line('ips='. print_r($ips->toArray(), true));

        if (!$this->writeIp2File($ips, $rblFile, $checkList)) {
            return false;
        }

        //chmod($rblFile.'.new', 0444);
        //rename
        if (@File::move($rblFile.'.new', $rblFile) === false) {
            $this->error(__('[ERROR! renaming file :file]', ['file' => $rblFile]));
            return false;
        }

        //sync
        DefineList::makeSync($checkList);

        return true;
    }

    /**
     * check black to be present in grey
     * @return bool
     */
    private function checkBlack(): bool
    {
        //run this only 1/day
        if (Cache::get('checkBlack')) {
            // $this->line(__('[Already ran checkBlack.exit.]'));
            return true;
        }

        Cache::put('checkBlack', true, now()->addDays(1));

        Black::
            where('checked', 1)
            ->whereNotExists(function ($query) {
                $lastIp = DB::raw("(`iplong`+(pow(2,32-`mask`)-1))");

                $grey = new Grey();

                $query->select(DB::raw(1))
                    ->from($grey->getTable())
                    ->whereBetween('blacks.iplong', [DB::raw('`iplong`'), $lastIp]);
            })
            ->update(['checked' => 0]);

        Black6::
            where('checked', 1)
            ->whereNotExists(function ($query) {
                $lastIp = DB::raw("(INET6_ATON(LastIPv6MatchingCIDR(INET6_NTOA(`iplong`), `mask`)))");

                $grey6 = new Grey6();

                $query->select(DB::raw(1))
                    ->from($grey6->getTable())
                    ->whereBetween('black6s.iplong', [DB::raw('`iplong`'), $lastIp]);
            })
            ->update(['checked' => 0]);

        //$this->line( 'black found='.print_r($rows, true));

        return true;
    }

    /**
     * to check old ip with no recent hits
     *
     * @return bool
     */
    private function checkHits(): bool
    {
        //run this only 1/day
        if (Cache::get('checkHits')) {
            // $this->line(__('[Already ran checkHits.exit.]'));
            return true;
        }

        $intervalLastCheck = 180;//180=6 months    -- last checked $months ago
        $intervalAdded = 4;//4 years     -- added $years ago
        $intervalHits = 3;//3 years -- no hits in last 3 years

        //get lists
        $lists = DefineList::select(['name'])->get();

        foreach ($lists as $list) {
            $model = app('App\Models\\' . $list->name);

            $latestHits = Hit::
                select('list_id', DB::raw('MAX(DATE(CONCAT_WS("-", `year`, `month`,`day`))) as last_hit_created_at'))
                ->where('list', 'App\Models\\'.$list->name)
                ->groupBy('list_id');

            $result = $model::
                where('checked', 1)
                ->where('last_check', '<', DB::raw('DATE_SUB(NOW(),INTERVAL '.$intervalLastCheck.' DAY)'))
                ->where('date_added', '<', DB::raw('DATE_SUB(NOW(),INTERVAL '.$intervalAdded.' YEAR)'))
                ->leftJoinSub($latestHits, 'latest_hits', function ($join) {
                    $join->on('id', '=', 'latest_hits.list_id');
                })
                ->where(function($query) use($intervalHits) {
                    $query->WhereNull('latest_hits.last_hit_created_at')
                        ->orWhere('latest_hits.last_hit_created_at', '<', DB::raw('DATE_SUB(NOW(), INTERVAL '.$intervalHits.' YEAR)'));
                })
                // ->get();
                ->update(['checked' => 0]);

            // $this->line('res '.$list->name.'='. print_r(count($result->toArray()), true));

            if (is_numeric($result) && $result > 0) {
                $this->line('to check ' . $list->name . '=' . print_r($result, true));
            }
        }

        Cache::put('checkHits', true, now()->addDays(1));

        return true;
    }

    public function checkRegex()
    {
        $time_start = microtime(true);

        DB::enableQueryLog();

        $debug = !$this->runRegex();

        if ($debug) {
            $time_end = microtime(true);
            $time = round($time_end - $time_start, 4);

            $logs = DB::getQueryLog();

            $this->line("SQL LOG: ".print_r($logs, true));

            $this->line(__('[:method took :time seconds]', ['time' => $time, 'method' => __METHOD__]));
        }
    }

    private function runRegex(): bool
    {
        //run this only 1/day
        if (Cache::get('runRegex')) {
            //$this->line(__('[Already ran runRegex.exit.]'));
            return true;
        }



        // return true;
        set_time_limit(1200);

        $lists = Rbl4::getLists();
        $c4 = new Rbl4();

        foreach ($lists as $list) {
            $model = app('App\Models\\' . $list);

            //get ips from list
            $ips = $model::
                whereNotIn('mask', ['32', '24', '16'])
                ->orderBy('mask', 'desc')
                ->get();

            $this->line('List '.$list.' ips count='.count($ips));

            foreach ($ips as $ip) {
                //$this->line('check ip=' . print_r($ip->toArray(), true));

                //get regex
                $regex = $c4->rangeToRegex($ip->iplong, $ip->mask);

                // $this->line('regex='.$regex);

                //make range
                $cidr = $ip->long2ip.'/'.$ip->mask;

                $range = $c4->getRange($cidr);

                $lastIp = ip2long($range['high']);

                // $this->line('range='. print_r($range, true));

                for ($i = $ip->iplong; $i <= $lastIp; $i++) {
                    // $this->line('check ip='. long2ip($i));

                    try {
                        if (!preg_match('/'.$regex.'/i', long2ip($i))) {
                            $this->line("regex=$regex, did not match ".long2ip($i).", cidr=$cidr, list=$list, range=".print_r($range, true));
                            return true;
                        }
                    } catch (Exception $e) {
                        $this->line(
                            "ERROR! msg=".$e->getMessage().
                            ", regex=$regex, cidr=$cidr, list=$list, range=".print_r($range, true)
                        );
                        return true;
                    }
                }
            }
        }

        Cache::put('runRegex', true, now()->addDays(1));

        $this->line('finish '.__METHOD__);
        return true;
    }

    /**
     * export ipv6 Grey list to DNS
     *
     * first export White negated, so a whitelisted ip is never greylisted
     *
     * @return bool
     */
    private function exportGrey6(): bool
    {
        $checkList = 'Grey6';
        $model = app('App\Models\\' . $checkList);

        //check sync
        $isSync = DefineList::isSync($checkList);

        if ($isSync) {
            // $this->info(__('[:list is synced. exit.]', ['list' => $checkList]));
            return true;
        }

        //get definition
        $setup = DefineList::where('name', $checkList)->first();

        if (is_null($setup)) {
            $this->error(__('[Missing :list list definition. Please create it in SETUP menu]', ['list' => $checkList]));
            return false;
        }

        //$this->line($checkList.' def='. print_r($setup->toArray(), true));

        $rblFile = $setup->list;

        //$this->line('Export to file '.$rblFile);

        //check file is writeable
        $canWrite = @fopen($rblFile.'.new', 'w');

        //$this->line('file check='. var_export($canWrite, true));

        if ($canWrite === false) {
            $this->error(__("[ERROR! can't open file to write, file=:file]", ['file' => $rblFile.'.new']));
            return false;
        }
        fclose($canWrite);

        $header = "
\$NS ".$setup->soansttl." ".$setup->nss."
\$SOA ".$setup->soansttl." ".$setup->primaryns." ".$setup->email." ".$setup->currentsn." ".$setup->refresh." ".$setup->retry." ".$setup->expire." ".$setup->minttl."
\$TTL ".$setup->minttl."

:3:greylisted


";

        $written = @File::append($rblFile.'.new', $header);

        if ($written === false) {
            $this->error(__("[ERROR! can't append to file, file=:file, text: :line]", ['file' => $rblFile.'.new', 'line' => $header]));
            return false;
        }

        //get white ips, to negate
        $ips = White6::
            where('active', 1)
            ->where('delete', 0)
            ->select(['iplong', 'mask'])
            ->get();

        //$this->line('ips='. print_r($ips->toArray(), true));


        if (!$this->writeIp2File($ips, $rblFile, 'White', true)) {
            return false;
        }

        $ips = $model::
            where('active', 1)
            ->where('delete', 0)
            ->select(['iplong', 'mask'])
            ->get();

        //$this->line('ips='. print_r($ips->toArray(), true));

        if (!$this->writeIp2File($ips, $rblFile, $checkList)) {
            return false;
        }

        //chmod($rblFile.'.new', 0444);
        //rename
        if (@File::move($rblFile.'.new', $rblFile) === false) {
            $this->error(__('[ERROR! renaming file :file]', ['file' => $rblFile]));
            return false;
        }

        //sync
        DefineList::makeSync($checkList);

        return true;
    }

    /**
     * export ipv6 Black list to DNS
     *
     * first export White negated, so a whitelisted ip is never blacklisted
     *
     * @return bool
     */
    private function exportBlack6(): bool
    {
        $checkList = 'Black6';
        $model = app('App\Models\\' . $checkList);

        //check sync
        $isSync = DefineList::isSync($checkList);

        if ($isSync) {
            // $this->info(__('[:list is synced. exit.]', ['list' => $checkList]));
            return true;
        }

        //get definition
        $setup = DefineList::where('name', $checkList)->first();

        if (is_null($setup)) {
            $this->error(__('[Missing :list list definition. Please create it in SETUP menu]', ['list' => $checkList]));
            return false;
        }

        //$this->line($checkList.' def='. print_r($setup->toArray(), true));

        $rblFile = $setup->list;

        //$this->line('Export to file '.$rblFile);

        //check file is writeable
        $canWrite = @fopen($rblFile.'.new', 'w');

        //$this->line('file check='. var_export($canWrite, true));

        if ($canWrite === false) {
            $this->error(__("[ERROR! can't open file to write, file=:file]", ['file' => $rblFile.'.new']));
            return false;
        }
        fclose($canWrite);

        $header = "
\$NS ".$setup->soansttl." ".$setup->nss."
\$SOA ".$setup->soansttl." ".$setup->primaryns." ".$setup->email." ".$setup->currentsn." ".$setup->refresh." ".$setup->retry." ".$setup->expire." ".$setup->minttl."
\$TTL ".$setup->minttl."

:4:blacklisted


";

        $written = @File::append($rblFile.'.new', $header);

        if ($written === false) {
            $this->error(__("[ERROR! can't append to file, file=:file, text: :line]", ['file' => $rblFile.'.new', 'line' => $header]));
            return false;
        }

        //get white ips, to negate
        $ips = White6::
            where('active', 1)
            ->where('delete', 0)
            ->select(['iplong', 'mask'])
            ->get();

        //$this->line('ips='. print_r($ips->toArray(), true));


        if (!$this->writeIp2File($ips, $rblFile, 'White', true)) {
            return false;
        }

        $ips = $model::
            where('active', 1)
            ->where('delete', 0)
            ->select(['iplong', 'mask'])
            ->get();

        //$this->line('ips='. print_r($ips->toArray(), true));

        if (!$this->writeIp2File($ips, $rblFile, $checkList)) {
            return false;
        }

        //chmod($rblFile.'.new', 0444);
        //rename
        if (@File::move($rblFile.'.new', $rblFile) === false) {
            $this->error(__('[ERROR! renaming file :file]', ['file' => $rblFile]));
            return false;
        }

        //sync
        DefineList::makeSync($checkList);

        return true;
    }
}
