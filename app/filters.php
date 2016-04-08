<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

\Illuminate\Support\Facades\App::before(function ($request) {
    //
});


\Illuminate\Support\Facades\App::after(function ($request, $response) {
    try {
        global $globalConfig;

        /**
         * If apiAnalytics module present then update api logs
         */
        if (isset($globalConfig['apiAnalytics']['status']) && $globalConfig['apiAnalytics']['status'] == true) {
            global $appConn;

            // Set Mongo Client
            if (false == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
                $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
            }
            $appConn['end_time'] = microtime(true);

            $update['duration'] = $appConn['end_time'] - LARAVEL_START;
            $update['updated_at'] = $appConn['end_time'];
            if (isset($appConn['platform'])) {
                $update['platform'] = $appConn['platform'];
            }

            if (isset($response->original['success'])) {
                $update['success'] = $response->original['success'];
                $update['id'] = $response->original['message']['id'];
                $update['description'] = $response->original['message']['description'];
                if (isset($response->original['data']['token'])) {
                    $update['token'] = $response->original['data']['token'];
                }

                if($globalConfig['apiAnalytics']['recordResponse']) {
                    $update['response'] = $response->original;
                }

                if ($update['success'] == false) {
                    $update['trace'] = json_encode($response->original['data']);
                }
            }

            if (isset($appConn['analytics_id'])) {
                $appConn['mongo']->collection('api_analytics')->where('_id', $appConn['analytics_id'])->update($update);
            }
        }
    } catch (\Exception $e) {
        trace($e);
    }
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

\Illuminate\Support\Facades\Route::filter('auth', function () {
    if (\Illuminate\Support\Facades\Auth::guest()) {
        if (\Illuminate\Support\Facades\Request::ajax()) {
            return \Illuminate\Support\Facades\Response::make('Unauthorized', 401);
        } else {
            return \Illuminate\Support\Facades\Redirect::guest('login');
        }
    }
});


\Illuminate\Support\Facades\Route::filter('auth.basic', function () {
    return \Illuminate\Support\Facades\Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

\Illuminate\Support\Facades\Route::filter('guest', function () {
    if (\Illuminate\Support\Facades\Auth::check()) return \Illuminate\Support\Facades\Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

\Illuminate\Support\Facades\Route::filter('csrf', function () {
    if (\Illuminate\Support\Facades\Session::token() !== \Illuminate\Support\Facades\Input::get('_token')) {
        throw new Illuminate\Session\TokenMismatchException;
    }
});
