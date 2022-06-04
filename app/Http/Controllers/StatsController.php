<?php /** @noinspection PhpUnusedAliasInspection */

namespace App\Http\Controllers;

use App\Http\Resources\ElasticResource;
use App\Http\Resources\HitsResource;
use App\Http\Resources\LogsResource;
use App\Models\Hit;
use App\Models\RblLog;
use App\Models\Syslog;
use App\Models\MailLog;
use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Elasticsearch;

use App\Helpers\Rbl4;
use App\Helpers\Rbl6;
use Inertia\Response;
use MongoDB\BSON\Regex;
use PhpParser\Node\Stmt\Catch_;

class StatsController extends Controller
{
    //

    public function index(): Response
    {
        /*$flash = [
            'msg_success' => 'test success',
            'msg_error' => 'test error',
            'msg_warning' => 'test warning',
            'msg_info' => 'test info'
        ];*/
        $data = array();
        $ips = array();
        $classes = array();

        $lists = Rbl4::getLists();
        $lists6 = Rbl6::getLists();

        // DB::enableQueryLog();


        try {
            foreach ($lists as $list) {
                $model = app('App\Models\\' . $list);

                $data[$list] = number_format($model->count(), 0, ",", ".");

                $ip[$list] = $model->ips24();

                $classes16 = $model->classes16();

                // dump($ip[$list]->toArray());

                foreach ($ip[$list] as $item) {
                    $ips[] = [
                        'ip1' => $item->ip1,
                        'ip2' => $item->ip2,
                        'ip3' => $item->ip3,
                        'ip4' => $item->ip4,
                        'inetnum' => $item->inetnum,
                        'list' => '<a href="' .
                            URL::route('rbl.show4', ['id' => $item->id, 'list' => $list])
                            . '">' . $list . '</a>'
                    ];
                }

                foreach ($classes16 as $item) {
                    $classes[] = [
                        'ip1' => $item->ip1,
                        'ip2' => $item->ip2,
                        'ip3' => $item->ip3,
                        'ip4' => $item->ip4,
                        'inetnum' => $item->inetnum,
                        'list' => '<a href="' .
                            URL::route('rbl.show4', ['id' => $item->id, 'list' => $list])
                            . '">' . $list . '</a>'
                    ];
                }
            }

            foreach ($lists6 as $list6) {
                $model = app('App\Models\\' . $list6);

                $data[$list6] = number_format($model->count(), 0, ",", ".");
            }
        } catch (Exception $e) {
            Log::error(
                __METHOD__ .
                ' error: ' . $e->getMessage() .
                "\n" . $e->getTraceAsString() . "\n"
            );
        }

        // dump(DB::getQueryLog());

        /*$c4 = new Rbl4();

        $range = $c4->rangeToRegex(ip2long('171.100.0.0'), 17);

        dump($range);*/

        $cache = env('CACHE_DRIVER');

        // dump($cache);

        $stats = [];

        if ($cache == 'memcached') {
            $stats = Cache::getMemcached()->getStats();

            if (isset($stats['127.0.0.1:11211'])) {
                $stats = $stats['127.0.0.1:11211'];
            }
        }

        try {
            $mongo = Syslog::count();
        } catch (Exception $e) {
            $mongo = 'error getting count';

            Log::error(
                __METHOD__ .
                ' error: ' . $e->getMessage() .
                // "\n".$e->getTraceAsString().
                "\n\n"
            );
        }

        try {
            $elastic = 'not yet';
        } catch (Exception $e) {
            $elastic = 'error getting count';

            Log::error(
                __METHOD__ .
                ' error: ' . $e->getMessage() .
                // "\n".$e->getTraceAsString().
                "\n\n"
            );
        }

        return Inertia::render('Stats/Index', [
            'data' => $data,
            'ips' => $ips,
            'classes' => $classes,
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'stats' => $stats,
            'mongo' => $mongo,
            'elastic' => $elastic,
            // 'flash' => $flash
        ]);
    }

    public function stats(): JsonResponse
    {
        $cache = env('CACHE_DRIVER');
        $c4 = new Rbl4();
        $c6 = new Rbl6();

        if (function_exists('sys_getloadavg')) {
            $loadAvg = sys_getloadavg();
        } else {
            $loadAvg[0] = 'sys_getloadavg() missing';
            $loadAvg[1] = '';
            $loadAvg[2] = '';
        }

        //dump($loadAvg);
        $data['loadAvg'] = $loadAvg;
        $data['date'] = date('H:i:s');
        $data['cache'] = false;

        if ($cache == 'memcached') {
            $isPristine = Cache::getMemcached()->isPristine();
            // dump($isPristine);

            $data['cache'] = $isPristine;
        }

        $data['logCount'] = RblLog::where('read', false)->count();

        $data['lists4'] = $c4->stats();
        $data['lists6'] = $c6->stats();

        return response()->json($data);
    }

