<?php /** @noinspection DuplicatedCode */

namespace App\Http\Controllers;

use App\Helpers\Rbl6;
use App\Http\Resources\HitsResource;
use App\Http\Resources\LogsResource;
use App\Models\Hit;
use App\Models\RblLog;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\StoreV4Request;
use App\Http\Requests\StoreV6Request;

use App\Models\DefineList;

use App\Helpers\Rbl4;
use App\Helpers\WhoisLib;

class RblController extends Controller
{
    //
    public function new4(): Response
    {
        //
        //get lists

        return Inertia::render('Rbl/New4', [
            'lists' => Rbl4::getLists()
        ]);
    }

    public function new6(): Response
    {
        return Inertia::render('Rbl/New6', [
            'lists' => Rbl6::getLists()
        ]);
    }

    /**
     * @throws Exception
     */
    public function deleteLog(RblLog $id)
    {
        //DB::enableQueryLog();

        $id->delete();

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return response('');
    }

    public function readLog(RblLog $id)
    {
        //dump($id);
        //DB::enableQueryLog();
        $id->read = true;
        $id->save();

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return response('');
    }

    public function getLogs(Request $request): AnonymousResourceCollection
    {
        //DB::enableQueryLog();

        $data = RblLog::orderBy($request->column ?? 'id', $request->order ?? 'desc')
            ->paginate($request->perPage);

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return LogsResource::collection($data);
    }

    public function store4(StoreV4Request $request)
    {
        //dump(__METHOD__.' '.__LINE__);
        //dump($request);

        $validated = $request->validated();

        //dump('validated=');
        //dump($validated);


        $msgIp = '';

        foreach ($validated['okip'] as $data) {
            list($g1, $g2, $g3, $g4) = explode(".", $data['ip']);

            $rec['ip1'] = $g1;
            $rec['ip2'] = $g2;
            $rec['ip3'] = $g3;
            $rec['ip4'] = $g4;
            $rec['iplong'] = ip2long($data['ip']);
            $rec['mask'] = $data['cidr'];
            $rec['active'] = '1';
            $rec['delete'] = '0';
            $rec['id'] = $data['old_id'] ?? null;
            $rec['geoipcountry'] = @geoip_country_code_by_name($data['ip']);


            //pr($ok);
            //dump($rec);

            $model = app('App\Models\\' . $validated['list']);

            try {
                $save = $model->updateOrCreate(
                    ['id' => $rec['id'] ?? null],
                    $rec
                );

                DefineList::increaseSn('%');
            } catch (Exception $e) {
                //throw new ValidationException($this->getValidationFactory(), 'asd');
                return back()->withException($e);
                //return response()->withException($e);
            }

            //dump($save);

            if ($save) {
                //session()->flash('msg.success', __('Information saved.'));

                $msgIp .= $data['init'].' => ' . $data['res'].'<br>';
            }
        }

        $resMsg = __(
            ":total lines => :count ips in :list",
            [
                'total' => count($validated['resip']),
                'count' => count($validated['okip']),
                'list' => $validated['list']
            ]
        ).'<br><br>';

        $resMsg .= $msgIp;

        //return Redirect::back();
        return response()->json(
            [
                'component' => 'Rbl/New4',
                'props' => [
                    'errors' => '',
                    'lists' => Rbl4::getLists(),
                    'auth' => ['user' => $request->user()],
                    'locale' => function () {
                    return app()->getLocale();
                    },
                    'language' => function () {
                    return translations(
                        resource_path('lang/'. app()->getLocale() .'.json')
                        );
                    },
                    'flash' => ['msg_success' => __('Information saved.')],
                    'resMsg' => $resMsg
                ]
            ],
            200,
            ['X-Inertia' => true]
        );
    }

    public function store6(StoreV6Request $request)
    {
        // dump(__METHOD__.' '.__LINE__);
        // dump($request);

        // DB::enableQueryLog();

        $validated = $request->validated();

        // dump('validated=');
        // dump($validated);


        $msgIp = '';

        foreach ($validated['okip'] as $data) {
            list($g1, $g2, $g3, $g4, $g5, $g6, $g7, $g8) = explode(":", $data['ip']);

            $rec['ip1'] = $g1;
            $rec['ip2'] = $g2;
            $rec['ip3'] = $g3;
            $rec['ip4'] = $g4;
            $rec['ip5'] = $g5;
            $rec['ip6'] = $g6;
            $rec['ip7'] = $g7;
            $rec['ip8'] = $g8;

            $rec['iplong'] = inet_pton($data['ip']);
            $rec['mask'] = $data['cidr'];
            $rec['active'] = '1';
            $rec['delete'] = '0';
            $rec['id'] = $data['old_id'] ?? null;
            $rec['geoipcountry'] = @geoip_country_code_by_name($data['ip']);

            // dump($rec);

            $model = app('App\Models\\' . $validated['list']);

            try {
                $save = $model->updateOrCreate(
                    ['id' => $rec['id'] ?? null],
                    $rec
                );

                DefineList::increaseSn('%6');
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error saving data='.print_r($rec, true).
                    ', msg='.$e->getMessage().
                    "\n".
                    $e->getTraceAsString().
                    "\n\n"
                );
                return back()->withException($e);
            }

            // dump($save);

            if ($save) {
                $msgIp .= $data['init'].' => ' . $data['res'].'<br><br>';
            }
        }

