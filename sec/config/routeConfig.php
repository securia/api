<?php

global $routeConfig;

// APP APIs
$routeConfig['api']['app']['1.0'] = array(
    // Auth controller
    'auth/login' => array('controller' => 'AuthController', 'function' => 'login', 'filters' => array(), 'method' => 'POST'),
    'call/save' => array('controller' => 'CallController', 'function' => 'save', 'filters' => array(), 'method' => 'POST'),
    'user/getMyCalls' => array('controller' => 'UserController', 'function' => 'getMyCalls', 'filters' => array(), 'method' => 'POST'),
    'user/getMyDoctors' => array('controller' => 'UserController', 'function' => 'getMyDoctors', 'filters' => array(), 'method' => 'GET'),
    'user/changePassword' => array('controller' => 'UserController', 'function' => 'changePassword', 'filters' => array(), 'method' => 'POST'),
    );

// ADMIN APIs
$routeConfig['api']['admin']['1.0'] = array(
    // Auth controller
    'auth/login' => array('controller' => 'AuthController', 'function' => 'login', 'filters' => array(), 'method' => 'POST'),
    'call/get' => array('controller' => 'CallController', 'function' => 'get', 'filters' => array(), 'method' => 'POST'),
    'user/getAll' => array('controller' => 'UserController', 'function' => 'getAll', 'filters' => array(), 'method' => 'POST'),
    'user/get' => array('controller' => 'UserController', 'function' => 'get', 'filters' => array(), 'method' => 'POST'),
    'user/save' => array('controller' => 'UserController', 'function' => 'save', 'filters' => array(), 'method' => 'POST'),
    'user/delete' => array('controller' => 'UserController', 'function' => 'delete', 'filters' => array(), 'method' => 'POST'),
);