<?php

global $routeConfig;

// APP APIs
$routeConfig['api']['app']['1.0'] = array(
    // Auth controller
    'auth/login' => array('controller' => 'AuthController', 'function' => 'login', 'filters' => array(), 'method' => 'POST'),
    'call/save' => array('controller' => 'CallController', 'function' => 'save', 'filters' => array(), 'method' => 'POST'),
    'user/getMyCalls' => array('controller' => 'UserController', 'function' => 'getMyCalls', 'filters' => array(), 'method' => 'POST'),
    'user/getMyDoctors' => array('controller' => 'UserController', 'function' => 'getMyDoctors', 'filters' => array(), 'method' => 'GET'),
    );

// ADMIN APIs
$routeConfig['api']['admin']['1.0'] = array(
    // Auth controller
    'auth/login' => array('controller' => 'AuthController', 'function' => 'login', 'filters' => array(), 'method' => 'POST'),
    'auth/logout' => array('controller' => 'AuthController', 'function' => 'logout', 'filters' => array(), 'method' => 'POST'),
    'auth/forgotPassword' => array('controller' => 'AuthController', 'function' => 'forgotPassword', 'filters' => array(), 'method' => 'POST'),
);