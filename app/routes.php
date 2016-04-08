<?php

//Valid Route
\Illuminate\Support\Facades\Route::any('/{module}/{version}/{controller}/{method}', 'RouteController@route');
\Illuminate\Support\Facades\Route::any('/{module}/{version}/{controller}', 'RouteController@route');
\Illuminate\Support\Facades\Route::any('/{module}/{version}', 'RouteController@route');
\Illuminate\Support\Facades\Route::any('/{module}', 'RouteController@route');
\Illuminate\Support\Facades\Route::any('/', 'RouteController@route');