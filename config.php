<?php
/**
 * Global App Configuration File
 * Location : /opt/data/config.php
 */

$systemEnvironment = "local";

if (empty($systemEnvironment)) {
    $systemEnvironment = "prod";
}

global $globalConfig;


$globalConfig = array(
    'apiEnv' => $systemEnvironment,
    'projectKey' => 'sec',
    'dataPath' => __DIR__
);

/**
 * Common Configuration
 */
$globalConfig['protocol'] = 'http://';
$globalConfig['timezone'] = 'Asia/Calcutta';

date_default_timezone_set($globalConfig['timezone']);
$globalConfig['api_security']['status'] = false;
$globalConfig['api_security']['public_key'] = '';

$globalConfig['errorLogging'] = true;
$globalConfig['isDebugMode'] = true;
$globalConfig['apiAnalytics']['status'] = false;
$globalConfig['apiAnalytics']['recordResponse'] = false;
$globalConfig['apiAnalyticsModules'] = array('app');

$globalConfig['bootstrap']['allowed_env'] = array('local');
$globalConfig['deprecatedApiVersions']['api'] = array();
$globalConfig['apiDockerName'] = $globalConfig['projectKey'] . '-api-' . $globalConfig['apiEnv'];

$globalConfig['domain'] = 'securiapharma.com';

if ('prod' == $globalConfig['apiEnv']) {
    $globalConfig['allowedOriginPatterns'][] = '/^(?:.+\.)?securiapharma\.com/';
    $globalConfig['apiUrl'] = $globalConfig['protocol'] . 'api.' . $globalConfig['domain'] . '/';
    $globalConfig['configUrl'] = $globalConfig['protocol'] . 'config.' . $globalConfig['domain'] . '/config.json';
    $globalConfig['appUrl'] = $globalConfig['protocol'] . 'app.' . $globalConfig['domain'] . '/';
    $globalConfig['farmUrl'] = 'api-farm.' . $globalConfig['domain'] . '.';
    $globalConfig['dnsUrl'] = 'api.' . $globalConfig['domain'] . '.';
} else {
    $globalConfig['allowedOriginPatterns'][] = '/^(?:.+\.)?securiapharma\.com/';
    $globalConfig['apiUrl'] = $globalConfig['protocol'] . $globalConfig['projectKey'] . '-api-' . $globalConfig['apiEnv'] . '.' . $globalConfig['domain'] . '/';
    $globalConfig['configUrl'] = $globalConfig['protocol'] . $globalConfig['projectKey'] . '-config-' . $globalConfig['apiEnv'] . '.' . $globalConfig['domain'] . '/config.json';
    $globalConfig['appUrl'] = $globalConfig['protocol'] . $globalConfig['projectKey'] . '-app-' . $globalConfig['apiEnv'] . '.' . $globalConfig['domain'] . '/';
    $globalConfig['farmUrl'] = $globalConfig['projectKey'] . '-api-' . $globalConfig['apiEnv'] . '-farm' . '.' . $globalConfig['domain'] . '.';
    $globalConfig['dnsUrl'] = $globalConfig['projectKey'] . '-api-' . $globalConfig['apiEnv'] . '.' . $globalConfig['domain'] . '.';
}

$globalConfig['allowedOriginPatterns'][] = '/chrome-extension:\/\/(.*)?/';
$globalConfig['sharedPath'] = $globalConfig['dataPath'] . '/' . $globalConfig['projectKey'] . '/';
$globalConfig['storagePath'] = $globalConfig['sharedPath'] . 'storage/';

/**
 * MongoDB configuration
 */
$globalConfig['mongo'] = array(
    'username' => '',
    'password' => '',
    'database' => $globalConfig['projectKey'] . '-' . $globalConfig['apiEnv'],
    'host' => array('mongo.securiapharma.com:27001')
);

/**
 * Amazon Configuration Template
 */
$queueName = $globalConfig['projectKey'] . '-' . $globalConfig['apiEnv'];

$appConfig['emails']['defaultFromEmail'] = 'avinashkatore89@gmail.com';

/**
 * Error Logging Settings
 */
$globalConfig['error']['email_to'] = array('techops@securiapharma.com');
$globalConfig['error']['codes'] = array(0, 100, 101, 102, 103, 104, 105);
$globalConfig['error']['subject'] = 'Error Occur on ' . $globalConfig['projectKey'] . ' ' . $globalConfig['apiEnv'] . ' Server';
$globalConfig['techops']['email_to'] = array('techops@securiapharma.com');
$globalConfig['error']['same_email_frequency'] = 3600;      // Same error email will not come for 1 hour
$globalConfig['error']['timestamp_file'] = '/tmp/' . $globalConfig['projectKey'] . '_' . $globalConfig['apiEnv'] . '_error_timestamp.txt';

$globalConfig['filesTempLocation'] = '/tmp/';

