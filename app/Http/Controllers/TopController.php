<?php

namespace App\Http\Controllers;

use App\Helpers\Rbl4;
use App\Helpers\Rbl6;
use App\Http\Resources\LogsResource;
use App\Http\Resources\TopHitsResource;
use App\Models\Hit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TopController extends Controller
{
    //
    public function index(): Response
    {
        $lists = Rbl4::getLists();
        $lists6 = Rbl6::getLists();

        return Inertia::render('Top/Index', [
            'lists' => $lists,
            'lists6' => $lists6
        ]);
    }

    public function topIp1(Request $request, $showList = 'White')
    {
        $model = app('App\Models\\' . $showList);
        $model = new $model();

        $ipv6 = false;
        if (stripos($showList, '6')) {
            $ipv6 = true;
        }

        $searchField = 'id';
        $searchValue = '';
        foreach ($request->input('search') as $field => $value) {
            //
            if (!empty($value)) {
                switch ($field) {
                    case 'ip':
                        if (stripos($showList, '6')) {
                            $searchField = DB::raw('INET6_NTOA(`iplong`)');
                        } else {
                            $searchField = DB::raw('INET_NTOA(`iplong`)');
                        }
                        break;

                    case 'date_added':
                        $searchField = DB::raw('DATE_FORMAT(`date_added`, "%d %M %Y, %H:%i:%s")');
                        break;

                    case 'last_check':
                        $searchField = DB::raw('DATE_FORMAT(`last_check`, "%d %M %Y, %H:%i:%s")');
                        break;

                    default:
                        $searchField = $field;
                        break;
                }
                $searchValue = $value;
            }
        }

        // DB::enableQueryLog();

        try {
            $data = $model
                ->orderBy($request->column ?? 'total_ip', $request->order ?? 'desc')
                ->groupBy('ip1')
                ->select([
                    DB::raw('@total := @total + 1 AS `index`'),
                    DB::raw('SUM(POW(2,'.($ipv6 ? 128 : 32).'-`mask`)) AS `total_ip`'),
                    DB::raw('COUNT(*) AS `row_count`'),
                    'ip1',
                    DB::raw('MIN(`date_added`) AS `date_added`'),
                    DB::raw('MIN(`last_check`) AS `last_check`'),
                    DB::raw('MIN(`iplong`) AS `iplong`')
                ])
                ->crossJoin(DB::raw("(SELECT @total := 0) AS `fakeTotal`"))

                ->where($searchField, 'like', '%'.$searchValue.'%')
                ->paginate($request->perPage);
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage().
                ", line=".$e->getLine().
                "\n"
            );

            return response('', 400);
        }

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return LogsResource::collection($data);
    }

    public function topCountry(Request $request, $showList = 'White')
    {
        $model = app('App\Models\\' . $showList);
        $model = new $model();

        $ipv6 = false;
        if (stripos($showList, '6')) {
            $ipv6 = true;
        }

        $searchField = 'id';
        $searchValue = '';
        foreach ($request->input('search') as $field => $value) {
            //
            if (!empty($value)) {
                switch ($field) {
                    case 'ip':
                        if (stripos($showList, '6')) {
                            $searchField = DB::raw('INET6_NTOA(`iplong`)');
                        } else {
                            $searchField = DB::raw('INET_NTOA(`iplong`)');
                        }
                        break;

                    case 'date_added':
                        $searchField = DB::raw('DATE_FORMAT(`date_added`, "%d %M %Y, %H:%i:%s")');
                        break;

                    case 'last_check':
                        $searchField = DB::raw('DATE_FORMAT(`last_check`, "%d %M %Y, %H:%i:%s")');
                        break;

                    default:
                        $searchField = $field;
                        break;
                }
                $searchValue = $value;
            }
        }

        // DB::enableQueryLog();

        try {
            $data = $model
                ->orderBy($request->column ?? 'total_ip', $request->order ?? 'desc')
                ->groupBy('country')
                ->select([
                    DB::raw('@total := @total + 1 AS `index`'),
                    DB::raw('SUM(POW(2,'.($ipv6 ? 128 : 32).'-`mask`)) AS `total_ip`'),
                    DB::raw('COUNT(*) AS `row_count`'),
                    'country',
                    DB::raw('MIN(`date_added`) AS `date_added`'),
                    DB::raw('MIN(`last_check`) AS `last_check`'),
                    DB::raw('MIN(`iplong`) AS `iplong`')
                ])
                ->crossJoin(DB::raw("(SELECT @total := 0) AS `fakeTotal`"))

                ->where($searchField, 'like', '%'.$searchValue.'%')
                ->paginate($request->perPage);
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage().
                ", line=".$e->getLine().
                "\n"
            );

            return response('', 400);
        }

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return LogsResource::collection($data);
    }

    public function topNetname(Request $request, $showList = 'White')
    {
        $model = app('App\Models\\' . $showList);
        $model = new $model();

        $ipv6 = false;
        if (stripos($showList, '6')) {
            $ipv6 = true;
        }

        $searchField = 'id';
        $searchValue = '';
        foreach ($request->input('search') as $field => $value) {
            //
            if (!empty($value)) {
                switch ($field) {
                    case 'ip':
                        if (stripos($showList, '6')) {
                            $searchField = DB::raw('INET6_NTOA(`iplong`)');
                        } else {
                            $searchField = DB::raw('INET_NTOA(`iplong`)');
                        }
                        break;

                    case 'date_added':
                        $searchField = DB::raw('DATE_FORMAT(`date_added`, "%d %M %Y, %H:%i:%s")');
                        break;

                    case 'last_check':
                        $searchField = DB::raw('DATE_FORMAT(`last_check`, "%d %M %Y, %H:%i:%s")');
                        break;

                    default:
                        $searchField = $field;
                        break;
                }
                $searchValue = $value;
            }
        }

        // DB::enableQueryLog();

        try {
            $data = $model
                ->orderBy($request->column ?? 'total_ip', $request->order ?? 'desc')
                ->groupBy('netname')
                ->select([
                    DB::raw('@total := @total + 1 AS `index`'),
                    DB::raw('SUM(POW(2,'.($ipv6 ? 128 : 32).'-`mask`)) AS `total_ip`'),
                    DB::raw('COUNT(*) AS `row_count`'),
                    'netname',
                    DB::raw('MIN(`date_added`) AS `date_added`'),
                    DB::raw('MIN(`last_check`) AS `last_check`'),
                    DB::raw('MIN(`iplong`) AS `iplong`')
                ])
                ->crossJoin(DB::raw("(SELECT @total := 0) AS `fakeTotal`"))

                ->where($searchField, 'like', '%'.$searchValue.'%')
                ->paginate($request->perPage);
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage().
                ", line=".$e->getLine().
                "\n"
            );

            return response('', 400);
        }

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return LogsResource::collection($data);
    }

    public function topInetnum(Request $request, $showList = 'White')
    {
        $model = app('App\Models\\' . $showList);
        $model = new $model();

        $ipv6 = false;
        if (stripos($showList, '6')) {
            $ipv6 = true;
        }

        $searchField = 'id';
        $searchValue = '';
        foreach ($request->input('search') as $field => $value) {
            //
            if (!empty($value)) {
                switch ($field) {
                    case 'ip':
                        if (stripos($showList, '6')) {
                            $searchField = DB::raw('INET6_NTOA(`iplong`)');
                        } else {
                            $searchField = DB::raw('INET_NTOA(`iplong`)');
                        }

                        break;

                    case 'date_added':
                        $searchField = DB::raw('DATE_FORMAT(`date_added`, "%d %M %Y, %H:%i:%s")');
                        break;

                    case 'last_check':
                        $searchField = DB::raw('DATE_FORMAT(`last_check`, "%d %M %Y, %H:%i:%s")');
                        break;

                    default:
                        $searchField = $field;
                        break;
                }
                $searchValue = $value;
            }
        }

        // DB::enableQueryLog();

        try {
            $data = $model
                ->orderBy($request->column ?? 'total_ip', $request->order ?? 'desc')
                ->groupBy('inetnum')
                ->select([
                    DB::raw('@total := @total + 1 AS `index`'),
                    DB::raw('SUM(POW(2,'.($ipv6 ? 128 : 32).'-`mask`)) AS `total_ip`'),
                    DB::raw('COUNT(*) AS `row_count`'),
                    'inetnum',
                    DB::raw('MIN(`date_added`) AS `date_added`'),
                    DB::raw('MIN(`last_check`) AS `last_check`'),
                    DB::raw('MIN(`iplong`) AS `iplong`')
                ])
                ->crossJoin(DB::raw("(SELECT @total := 0) AS `fakeTotal`"))

                ->where($searchField, 'like', '%'.$searchValue.'%')
                ->paginate($request->perPage);
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage().
                ", line=".$e->getLine().
                "\n"
            );

            return response('', 400);
        }

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return LogsResource::collection($data);
    }

    public function topOrgname(Request $request, $showList = 'White')
    {
        $model = app('App\Models\\' . $showList);
        $model = new $model();

        $ipv6 = false;
        if (stripos($showList, '6')) {
            $ipv6 = true;
        }

        $searchField = 'id';
        $searchValue = '';
        foreach ($request->input('search') as $field => $value) {
            //
            if (!empty($value)) {
                switch ($field) {
                    case 'ip':
                        if (stripos($showList, '6')) {
                            $searchField = DB::raw('INET6_NTOA(`iplong`)');
                        } else {
                            $searchField = DB::raw('INET_NTOA(`iplong`)');
                        }
                        break;

                    case 'date_added':
                        $searchField = DB::raw('DATE_FORMAT(`date_added`, "%d %M %Y, %H:%i:%s")');
                        break;

                    case 'last_check':
                        $searchField = DB::raw('DATE_FORMAT(`last_check`, "%d %M %Y, %H:%i:%s")');
                        break;

                    default:
                        $searchField = $field;
                        break;
                }
                $searchValue = $value;
            }
        }

        // DB::enableQueryLog();

        try {
            $data = $model
                ->orderBy($request->column ?? 'total_ip', $request->order ?? 'desc')
                ->groupBy('orgname')
                ->select([
                    DB::raw('@total := @total + 1 AS `index`'),
                    DB::raw('SUM(POW(2,'.($ipv6 ? 128 : 32).'-`mask`)) AS `total_ip`'),
                    DB::raw('COUNT(*) AS `row_count`'),
                    'orgname',
                    DB::raw('MIN(`date_added`) AS `date_added`'),
                    DB::raw('MIN(`last_check`) AS `last_check`'),
                    DB::raw('MIN(`iplong`) AS `iplong`')
                ])
                ->crossJoin(DB::raw("(SELECT @total := 0) AS `fakeTotal`"))

                ->where($searchField, 'like', '%'.$searchValue.'%')
                ->paginate($request->perPage);
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage().
                ", line=".$e->getLine().
                "\n"
            );

            return response('', 400);
        }

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return LogsResource::collection($data);
    }

    public function topDateAdded(Request $request, $showList = 'White')
    {
        $model = app('App\Models\\' . $showList);
        $model = new $model();

        $ipv6 = false;
        if (stripos($showList, '6')) {
            $ipv6 = true;
        }

        $searchField = 'id';
        $searchValue = '';
        foreach ($request->input('search') as $field => $value) {
            //
            if (!empty($value)) {
                switch ($field) {
                    case 'ip':
                        if (stripos($showList, '6')) {
                            $searchField = DB::raw('INET6_NTOA(`iplong`)');
                        } else {
                            $searchField = DB::raw('INET_NTOA(`iplong`)');
                        }
                        break;

                    case 'date_added':
                        $searchField = DB::raw('DATE_FORMAT(`date_added`, "%d %M %Y, %H:%i:%s")');
                        break;

                    case 'year_added':
                        $searchField = DB::raw('YEAR(`date_added`)');
                        break;

                    case 'last_check':
                        $searchField = DB::raw('DATE_FORMAT(`last_check`, "%d %M %Y, %H:%i:%s")');
                        break;

                    default:
                        $searchField = $field;
                        break;
                }
                $searchValue = $value;
            }
        }

        // DB::enableQueryLog();

        try {
            $data = $model
                ->orderBy($request->column ?? 'total_ip', $request->order ?? 'desc')
                ->groupBy(DB::raw('YEAR(`date_added`)'))
                ->select([
                    DB::raw('@total := @total + 1 AS `index`'),
                    DB::raw('SUM(POW(2,'.($ipv6 ? 128 : 32).'-`mask`)) AS `total_ip`'),
                    DB::raw('COUNT(*) AS `row_count`'),
                    DB::raw('YEAR(`date_added`) AS `year_added`'),
                    DB::raw('MIN(`date_added`) AS `date_added`'),
                    DB::raw('MIN(`last_check`) AS `last_check`'),
                    DB::raw('MIN(`iplong`) AS `iplong`')
                ])
                ->crossJoin(DB::raw("(SELECT @total := 0) AS `fakeTotal`"))

                ->where($searchField, 'like', '%'.$searchValue.'%')
                ->paginate($request->perPage);
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage().
                ", line=".$e->getLine().
                "\n"
            );

            return response('', 400);
        }

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return LogsResource::collection($data);
    }

    public function topLastHit(Request $request, $showList = 'White')
    {
        $model = app('App\Models\\' . $showList);
        $model = new $model();

        $ipv6 = false;
        if (stripos($showList, '6')) {
            $ipv6 = true;
        }


        $searchField = 'hits.id';
        $searchValue = '';
        foreach ($request->input('search') as $field => $value) {
            //
            if (!empty($value)) {
                switch ($field) {
                    case 'format_cidr':
                        if ($ipv6) {
                            $searchField = DB::raw('CONCAT(INET6_NTOA(`iplong`),"/",`mask`)');
                        } else {
                            $searchField = DB::raw('CONCAT(INET_NTOA(`iplong`),"/",`mask`)');
                        }
                        break;

                    default:
                        $searchField = $field;
                        break;
                }
                $searchValue = $value;
            }
        }

        // DB::enableQueryLog();

        try {
            $data = Hit::
                orderBy($request->column ?? 'hits.updated_at', $request->order ?? 'desc')
                ->where('list', '=', 'App\\Models\\'.$showList)
                ->select([
                    DB::raw('@total := @total + 1 AS `index`'),
                    DB::raw('CONCAT_WS("-", `hits`.`year`, `hits`.`month`, `hits`.`day`) AS `hit_date`'),
                    'hits.list',
                    'hits.list_id',
                    'hits.count',
                    DB::raw("'$showList' AS `show_list`"),
                    $model->getTable().'.iplong',
                    $model->getTable().'.mask',
                ])
                ->crossJoin(DB::raw("(SELECT @total := 0) AS `fakeTotal`"))
                ->leftJoin($model->getTable(), 'hits.list_id', '=', $model->getTable().'.id')

                ->where($searchField, 'like', '%'.$searchValue.'%')
                ->paginate($request->perPage);
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage().
                ", line=".$e->getLine().
                "\n"
            );

            return response('', 400);
        }

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        /*Log::debug(
            __METHOD__.
            " data: \n".
            print_r($data->toArray(), true).
            "\n"
        );*/

        return TopHitsResource::collection($data);
    }
    public function topLastCheck(Request $request, $showList = 'White')
    {
        $model = app('App\Models\\' . $showList);
        $model = new $model();

        $ipv6 = false;
        if (stripos($showList, '6')) {
            $ipv6 = true;
        }

        $searchField = 'id';
        $searchValue = '';
        foreach ($request->input('search') as $field => $value) {
            //
            if (!empty($value)) {
                switch ($field) {
                    case 'ip':
                        if (stripos($showList, '6')) {
                            $searchField = DB::raw('INET6_NTOA(`iplong`)');
                        } else {
                            $searchField = DB::raw('INET_NTOA(`iplong`)');
                        }
                        break;

                    case 'date_added':
                        $searchField = DB::raw('DATE_FORMAT(`date_added`, "%d %M %Y, %H:%i:%s")');
                        break;

                    case 'top_last_check':
                        $searchField = DB::raw('DATE_FORMAT(`last_check`, "%Y-%m %M")');
                        break;

                    case 'last_check':
                        $searchField = DB::raw('DATE_FORMAT(`last_check`, "%d %M %Y, %H:%i:%s")');
                        break;

                    default:
                        $searchField = $field;
                        break;
                }
                $searchValue = $value;
            }
        }

        // DB::enableQueryLog();

        try {
            $data = $model
                ->orderBy($request->column ?? 'total_ip', $request->order ?? 'desc')
                ->groupBy(DB::raw('DATE_FORMAT(`last_check`, "%Y-%m %M")'))
                ->select([
                    DB::raw('@total := @total + 1 AS `index`'),
                    DB::raw('SUM(POW(2,'.($ipv6 ? 128 : 32).'-`mask`)) AS `total_ip`'),
                    DB::raw('COUNT(*) AS `row_count`'),
                    DB::raw('DATE_FORMAT(`last_check`, "%Y-%m %M") AS `top_last_check`'),
                    DB::raw('MIN(`date_added`) AS `date_added`'),
                    DB::raw('MIN(`last_check`) AS `last_check`'),
                    DB::raw('MIN(`iplong`) AS `iplong`')
                ])
                ->crossJoin(DB::raw("(SELECT @total := 0) AS `fakeTotal`"))

                ->where($searchField, 'like', '%'.$searchValue.'%')
                ->paginate($request->perPage);
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage().
                ", line=".$e->getLine().
                "\n"
            );

            return response('', 400);
        }

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return LogsResource::collection($data);
    }

    public function topHits(Request $request, $showList = 'White')
    {
        $model = app('App\Models\\' . $showList);
        $model = new $model();

        $ipv6 = false;
        if (stripos($showList, '6')) {
            $ipv6 = true;
        }

        $searchField = 'id';
        $groupBy = ['id'];
        $groupBy[] = 'iplong';
        $groupBy[] = 'mask';
        $groupBy[] = 'netname';
        $groupBy[] = 'country';

        $searchValue = '';
        foreach ($request->input('search') as $field => $value) {
            //
            if (!empty($value)) {
                switch ($field) {
                    case 'ip':
                        if (stripos($showList, '6')) {
                            $searchField = DB::raw('INET6_NTOA(`iplong`)');
                        } else {
                            $searchField = DB::raw('INET_NTOA(`iplong`)');
                        }
                        // $groupBy = ['id','iplong'];
                        break;

                    case 'date_added':
                        $searchField = DB::raw('DATE_FORMAT(`date_added`, "%d %M %Y, %H:%i:%s")');
                        break;

                    case 'format_cidr':
                        if ($ipv6) {
                            $searchField = DB::raw('CONCAT(INET6_NTOA(`iplong`),"/",`mask`)');
                        } else {
                            $searchField = DB::raw('CONCAT(INET_NTOA(`iplong`),"/",`mask`)');
                        }
                        break;

                    case 'last_check':
                        $searchField = DB::raw('DATE_FORMAT(`last_check`, "%d %M %Y, %H:%i:%s")');
                        break;

                    default:
                        $searchField = $field;
                        break;
                }
                $searchValue = $value;
            }
        }

        // DB::enableQueryLog();

        try {
            $data = $model
                ->orderBy($request->column ?? 'total_ip', $request->order ?? 'desc')
                ->groupBy($groupBy)
                ->select([
                    DB::raw('@total := @total + 1 AS `index`'),
                    DB::raw('SUM(POW(2,'.($ipv6 ? 128 : 32).'-`mask`)) AS `total_ip`'),
                    DB::raw('COUNT(*) AS `row_count`'),
                    DB::raw('CONCAT('.($ipv6 ? 'INET6_NTOA': 'INET_NTOA').'(`iplong`),"/",`mask`) AS `format_cidr`'),
                    'iplong',
                    'mask',
                    'netname',
                    'country'
                ])
                ->crossJoin(DB::raw("(SELECT @total := 0) AS `fakeTotal`"))

                ->where($searchField, 'like', '%'.$searchValue.'%')
                ->withSum('hits', 'count')
                ->paginate($request->perPage);

            //DB::raw('MIN(`iplong`) AS `iplong`')
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage().
                ", line=".$e->getLine().
                "\n"
            );

            return response('', 400);
        }

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return LogsResource::collection($data);
    }
}