    public function elastic(Request $request, string $iplong, int $mask)
    {
        $ipv6 = false;
        $c4 = new Rbl4();
        $c6 = new Rbl6();
        $table = [];

        if (stripos($iplong, ':') !== false) {
            $ipv6 = true;
            $ipAddr = $iplong;
        } else {
            $ipAddr = long2ip($iplong);
        }

        $cidrInfo = $ipAddr . '/' . $mask;

        if ($ipv6) {
            $rangeInfo = $c6->getRange($cidrInfo, 'string');

        } else {
            $rangeInfo = $c4->getRange($cidrInfo, 'string');
        }

        try {
            // DB::connection('elasticsearch')->enableQueryLog();

            $data = MailLog::where('client.ip', $cidrInfo)->paginate(intval($request->perPage));
            /*rawSearch([
                "query" => [
                    "term" => [
                        "client.ip" => "1.1.1.0/25"
                    ]
                ],
            ])->paginate(intval($request->perPage));*/
           /* rawSearch([
                'index' => '.ds-filebeat-*',
                'body' => [
                    "query" => [
                        "term" => [
                            "client.ip" => "1.1.1.0/25"
                        ]
                    ],
                    "size" => intval($request->perPage),
                    "from" => isset($request->from) ? ($request->from-1) : 0,
                    "sort" => [
                        [
                            $request->column ?? "@timestamp" => [
                                "unmapped_type" => "keyword",
                                "order" => $request->order ?? "desc"
                            ]
                        ]
                    ]
                ]
            ])->paginate(intval($request->perPage));*/
            /*(['time', 'sys', 'msg'])
                ->where('msg', 'regex', '/[^.0-9]'.$cidrInfo.'/i')
                ->orderBy($request->column ?? 'time', $request->order ?? 'desc')
                ->paginate(intval($request->perPage));*/

            // dump(DB::connection('elasticsearch')->getQueryLog());
            /*Log::debug(
                __METHOD__ .
                " query: \n" .
                print_r(DB::connection('elasticsearch')->getQueryLog(), true) .
                "\n"
            );*/

            // dump($data);
            /*Log::debug(
                __METHOD__.
                " data: \n".
                print_r($data, true).
                "\n\n"
            );*/

            foreach ($data as $row) {
                $row->msg2 = '<pre>'.print_r($row, true).'</pre>';
                // $row->msg2 = preg_replace('/(' . $regexInfo . '?)/', '<code>\1</code>', htmlentities($row->msg));
            }

            // $table = ElasticResource::collection($data);
            $table = LogsResource::collection($data);
            // $table = $data;

            $table->additional = [
                'rangeInfo' => $rangeInfo,
                'regexInfo' => $cidrInfo
            ];
        } catch (Exception $e) {
            Log::error(
                __METHOD__ .
                ' error: ' . $e->getMessage() .
                "\n".$e->getTraceAsString().
                "\n\n"
            );

            return response('', 400);
        }

        // dump($table);

        return $table;
    }

    public function syslog(Request $request, string $iplong, int $mask)
    {
        $ipv6 = false;

        $c4 = new Rbl4();
        $c6 = new Rbl6();

        // dump($iplong);

        if (stripos($iplong, ':') !== false) {
            $ipv6 = true;
            $ipAddr = $iplong;
        } else {
            $ipAddr = long2ip($iplong);
        }

        $cidrInfo = $ipAddr . '/' . $mask;

        if ($ipv6) {
            $rangeInfo = $c6->getRange($cidrInfo, 'string');

            $regexInfo = $c6->rangeToRegex($iplong, $mask);
        } else {
            $rangeInfo = $c4->getRange($cidrInfo, 'string');

            $regexInfo = $c4->rangeToRegex($iplong, $mask);
        }

        // dump($rangeInfo);

        try {
            // DB::connection('mongodb')->enableQueryLog();

            $data = Syslog::
            select(['time', 'sys', 'msg'])
                ->where('msg', 'regex', '/[^.0-9]' . $regexInfo . '/i')
                ->orderBy($request->column ?? 'time', $request->order ?? 'desc')
                ->paginate(intval($request->perPage));

            // dump(DB::connection('mongodb')->getQueryLog());
            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::connection('mongodb')->getQueryLog(), true).
                "\n"
            );*/

            foreach ($data as $row) {
                $row->msg2 = preg_replace('/(' . $regexInfo . '?)/', '<code>\1</code>', htmlentities($row->msg));
            }

            $table = LogsResource::collection($data);

            $table->additional = [
                'rangeInfo' => $rangeInfo,
                'regexInfo' => $regexInfo
            ];
        } catch (Exception $e) {
            Log::error(
                __METHOD__ .
                ' error: ' . $e->getMessage() .
                //"\n".$e->getTraceAsString().
                "\n\n"
            );

            return response('', 400);
        }


        // dump($table);

        return $table;
    }
}
