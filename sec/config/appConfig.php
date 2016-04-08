<?php

/**
 * App Configuration File
 * Location : /opt/data/<Project Code>/<Environment>/config/appConfig.php
 */
global $appConfig;

$appConfig['currentVersion'] = '1.0';
$appConfig['platforms'] = array('android');

//session settings
$appConfig['sessions']['app'] = array(
    'ttl' => 0, //(in seconds) 0 = forever
);

//users token expiry time
$appConfig['user']['normalExpiration'] = '+2 hours';
$appConfig['user']['rememberMeExpiration'] = '+7 days';
$appConfig['user']['resetPasswordLength'] = 6;


/**
 * Grid config
 */
$appConfig['grid']['perPageDefaultValue'] = 10;
$appConfig['grid']['pageDefaultValue'] = 1;
$appConfig['grid']['perPageMaxValue'] = 100;
$appConfig['grid']['orderByDefaultValue'] = 'DESC';
$appConfig['grid']['perPageValues'] = array(-1, 10, 20, 50, 100);
$appConfig['grid']['orderByValues'] = array('DESC', 'ASC');
$appConfig['grid']['defaultInputs'] = array(
    'per_page' => $appConfig['grid']['perPageDefaultValue'],
    'page' => $appConfig['grid']['pageDefaultValue'],
    'sort_by' => '',
    'order_by' => $appConfig['grid']['orderByDefaultValue'],
    'search_by' => '',
    'search_value' => ''
);
$appConfig['mongo_errors']['list'] = array(
    'could not contact primary for replica set',
    'write results unavailable',
    'Your socket connection to the server was not read from or written to within the timeout period',
    'not master',

);
$appConfig['mongo_errors']['wait_for'] = 2; //in seconds
$appConfig['mongo_errors']['retry_for'] = 2; //retry (number of times)

global $appConn;

$appConn['mongo'] = null;
$appConn['logged_in_user_id'] = null;