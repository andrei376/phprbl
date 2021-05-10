<?php

namespace App\Http\Controllers;

use App\Models\DefineList;

use App\Models\Setup;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

use Inertia\Inertia;
use Inertia\Response;

class SetupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     * @noinspection PhpUnused
     */
    public function index(): Response
    {
        //
        $data = DefineList::all();

        $variables = Setup::all();

        return Inertia::render('Setup/Index', [
            'lists' => $data,
            'variables' => $variables
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     * @noinspection PhpUnused
     */
    public function create(): Response
    {
        //
        return Inertia::render('Setup/Edit', [
            'isEdit' => false
        ]);
    }

    public function createVar(): Response
    {
        return Inertia::render('Setup/EditVar', [
            'isEdit' => false
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     * @noinspection PhpUnused
     */
    public function store(Request $request): RedirectResponse
    {
        //
        DefineList::create(
            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'expire' => 'required',
                'host' => 'required',
                'list' => 'required',
                'minttl' => 'required',
                'nss' => 'required',
                'primaryns' => 'required',
                'refresh' => 'required',
                'retry' => 'required',
                'soansttl' => 'required'
            ])
        );

        //session()->flash('message', __('List successfully created.'));
        return Redirect::route('setup.index')->with('msg.success', __('List successfully created.'));
    }

    public function storeVar(Request $request): RedirectResponse
    {
        //
        Setup::create(
            $request->validate([
                'name' => 'required',
                'value' => 'required'
            ])
        );

        return Redirect::route('setup.index')->with('msg.success', __('Variable successfully created.'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DefineList  $defineList
     * @return \Inertia\Response
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     * @noinspection PhpUnused
     */
    public function edit(DefineList $defineList): Response
    {
        //
        //dump($defineList);

        return Inertia::render('Setup/Edit', [
            'list' => $defineList,
            'isEdit' => true
        ]);
    }

    public function editVar(Setup $setup): Response
    {
        //
        // dump($setup);

        return Inertia::render('Setup/EditVar', [
            'setupvar' => $setup,
            'isEdit' => true
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DefineList  $defineList
     * @return \Illuminate\Http\RedirectResponse
     * @noinspection PhpUnused
     */
    public function update(Request $request, DefineList $defineList): RedirectResponse
    {
        //
        $defineList->update(
            $request->validate([
                'name' => 'required',
                'email' => 'required',
                'expire' => 'required',
                'host' => 'required',
                'list' => 'required',
                'minttl' => 'required',
                'nss' => 'required',
                'primaryns' => 'required',
                'refresh' => 'required',
                'retry' => 'required',
                'soansttl' => 'required'
            ])
        );

        return Redirect::route('setup.index')->with('msg.success', 'List successfully updated.');
    }

    public function updateVar(Request $request, Setup $setup): RedirectResponse
    {
        //
        $setup->update(
            $request->validate([
                'name' => 'required',
                'value' => 'required'
            ])
        );

        return Redirect::route('setup.index')->with('msg.success', 'Variable successfully updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DefineList  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @noinspection PhpFullyQualifiedNameUsageInspection
     * @noinspection PhpUnused
     */
    public function destroy(DefineList $id)
    {
        //
        try {
            if ($id instanceof DefineList) {
                $id = $id->id;
            }

            //dump($id);
            //$defineList=1234;

            DefineList::findOrFail($id)->delete();

            //session()->flash('msg.warning', __('User successfully deleted.'));

            return Redirect::back()->with('msg.warning', __('List successfully deleted.'));
            //
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " failed to delete list id=".$id."\n"
            );

            return response()->json(['errors' => ''], 422, ['X-Inertia' => true]);
        }
    }

    public function destroyVar(Setup $setup)
    {
        //
        try {
            $id = $setup;
            if ($setup instanceof Setup) {
                $id = $setup->id;
            }

            //dump($id);
            // $id=1234;

            Setup::findOrFail($id)->delete();

            return Redirect::back()->with('msg.warning', __('Variable successfully deleted.'));
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " failed to delete setup id=".$id."\n"
            );

            return response()->json(['errors' => ''], 422, ['X-Inertia' => true]);
        }
    }
}