switch ($globalConfig['apiEnv']) {
    case 'prod':
        $globalConfig['api_security']['status'] = false;
        $globalConfig['apiAnalytics']['status'] = false;

        /**
         * Mongo Random Host selection
         */
        $mongoHosts = array(
            'localhost'
        );
        $host = array_rand($mongoHosts);

        $globalConfig['mongo']['host'] = $mongoHosts[$host] . ':27017';

        /**
         * AWS Configuration
         */
        $globalConfig['AWS'] = array(
            'key' => '',
            'secret' => '',
            'account_id' => '',
            'dns_zone_id' => '',
            'region' => 'us-east-1',
            'ses' => array(
                'key' => '',
                'secret' => '',
            )
        );

        $globalConfig['route_53'] = $globalConfig['AWS'];
        $globalConfig['ec2'] = $globalConfig['AWS'];

        $globalConfig['s3']['accessKey'] = $globalConfig['AWS']['key'];
        $globalConfig['s3']['accessToken'] = $globalConfig['AWS']['secret'];
        $globalConfig['s3']['region'] = $globalConfig['AWS']['region'];

        /**
         * SQS configuration
         */
        $globalConfig['sqs'] = array(
            'key' => $globalConfig['AWS']['key'],
            'secret' => $globalConfig['AWS']['secret'],
            'queue' => 'https://sqs.' . $globalConfig['AWS']['region'] . '.amazonaws.com/' . $globalConfig['AWS']['account_id'] . '/psc-prod',
            'region' => $globalConfig['AWS']['region'],
        );

        /**
         * SMTP configuration
         */
        $globalConfig['smtp'] = array(
            'SMTP_DRIVER' => 'smtp',
            'SMTP_HOST' => 'email-smtp.us-east-1.amazonaws.com',
            'SMTP_PORT' => 465,
            'SMTP_FROM_EMAIL' => 'do-not-reply@securiapharma.com',
            'SMTP_FROM_USERNAME' => 'DCC Admin',
            'SMTP_ENCRYPTION' => 'ssl',
            'SMTP_USERNAME' => $globalConfig['AWS']['ses']['key'],
            'SMTP_PASSWORD' => $globalConfig['AWS']['ses']['secret'],
            'SMTP_PRETEND' => false //TRUE: emails written to your application's logs files and mail will not be send
        );

        break;
    case 'local':
        $globalConfig['storagePath'] = '';
        $globalConfig['errorLogging'] = true;
        $globalConfig['isDebugMode'] = true;
        $globalConfig['apiAnalytics']['status'] = true;

        $globalConfig['AWS'] = array(
            'key' => '',
            'secret' => '',
            'account_id' => '',
            'region' => 'us-east-1',
            'dns_zone_id' => '',
            'ses' => array(
                'key' => '',
                'secret' => '',
            )
        );

        $globalConfig['s3']['accessKey'] = $globalConfig['AWS']['key'];
        $globalConfig['s3']['accessToken'] = $globalConfig['AWS']['secret'];
        $globalConfig['s3']['region'] = $globalConfig['AWS']['region'];

        /**
         * SQS configuration
         */
        $queueName = 'sec-dev';
        $globalConfig['sqs'] = array(
            'key' => $globalConfig['AWS']['key'],
            'secret' => $globalConfig['AWS']['secret'],
            'queue' => 'https://sqs.' . $globalConfig['AWS']['region'] . '.amazonaws.com/' . $globalConfig['AWS']['account_id'] . '/' . $queueName,
            'region' => $globalConfig['AWS']['region'],
        );

        /**
         * SMTP configuration
         */
        $globalConfig['smtp'] = array(
            'SMTP_DRIVER' => 'smtp',
            'SMTP_HOST' => 'email-smtp.us-east-1.amazonaws.com',
            'SMTP_PORT' => 465,
            'SMTP_FROM_EMAIL' => 'do-not-reply@securiapharma.com',
            'SMTP_FROM_USERNAME' => 'Securia Admin',
            'SMTP_ENCRYPTION' => 'ssl',
            'SMTP_USERNAME' => $globalConfig['AWS']['ses']['key'],
            'SMTP_PASSWORD' => $globalConfig['AWS']['ses']['secret'],
            'SMTP_PRETEND' => false //TRUE: emails written to your application's logs files and mail will not be send
        );

        $globalConfig['mongo']['host'] = $globalConfig['projectKey'] . '-mongo-' . $globalConfig['apiEnv'] . '.' . $globalConfig['domain'] . ':27017';
        $queueName = 'Local';
        $globalConfig['techops']['email_to'] = array('avinashkatore89@gmail.com');
        break;
}

$globalConfig['s3']['bucketName'] = 'data-prod-sec';
$globalConfig['s3']['cloudFrontName'] = 'data.securiapharma.com';

if (true == in_array($globalConfig['apiEnv'], array('dev', 'qa', 'pp'))) {
    $globalConfig['smtp']['SMTP_FROM_EMAIL'] = 'admin@securiapharma.com';
    $globalConfig['smtp']['SMTP_FROM_USERNAME'] = 'Securia Pharma';
    $appConfig['emails']['defaultFromEmail'] = 'admin@securiapharma.com';

    $globalConfig['s3']['cloudFrontName'] = $globalConfig['s3']['bucketName'] = $globalConfig['projectKey'] . '-data-' . $globalConfig['apiEnv'] . '.' . $globalConfig['domain'];
}

/**
 * S3 Buckets Configuration
 */

if ($globalConfig['apiEnv'] == 'local') {
    $globalConfig['diag']['bucket'] = $globalConfig['s3']['cloudFrontName'] = $globalConfig['s3']['bucketName'] = $globalConfig['projectKey'] . '-data-dev.' . $globalConfig['domain'];
}

try {
    require_once __DIR__ . '/' . $globalConfig['projectKey'] . '/config/routeConfig.php';
    require_once __DIR__ . '/' . $globalConfig['projectKey'] . '/config/appConfig.php';
} catch (Exception $e) {
    die(json_encode(array('success' => FALSE, 'message' => array('errorId' => 501, 'description' => $e->getMessage()))));
}
