<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'errors' => function () {

                return Session::get('errors')

                ? Session::get('errors')->getBag('default')->getMessages()

                : (object) [];
            },
            'flash' => [
                'msg_success' => $request->session()->get('msg.success'),
                'msg_error' => $request->session()->get('msg.error'),
                'msg_warning' => $request->session()->get('msg.warning'),
                'msg_info' => $request->session()->get('msg.info'),
            ],
            'flashData' => $request->session()->get('flashData'),
            'locale' => function () {
                return app()->getLocale();
            },
            'language' => function () {
                return translations(
                    resource_path('lang/'. app()->getLocale() .'.json')
                );
            },
        ]);
    }
}