        $resMsg = __(
                ":total lines => :count ips in :list",
                [
                    'total' => count($validated['resip']),
                    'count' => count($validated['okip']),
                    'list' => $validated['list']
                ]
            ).'<br><br>';

        $resMsg .= $msgIp;

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return response()->json(
            [
                'component' => 'Rbl/New6',
                'props' => [
                    'errors' => '',
                    'lists' => Rbl6::getLists(),
                    'auth' => ['user' => $request->user()],
                    'locale' => function () {
                        return app()->getLocale();
                    },
                    'language' => function () {
                        return translations(
                            resource_path('lang/'. app()->getLocale() .'.json')
                        );
                    },
                    'flash' => ['msg_success' => __('Information saved.')],
                    'resMsg' => $resMsg
                ]
            ],
            200,
            ['X-Inertia' => true]
        );
    }

    public function whois(): Response
    {
        return Inertia::render('Rbl/Whois');
    }

    public function lookup(): Response
    {
        return Inertia::render('Rbl/Lookup');
    }

    public function inactive4(string $list): RedirectResponse
    {
        $lists4 = Rbl4::getLists();

        if (!in_array($list, $lists4)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $model = app('App\Models\\' . $list);

        //find first 'netname' = '' and redirect to show
        $ip = $model::
        where('active', false)
            ->orderBy('last_check', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if (empty($ip)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        $id = $ip->id;

        //redirect to show
        return redirect()->route('rbl.show4', [
            'id' => $id,
            'list' => $list
        ]);
    }

    public function inactive6(string $list): RedirectResponse
    {
        $lists6 = Rbl6::getLists();

        if (!in_array($list, $lists6)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $model = app('App\Models\\' . $list);

        //find first 'netname' = '' and redirect to show
        $ip = $model::
        where('active', false)
            ->orderBy('last_check', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if (empty($ip)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        $id = $ip->id;

        //redirect to show
        return redirect()->route('rbl.show6', [
            'id' => $id,
            'list' => $list
        ]);
    }

    public function delete4(string $list): RedirectResponse
    {
        $lists4 = Rbl4::getLists();

        if (!in_array($list, $lists4)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $model = app('App\Models\\' . $list);

        //find first 'netname' = '' and redirect to show
        $ip = $model::
            where('delete', true)
            ->orderBy('last_check', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if (empty($ip)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        $id = $ip->id;

        //redirect to show
        return redirect()->route('rbl.show4', [
            'id' => $id,
            'list' => $list
        ]);
    }

    public function destroy(int $id, string $list): JsonResponse
    {
        // dump($id);
        // dump($list);

        try {
            $model = app('App\Models\\' . $list);

            //find db row
            $ipInfo = $model::findOrFail($id);

            // DB::enableQueryLog();

            $ipInfo->update([
                'delete' => 1,
                'active' => 0,
                'checked' => 1
            ]);

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/

            return response()->json($ipInfo);
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " failed to mark delete list=$list id=$id\n"
            );

            return response()->json(['errors' => ''], 422, ['X-Inertia' => true]);
        }
    }

    public function delete6(string $list): RedirectResponse
    {
        $lists6 = Rbl6::getLists();

        if (!in_array($list, $lists6)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $model = app('App\Models\\' . $list);

        //find first 'netname' = '' and redirect to show
        $ip = $model::
        where('delete', true)
            ->orderBy('last_check', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if (empty($ip)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        $id = $ip->id;

        //redirect to show
        return redirect()->route('rbl.show6', [
            'id' => $id,
            'list' => $list
        ]);
    }

    public function netname4(string $list): RedirectResponse
    {
        $lists4 = Rbl4::getLists();

        if (!in_array($list, $lists4)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $model = app('App\Models\\' . $list);

        //find first 'netname' = '' and redirect to show
        $ip = $model::
            where('netname', null)
            ->orderBy('last_check', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if (empty($ip)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        $id = $ip->id;

        //redirect to show
        return redirect()->route('rbl.show4', [
            'id' => $id,
            'list' => $list
        ]);
    }

    public function netname6(string $list): RedirectResponse
    {
        $lists6 = Rbl6::getLists();

        if (!in_array($list, $lists6)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $model = app('App\Models\\' . $list);

        //find first 'netname' = '' and redirect to show
        $ip = $model::
        where('netname', null)
            ->orderBy('last_check', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        if (empty($ip)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        $id = $ip->id;

        //redirect to show
        return redirect()->route('rbl.show6', [
            'id' => $id,
            'list' => $list
        ]);
    }

    /**
     * @param string $list
     * @return RedirectResponse
     */
    public function check4(string $list): RedirectResponse
    {
        //dump($list);
        //valid list
        $lists4 = Rbl4::getLists();

        if (!in_array($list, $lists4)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $model = app('App\Models\\' . $list);

        //find first checked=0 and redirect to show
        $ip = $model::
            where('checked', 0)
            ->orderBy('mask', 'asc')
            ->orderBy('last_check', 'asc')
            ->orderBy('ip1', 'asc')
            ->orderBy('ip2', 'asc')
            ->orderBy('ip3', 'asc')
            ->first();

        //dump($ip);

        if (empty($ip)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        $id = $ip->id;

        //redirect to show
        return redirect()->route('rbl.show4', [
            'id' => $id,
            'list' => $list
        ]);
    }

    public function check6(string $list): RedirectResponse
    {
        // dump($list);
        //valid list
        $lists6 = Rbl6::getLists();

        if (!in_array($list, $lists6)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $model = app('App\Models\\' . $list);

        //find first checked=0 and redirect to show
        $ip = $model::
        where('checked', 0)
            ->orderBy('mask', 'asc')
            ->orderBy('last_check', 'asc')
            ->orderBy('ip1', 'asc')
            ->orderBy('ip2', 'asc')
            ->orderBy('ip3', 'asc')
            ->first();

        //dump($ip);

        if (empty($ip)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        $id = $ip->id;

        //redirect to show
        return redirect()->route('rbl.show6', [
            'id' => $id,
            'list' => $list
        ]);
    }

    public function toggle4(Request $request, int $id, string $list, string $field, string $reason = null)
    {
        //valid list
        $c4 = new Rbl4();

        $lists4 = $c4::getLists();

        if (!in_array($list, $lists4)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $model = app('App\Models\\' . $list);

        //find db row
        $ipInfo = $model::find($id);

        //dump($ipInfo);

        if (empty($ipInfo)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        //check column
        if (!Schema::hasColumn($model->getTable(), $field)) {
            return Redirect::back()->with('msg.error', __('Invalid field.'));
        }

        $ipAddr = long2ip($ipInfo->iplong);

        $cidrInfo = $ipAddr.'/'.$ipInfo->mask;

        if ($request->isMethod('post')) {
            //dump($ipInfo);
            //dump($ipInfo->$field);
            //dump($request->input());

            try {
                //DB::enableQueryLog();

                $url = route('rbl.show4', [
                    'id' => $id,
                    'list' => $list
                ]);

                $message = '<a href="'.$url.'">'.$cidrInfo.'</a> in '.$list;
                $message .='<br>';

                $message .= __('[toggle :field from :from to :to]', [
                    'field' => $field,
                    'from' => $ipInfo->$field ? 'true' : 'false',
                    'to' => !$ipInfo->$field ? 'true' : 'false'
                ]);

                $message .='<br><br>';

                $message .= __('[Reason]').': ';
                $message .= '<br>';
                $message .= nl2br($request->input('reason'));


                if (!empty($request->input('reason'))) {
                    RblLog::saveLog($request->user()->name, __('['.$field.']'), $message);
                }

                /*Log::debug(
                    __METHOD__.
                    " query: \n".
                    print_r(DB::getQueryLog(), true).
                    "\n"
                );*/
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error saving: '.$e->getMessage().
                    ", line=".$e->getLine().
                    "\n"
                );

                return response()->json(['error' => __('Error saving to log.')], 400);
            }

            $ipInfo->$field = !$ipInfo->$field;

            try {
                //DB::enableQueryLog();
                $ipInfo->save();

                if ($field == 'active') {
                    DefineList::increaseSn('%');
                }

                /*Log::debug(
                    __METHOD__.
                    " query: \n".
                    print_r(DB::getQueryLog(), true).
                    "\n"
                );*/
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error updating ip in db: '.$e->getMessage().
                    ", line=".$e->getLine().
                    ", field=$field, ".
                    "\n data=".
                    print_r($ipInfo, true).
                    "\n"
                );

                return response()->json(['error' => __('Error updating field :field in list :list.', ['list' => $list, 'field' => $field])], 400);
            }

            return Redirect::route('rbl.show4', [
                'id' => $id,
                'list' => $list
            ])->with('msg.success', __('Information saved.'));
        }

        return Inertia::render('Rbl/Toggle4', [
            'id' => $id,
            'field' => $field,
            'cidrInfo' => $cidrInfo,
            'list' => $list,
            'reason' => $reason
        ]);
    }


    public function many4(Request $request, int $id, string $list, int $cidr = null)
    {
        //dump($id);
        //dump($list);
        //dump($cidr);

        //valid list
        $c4 = new Rbl4();
        $c6 = new Rbl6();

        $ipv6 = false;

        $lists4 = $c4::getLists();
        $lists6 = $c6::getLists();

        if (!in_array($list, $lists4) && !in_array($list, $lists6)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $model = app('App\Models\\' . $list);

        //find db row
        $ipInfo = $model::find($id);

        //dump($ipInfo);

        if (empty($ipInfo)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        // DB::enableQueryLog();
        if (stripos($ipInfo->long2ip, ':') !== false) {
            $ipv6 = true;
            $otherIp = $c6->findDoubles($ipInfo, $list, $cidr);
        } else {
            $otherIp = $c4->findDoubles($ipInfo, $list, $cidr);
        }
        // dump(DB::getQueryLog());

        //dump($otherIp);

        $formData = $otherIp->pluck(null, 'id')->toArray();

        if ($request->isMethod('post')) {
            //dump($request->all());

            // DB::enableQueryLog();
            foreach ($request->input('fdata') as $data) {
                //dump($data);

                try {
                    unset($row);

                    $row = $model::findorFail($data['id']);


                    $row->active = $data['active'];
                    $row->delete = $data['delete'];
                    $row->checked = $data['checked'];


                    $row->save();
                } catch (Exception $e) {
                    Log::error(
                        __METHOD__.
                        ' error saving: '.$e->getMessage().
                        ", line=".$e->getLine().
                        "\n"
                    );

                    return Redirect::back()->withErrors('error');
                }
            }
            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/

            return Redirect::back();
        }


        return Inertia::render('Rbl/Many', compact(
            'id',
            'list',
            'cidr',
            'ipInfo',
            'otherIp',
            'formData',
            'ipv6'
        ));
    }
    public function show4(Request $request, int $id, string $list)
    {
        //valid list
        $c4 = new Rbl4();
        $whoisLib = new WhoisLib();

        $lists4 = $c4::getLists();

        if (!in_array($list, $lists4)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $moveLists = $lists4;
        unset($moveLists[$list]);

        $model = app('App\Models\\' . $list);

        //find db row
        $ipInfo = $model::find($id);

        //dump($ipInfo);

        if (empty($ipInfo)) {
            return Redirect::route('stats.index')->with('msg.error', __('Nothing to show.'));
        }

        $hostnameInfo = $c4->hostnameRange($ipInfo->iplong, $ipInfo->mask);

        $ipAddr = long2ip($ipInfo->iplong);

        $cidrInfo = $ipAddr.'/'.$ipInfo->mask;


        if ($request->isMethod('post') && $request->forceWhois == 1) {
            $whoisData = $whoisLib->searchCache($cidrInfo, true);
        } else {
            $whoisData = $whoisLib->searchCache($cidrInfo, false);
        }
        $whoisData['date'] = date('d F Y, H:i:s',strtotime($whoisData['date']));

        if ($request->isMethod('post') && $request->updateWhois == 1) {
            //DB::enableQueryLog();

            $ipInfo->update([
                'inetnum' => $whoisData['inetnum'],
                'netname' => $whoisData['netname'],
                'country' => $whoisData['country'],
                'orgname' => $whoisData['orgname'],
            ]);

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/

            return response()->json($ipInfo);
        }

        if ($request->isMethod('post') && $request->updateLastCheck == 1) {
            //DB::enableQueryLog();

            $ipInfo->update([
                'last_check' => now(),
            ]);

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/

            return response()->json($ipInfo);
        }

        if ($request->isMethod('post') && $request->updateIp == 1) {
            $newIp = $request->newIp;

            $dataIp = $c4->ip2cidr($newIp);

            if ($dataIp['ip'] == -1) {
                return response()->json(['error' => __('[Invalid ip.]')], 400);
            }

            //dump($dataIp);

            if ($list != 'Grey') {
                RblLog::saveLog(
                    $request->user()->name,
                    '[replace ip]',
                    __('[in :list replacing :oldip with :newip]', [
                        'list' => $list,
                        'oldip' => $cidrInfo,
                        'newip' => '<a href="'.URL::route('rbl.show4', ['id' => $id, 'list' => $list]).'">'.$dataIp['ip'].'/'.$dataIp['cidr'].'</a>'
                    ])
                );
            }

            list($g1,$g2,$g3,$g4) = explode(".", $dataIp['ip']);

            //DB::enableQueryLog();
            $ipInfo->update([
                'iplong' => ip2long($dataIp['ip']),
                'mask' => $dataIp['cidr'],
                'ip1' => $g1,
                'ip2' => $g2,
                'ip3' => $g3,
                'ip4' => $g4,
            ]);

            $hostnameInfo = $c4->hostnameRange($ipInfo->iplong, $ipInfo->mask);

            $ipAddr = long2ip($ipInfo->iplong);

            $cidrInfo = $ipAddr.'/'.$ipInfo->mask;

            $rangeInfo = $c4->getRange($cidrInfo, 'range');

            DefineList::increaseSn('%');

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/

            return response()->json(compact(
                'ipInfo',
                'hostnameInfo',
                'cidrInfo',
                'rangeInfo'
            ));
        }

        if ($request->isMethod('post') && $request->moveIp == 1) {
            $newList = $request->moveList;

            if (!in_array($newList, $moveLists)) {
                return response()->json(['error' => __('[Invalid list.]')], 400);
            }

            //dump($newList);

            $saveIp = $ipInfo->toArray();

            $saveIp['id'] = '';
            $saveIp['delete'] = '0';
            $saveIp['checked'] = '0';
            $saveIp['last_check'] = null;

            $newModel = app('App\Models\\' . $newList);

            try {
                //DB::enableQueryLog();

                $newModel->create($saveIp);

                /*Log::debug(
                    __METHOD__.
                    " query: \n".
                    print_r(DB::getQueryLog(), true).
                    "\n"
                );*/
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error saving moved ip in db: '.$e->getMessage().
                    ", line=".$e->getLine().
                    "\n data=".
                    print_r($saveIp, true).
                    "\n"
                );

                return response()->json(['error' => __('[Error saving in new list :list.]', ['list' => $newList])], 400);
            }

            try {
                //DB::enableQueryLog();
                $ipInfo->delete = '0';
                $ipInfo->active = '0';

                $ipInfo->save();

                DefineList::increaseSn('%');

                /*Log::debug(
                    __METHOD__.
                    " query: \n".
                    print_r(DB::getQueryLog(), true).
                    "\n"
                );*/
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error updating old ip in db: '.$e->getMessage().
                    ", line=".$e->getLine().
                    "\n data=".
                    print_r($ipInfo, true).
                    "\n"
                );

                return response()->json(['error' => __('[Error updating ip in old list :list.]', ['list' => $list])], 400);
            }

            //redirect to toggle delete for old ip
            $redirectUrl = route('rbl.toggle4', ['id' => $id, 'list' => $list, 'field' => 'delete', 'msg' => __('moved from :list to :newlist', ['list' => $list, 'newlist' => $newList])]);

            return response()->json($redirectUrl);
        }

        if ($request->isMethod('post') && $request->forceWhois == 1) {
            return response()->json($whoisData);
        }



        //dump($whoisData);


        $rangeInfo = $c4->getRange($cidrInfo, 'range');

        $geoCountry = geoip_country_name_by_name($ipAddr);

        //DB::enableQueryLog();
        $multiple = $c4->isMultiple($ipAddr, $ipInfo->mask, $id, $list);
        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/
        //dump($multiple);

        $other24 = $c4->searchOthers($list, $ipInfo);

        $other16 = $c4->searchOthers($list, $ipInfo, 16);

        /*dump($ipInfo->date_added);
        dump($ipInfo->date_added_format);
        dump($ipInfo->date_added_ago);
        dump($ipInfo->date_added->diffForHumans());
        dump($ipInfo);*/

        return Inertia::render('Rbl/Show', [
            'ipInfo' => $ipInfo,
            'hostnameInfo' => $hostnameInfo,
            'list' => $list,
            'moveLists' => $moveLists,
            'cidrInfo' => $cidrInfo,
            'geoCountry' => $geoCountry,
            'whoisData' => $whoisData,
            'rangeInfo' => $rangeInfo,
            'multiple' => $multiple,
            'other24' => $other24,
            'other16' => $other16
        ]);
    }

    public function getLookup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ip' => [
                'required',
                function ($attribute, $value, $fail) {
                    $c4 = new Rbl4();
                    $c6 = new Rbl6();

                    $result = $c4->isIp($value);
                    $result6 = $c6->isIp($value);

                    if (!$result && !$result6) {
                        $fail(__('Invalid ip.'));
                        return false;
                    }

                    return true;
                }
            ]
        ]);

        $searchIp = $validated['ip'];

        $c4 = new Rbl4();
        $c6 = new Rbl6();

        if (stripos($searchIp, ':') !== false) {
            $multiple = $c6->isMultiple($searchIp, 128, 0, '');

            $result['dns'] = $c6->checkDns($searchIp);
        } else {
            $multiple = $c4->isMultiple($searchIp, 32, 0, '');

            $result['dns'] = $c4->checkDns($searchIp);
        }

        $result['multiple'] = $multiple;


        return Redirect::back()->with('flashData.result', $result);
    }

    public function getWhois(Request $request): RedirectResponse
    {
        //

        $validated = $request->validate([
            'ip' => [
                'required',
                function ($attribute, $value, $fail) {
                    $c4 = new Rbl4();
                    $c6 = new Rbl6();

                    $result = $c4->isIp($value);
                    $result6 = $c6->isIp($value);

                    if (!$result && !$result6) {
                        $fail(__('Invalid ip.'));
                        return false;
                    }

                    return true;
                }
            ]
        ]);

        //dump($validated);

        $searchIp = $validated['ip'];

        $whois = new WhoisLib();

        $result = $whois->searchCache($searchIp, true);

        return Redirect::back()->with('flashData.result', $result);
    }

    public function ipLog(Request $request): AnonymousResourceCollection
    {
        // DB::enableQueryLog();

        if (stripos($request->searchIp, ':') !== false) {
            $searchIp = $request->searchIp;
            $g = explode(':', $searchIp);

            // dump($g);

            $search = '';

            foreach ($g as $item) {
                if (trim($item) != '') {
                    $search .= $item.':';
                }
            }
            $searchIp = $search;
        } else {
            $searchIp = long2ip($request->searchIp);
        }


        $data = RblLog::
            where('type', '!=', __('[readlog]'))
            ->where('message', 'like', '%'.$searchIp.'%')
            ->orderBy('date', 'desc')
            ->paginate($request->perPage);

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return LogsResource::collection($data);
    }

    public function getHits(Request $request): AnonymousResourceCollection
    {
        // DB::enableQueryLog();

        $hits = Hit::orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->select(['year', 'month', DB::raw('SUM(`count`) AS `counts`')])
            ->groupBy('year', 'month')
            ->where('list', 'App\\Models\\'.$request->list)
            ->where('list_id', $request->id)
            ->paginate($request->perPage);

        // dump(DB::getQueryLog());

        return HitsResource::collection($hits);
    }

    public function browse(Request $request, $showList = 'White')
    {
        $lists = Rbl4::getLists();
        $lists6 = Rbl6::getLists();

        $ipv6 = false;
        if (stripos($showList, '6')) {
            $ipv6 = true;
        }

        if ($request->isMethod('post')) {
            $model = app('App\Models\\' . $showList);
            $model = new $model();

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

            //dump($searchField);
            // dump($searchValue);

            // dump($request->all());

            // DB::enableQueryLog();

            try {
                $data = $model
                    ->orderBy($request->column, $request->order)
                    ->orderBy('date_added', 'desc')
                    ->groupBy(['id', 'ip1','ip2'])
                    ->where($searchField, 'like', '%'.$searchValue.'%')
                    ->withSum('hits', 'count')
                    ->paginate($request->perPage);
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error: '.$e->getMessage().
                    ", file=".$e->getFile().
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

        return Inertia::render('Rbl/Browse', compact(
            'showList',
            'lists',
            'lists6',
            'ipv6'
        ));
    }

    public function show6(Request $request, int $id, string $list)
    {
        //valid list
        $c6 = new Rbl6();
        $whoisLib = new WhoisLib();

        $lists6 = $c6::getLists();

        if (!in_array($list, $lists6)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $moveLists = $lists6;
        unset($moveLists[$list]);

        $model = app('App\Models\\' . $list);

        //find db row
        $ipInfo = $model::find($id);

        // dump($ipInfo);



        if (empty($ipInfo)) {
            return Redirect::route('stats.index')->with('msg.error', __('Nothing to show.'));
        }

        $hostnameInfo = $c6->hostnameRange($ipInfo->getRawOriginal('iplong'), $ipInfo->mask);

        $ipAddr = $ipInfo->long2ip;

        $cidrInfo = $ipAddr.'/'.$ipInfo->mask;

        // dump($cidrInfo);


        if ($request->isMethod('post') && $request->forceWhois == 1) {
            $whoisData = $whoisLib->searchCache($cidrInfo, true);
        } else {
            $whoisData = $whoisLib->searchCache($cidrInfo, false);
        }
        $whoisData['date'] = date('d F Y, H:i:s',strtotime($whoisData['date']));

        if ($request->isMethod('post') && $request->updateWhois == 1) {
            // DB::enableQueryLog();

            $ipInfo->update([
                'inetnum' => $whoisData['inetnum'],
                'netname' => $whoisData['netname'],
                'country' => $whoisData['country'],
                'orgname' => $whoisData['orgname'],
            ]);

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/

            return response()->json($ipInfo);
        }

        if ($request->isMethod('post') && $request->updateLastCheck == 1) {
            // DB::enableQueryLog();

            $ipInfo->update([
                'last_check' => now(),
            ]);

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/

            return response()->json($ipInfo);
        }

        if ($request->isMethod('post') && $request->updateIp == 1) {
            $newIp = $request->newIp;

            $dataIp = $c6->ip2cidr($newIp);

            if ($dataIp['ip'] == -1) {
                return response()->json(['error' => __('[Invalid ip.]')], 400);
            }

            // dump($dataIp);

            if ($list != 'Grey6') {
                RblLog::saveLog(
                    $request->user()->name,
                    '[replace ip]',
                    __('[in :list replacing :oldip with :newip]', [
                        'list' => $list,
                        'oldip' => $cidrInfo,
                        'newip' => '<a href="'.URL::route('rbl.show6', ['id' => $id, 'list' => $list]).'">'.$dataIp['ip'].'/'.$dataIp['cidr'].'</a>'
                    ])
                );
            }

            list($g1,$g2,$g3,$g4,$g5,$g6,$g7,$g8) = explode(":", $dataIp['ip']);

            // DB::enableQueryLog();
            $ipInfo->update([
                'iplong' => inet_pton($dataIp['ip']),
                'mask' => $dataIp['cidr'],
                'ip1' => $g1,
                'ip2' => $g2,
                'ip3' => $g3,
                'ip4' => $g4,
                'ip5' => $g5,
                'ip6' => $g6,
                'ip7' => $g7,
                'ip8' => $g8,
            ]);

            $hostnameInfo = $c6->hostnameRange($ipInfo->getRawOriginal('iplong'), $ipInfo->mask);

            $ipAddr = $ipInfo->long2ip;

            $cidrInfo = $ipAddr.'/'.$ipInfo->mask;

            $rangeInfo = $c6->getRange($cidrInfo, 'range');

            DefineList::increaseSn('%6');

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/

            return response()->json(compact(
                'ipInfo',
                'hostnameInfo',
                'cidrInfo',
                'rangeInfo'
            ));
        }

        if ($request->isMethod('post') && $request->moveIp == 1) {
            $newList = $request->moveList;

            if (!in_array($newList, $moveLists)) {
                return response()->json(['error' => __('[Invalid list.]')], 400);
            }

            // dump($newList);

            $saveIp = $ipInfo->toArray();

            $saveIp['id'] = '';
            $saveIp['delete'] = '0';
            $saveIp['checked'] = '0';
            $saveIp['last_check'] = null;
            $saveIp['iplong'] = $ipInfo->getRawOriginal('iplong');

            $newModel = app('App\Models\\' . $newList);

            try {
                // DB::enableQueryLog();

                $newModel->create($saveIp);

                /*Log::debug(
                    __METHOD__.
                    " query: \n".
                    print_r(DB::getQueryLog(), true).
                    "\n"
                );*/
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error saving moved ip in db: '.$e->getMessage().
                    ", line=".$e->getLine().
                    "\n data=".
                    print_r($saveIp, true).
                    "\n"
                );

                return response()->json(['error' => __('[Error saving in new list :list.]', ['list' => $newList])], 400);
            }

            try {
                // DB::enableQueryLog();
                $ipInfo->delete = '0';
                $ipInfo->active = '0';

                $ipInfo->save();

                DefineList::increaseSn('%6');

                /*Log::debug(
                    __METHOD__.
                    " query: \n".
                    print_r(DB::getQueryLog(), true).
                    "\n"
                );*/
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error updating old ip in db: '.$e->getMessage().
                    ", line=".$e->getLine().
                    "\n data=".
                    print_r($ipInfo, true).
                    "\n"
                );

                return response()->json(['error' => __('[Error updating ip in old list :list.]', ['list' => $list])], 400);
            }

            //redirect to toggle delete for old ip
            $redirectUrl = route('rbl.toggle6', ['id' => $id, 'list' => $list, 'field' => 'delete', 'msg' => __('moved from :list to :newlist', ['list' => $list, 'newlist' => $newList])]);

            return response()->json($redirectUrl);
        }

        if ($request->isMethod('post') && $request->forceWhois == 1) {
            return response()->json($whoisData);
        }



        //dump($whoisData);


        $rangeInfo = $c6->getRange($cidrInfo, 'range');

        $geoCountry = geoip_country_name_by_name($ipAddr);

        // DB::enableQueryLog();
        $multiple = $c6->isMultiple($ipAddr, $ipInfo->mask, $id, $list);
        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/
        //dump($multiple);

        //dynamic search, mask-8 and mask-16
        // $other24 = $c4->searchOthers($list, $ipInfo);
        $searchMask1 = $ipInfo->mask;
        if ($ipInfo->mask > 16) {
            $searchMask1 = $ipInfo->mask - 16;
        }
        $other24 = $c6->searchOthers($list, $ipInfo, $searchMask1);

        // $other16 = $c4->searchOthers($list, $ipInfo, 16);
        //$searchMask2 = $ipInfo->mask;
        if ($ipInfo->mask > 32) {
            $searchMask2 = $ipInfo->mask - 32;
        } else {
            $searchMask2 = $searchMask1;
        }
        $other16 = $c6->searchOthers($list, $ipInfo, $searchMask2);


        /*dump($ipInfo->date_added);
        dump($ipInfo->date_added_format);
        dump($ipInfo->date_added_ago);
        dump($ipInfo->date_added->diffForHumans());
        dump($ipInfo);*/

        //$ipInfo->iplong = bin2hex($ipInfo->iplong);

        // dump($hostnameInfo);
        // dump($cidrInfo);
        // dump($rangeInfo);


        return Inertia::render('Rbl/Show6', [
            'ipInfo' => $ipInfo,
            'hostnameInfo' => $hostnameInfo,
            'list' => $list,
            'moveLists' => $moveLists,
            'cidrInfo' => $cidrInfo,
            'geoCountry' => $geoCountry,
            'whoisData' => $whoisData,
            'rangeInfo' => $rangeInfo,
            'multiple' => $multiple,
            'other24' => $other24,
            'other16' => $other16,
            'searchMask1' => $searchMask1,
            'searchMask2' => $searchMask2
        ]);
    }

    public function toggle6(Request $request, int $id, string $list, string $field, string $reason = null)
    {
        //valid list
        $c6 = new Rbl6();

        $lists6 = $c6::getLists();

        if (!in_array($list, $lists6)) {
            return Redirect::back()->with('msg.error', __('Invalid list.'));
        }

        $model = app('App\Models\\' . $list);

        //find db row
        $ipInfo = $model::find($id);

        //dump($ipInfo);

        if (empty($ipInfo)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        //check column
        if (!Schema::hasColumn($model->getTable(), $field)) {
            return Redirect::back()->with('msg.error', __('Invalid field.'));
        }

        $ipAddr = $ipInfo->long2ip;

        $cidrInfo = $ipAddr.'/'.$ipInfo->mask;

        if ($request->isMethod('post')) {
            //dump($ipInfo);
            //dump($ipInfo->$field);
            //dump($request->input());

            try {
                // DB::enableQueryLog();

                $url = route('rbl.show6', [
                    'id' => $id,
                    'list' => $list
                ]);

                $message = '<a href="'.$url.'">'.$cidrInfo.'</a> in '.$list;
                $message .='<br>';

                $message .= __('[toggle :field from :from to :to]', [
                    'field' => $field,
                    'from' => $ipInfo->$field ? 'true' : 'false',
                    'to' => !$ipInfo->$field ? 'true' : 'false'
                ]);

                $message .='<br><br>';

                $message .= __('[Reason]').': ';
                $message .= '<br>';
                $message .= nl2br($request->input('reason'));


                if (!empty($request->input('reason'))) {
                    RblLog::saveLog($request->user()->name, __('['.$field.']'), $message);
                }

                /*Log::debug(
                    __METHOD__.
                    " query: \n".
                    print_r(DB::getQueryLog(), true).
                    "\n"
                );*/
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error saving: '.$e->getMessage().
                    ", line=".$e->getLine().
                    "\n"
                );

                return response()->json(['error' => __('Error saving to log.')], 400);
            }

            $ipInfo->$field = !$ipInfo->$field;

            try {
                // DB::enableQueryLog();
                $ipInfo->save();

                if ($field == 'active') {
                    DefineList::increaseSn('%6');
                }

                /*Log::debug(
                    __METHOD__.
                    " query: \n".
                    print_r(DB::getQueryLog(), true).
                    "\n"
                );*/
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error updating ip in db: '.$e->getMessage().
                    ", line=".$e->getLine().
                    ", field=$field, ".
                    "\n data=".
                    print_r($ipInfo, true).
                    "\n"
                );

                return response()->json(['error' => __('Error updating field :field in list :list.', ['list' => $list, 'field' => $field])], 400);
            }

            return Redirect::route('rbl.show6', [
                'id' => $id,
                'list' => $list
            ])->with('msg.success', __('Information saved.'));
        }

        return Inertia::render('Rbl/Toggle6', [
            'id' => $id,
            'field' => $field,
            'cidrInfo' => $cidrInfo,
            'list' => $list,
            'reason' => $reason
        ]);
    }
}
