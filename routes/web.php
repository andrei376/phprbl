<?php

use App\Http\Controllers\StatsController;
use App\Http\Controllers\TopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\RblController;


use App\Models\DefineList;
use App\Models\RblLog;
use App\Models\Setup;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
*/

Route::get('/token', function (Request $request) {
    if ($request->expectsJson()) {
        return new JsonResponse(null, 200);
    }

    return new Response('', 200);
})->name('token');

Route::middleware(['auth', 'verified'])->group(function () {
    //
    Route::get('/', function () {
        return redirect()->route('stats.index');
    })->name('index');

    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
    Route::get('/stats/rbl', [StatsController::class, 'stats'])->name('stats.rbl');
    Route::get('/stats/syslog/{iplong}/{mask}', [StatsController::class, 'syslog'])->name('syslog.rbl');
    Route::get('/stats/elastic/{iplong}/{mask}', [StatsController::class, 'elastic'])->name('elastic.rbl');

    Route::get('/browse/{list?}', [RblController::class, 'browse'])->name('rbl.browse');
    Route::post('/browse/{list?}', [RblController::class, 'browse'])->name('rbl.getBrowse');

    Route::post('/topip1/{list}', [TopController::class, 'topIp1'])->name('top.ip1');
    Route::post('/topcountry/{list}', [TopController::class, 'topCountry'])->name('top.country');
    Route::post('/topnetname/{list}', [TopController::class, 'topNetname'])->name('top.netname');
    Route::post('/topinetnum/{list}', [TopController::class, 'topInetnum'])->name('top.inetnum');
    Route::post('/toporgname/{list}', [TopController::class, 'topOrgname'])->name('top.orgname');
    Route::post('/topdateadded/{list}', [TopController::class, 'topDateAdded'])->name('top.dateadded');
    Route::post('/toplastcheck/{list}', [TopController::class, 'topLastCheck'])->name('top.lastcheck');
    Route::post('/toplasthit/{list}', [TopController::class, 'topLastHit'])->name('top.lasthit');
    Route::post('/tophits/{list}', [TopController::class, 'topHits'])->name('top.hits');

    Route::get('/logs', function() {
        return Inertia::render('Rbl/Logs', [
            'sortColumns' => [
                ['name' => 'id', 'class' => 'w-1/12'],
                ['name' => __('User'), 'class' => 'w-1/12'],
                ['name' => __('Date'), 'class' => 'w-2/12'],
                ['name' => __('Type'), 'class' => 'w-2/12']
            ]
        ]);
    })->name('rbl.logs');
    Route::get('/get-logs', [RblController::class, 'getLogs'])->name('rbl.jslogs');
    Route::model('rbllog', RblLog::class);
    Route::post('/read-log/{rbllog}', [RblController::class, 'readLog'])->name('log.read');
    Route::delete('/delete-log/{rbllog}', [RblController::class, 'deleteLog'])->name('log.delete');


    Route::get('/get-hits', [RblController::class, 'getHits'])->name('rbl.hits');

    Route::get('/ip-log', [RblController::class, 'ipLog'])->name('ip.log');

    Route::get('/whois', [RblController::class, 'whois'])->name('rbl.whois');
    Route::post('/getWhois', [RblController::class, 'getWhois'])->name('rbl.getWhois');

    Route::get('/lookup', [RblController::class, 'lookup'])->name('rbl.lookup');
    Route::post('/getLookup', [RblController::class, 'getLookup'])->name('rbl.getLookup');

    Route::get('/top', [TopController::class, 'index'])->name('top.index');

    Route::get('/new4', [RblController::class, 'new4'])->name('rbl.new4');
    Route::get('/new6', [RblController::class, 'new6'])->name('rbl.new6');

    Route::post('/store4', [RblController::class, 'store4'])->name('v4.save');
    Route::post('/store6', [RblController::class, 'store6'])->name('v6.save');

    Route::get('/check4/{list}', [RblController::class, 'check4'])->name('rbl.check4');
    Route::get('/check6/{list}', [RblController::class, 'check6'])->name('rbl.check6');

    Route::get('/netname4/{list}', [RblController::class, 'netname4'])->name('rbl.netname4');
    Route::get('/netname6/{list}', [RblController::class, 'netname6'])->name('rbl.netname6');

    Route::get('/delete4/{list}', [RblController::class, 'delete4'])->name('rbl.delete4');
    Route::get('/delete6/{list}', [RblController::class, 'delete6'])->name('rbl.delete6');

    Route::get('/inactive4/{list}', [RblController::class, 'inactive4'])->name('rbl.inactive4');
    Route::get('/inactive6/{list}', [RblController::class, 'inactive6'])->name('rbl.inactive6');

    Route::get('/show4/{id}/{list}', [RblController::class, 'show4'])->name('rbl.show4');
    Route::get('/show6/{id}/{list}', [RblController::class, 'show6'])->name('rbl.show6');

    Route::post('/show4/{id}/{list}', [RblController::class, 'show4'])->name('update.show4');
    Route::post('/show6/{id}/{list}', [RblController::class, 'show6'])->name('update.show6');

    Route::get('/many4/{id}/{list}/{cidr?}', [RblController::class, 'many4'])->name('rbl.many4');
    Route::post('/many4/{id}/{list}/{cidr?}', [RblController::class, 'many4'])->name('update.many4');

    Route::delete('/rbl/destroy/{id}/{list}', [RblController::class, 'destroy'])->name('rbl.destroy');

    Route::get('/toggle4/{id}/{list}/{field}/{msg?}', [RblController::class, 'toggle4'])->name('rbl.toggle4');
    Route::get('/toggle6/{id}/{list}/{field}/{msg?}', [RblController::class, 'toggle6'])->name('rbl.toggle6');

    Route::post('/toggle4/{id}/{list}/{field}/{msg?}', [RblController::class, 'toggle4'])->name('update.toggle4');
    Route::post('/toggle6/{id}/{list}/{field}/{msg?}', [RblController::class, 'toggle6'])->name('update.toggle6');

    Route::model('setup', DefineList::class);
    Route::resource('setup', SetupController::class);

    Route::model('variable', Setup::class);
    Route::get('/setup/create/var', [SetupController::class, 'createVar'])->name('setup.createvar');
    Route::post('/setup/store/var', [SetupController::class, 'storeVar'])->name('setup.storevar');
    Route::get('/setup/{variable}/editvar', [SetupController::class, 'editVar'])->name('setup.editvar');
    Route::put('/setup/update/{variable}', [SetupController::class, 'updateVar'])->name('setup.updatevar');
    Route::delete('/setup/destroy/{variable}', [SetupController::class, 'destroyVar'])->name('setup.destroyvar');

    Route::resource('users', UserController::class);
    //
});

require __DIR__.'/auth.php';
