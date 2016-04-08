<?php

/**
 * Utility function to debug variable
 *
 * @param $data
 * @param bool $exit
 */
function trace($data, $exit = true)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    if ($exit) {
        exit;
    }
}

/**
 * Check valid integer or not
 *
 * @param $num
 * @return bool
 */
function valInt($num)
{
    return (true == isset($num) && true == is_int($num)) ? true : false;
}

/**
 * Check valid string or not
 *
 * @param $str
 * @param int $intLen
 * @return bool
 */
function valStr($str, $intLen = 1)
{
    $str = (false == is_array($str)) ? trim((string)$str) : NULL;
    return (true == isset($str[0]) && $intLen <= strlen($str)) ? true : false;
}

/**
 * Check valid array or not
 *
 * @param $arr
 * @param int $intCount
 * @return bool
 */
function valArr($arr, $intCount = 1)
{
    return (true == isset($arr) && true == is_array($arr) && $intCount <= count($arr)) ? true : false;
}

/**
 *  Check valid object or not of Specified Class
 *
 * @param $obj
 * @param $strClass
 * @return bool
 */
function valObj($obj, $strClass)
{
    return (true == isset($obj) && true == is_object($obj) && true == ($obj instanceof $strClass)) ? true : false;
}

/**
 * Check valid email or not
 *
 * @param $email
 * @return bool
 */
function valEmail($email)
{
    return (true == isset($email) && true == filter_var($email, FILTER_VALIDATE_EMAIL)) ? true : false;
}

/**
 * Check image extension for validation
 *
 * @param $image
 * @return bool
 */
function validateImage($image)
{
    $extension = substr(strrchr($image, '.'), 1);

    $allowedExtensions = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
    if (in_array(strtolower($extension), $allowedExtensions)) {
        return true;
    }
    return false;
}

/**
 * Change array key
 *
 * @param $arrUnKeyedData
 * @param string $strKeyFieldName
 * @return array
 */
function changeArrayKey($arrUnKeyedData, $strKeyFieldName = 'id')
{
    if (false == valArr($arrUnKeyedData))
        return $arrUnKeyedData;
    $arrReKeyedData = array();
    foreach ($arrUnKeyedData as $mixUnKeyedData) {
        $arrReKeyedData[$mixUnKeyedData[$strKeyFieldName]] = $mixUnKeyedData;
    }
    return $arrReKeyedData;
}

/**
 * Get all values from specific key in a multidimensional array
 *
 * @param $key string
 * @param $arr array
 * @return null|string|array
 */
function arrayValueRecursive($key, array $arr)
{
    $val = array();
    array_walk_recursive($arr, function ($v, $k) use ($key, &$val) {
        if ($k == $key)
            array_push($val, $v);
    });
    return count($val) > 1 ? $val : array_pop($val);
}

/**
 * Trim all array elements
 *
 * @param array $arr
 * @return array
 */
function trimArray($arr = array())
{
    if (true == valArr($arr)) {
        foreach ($arr as $key => $element) {
            if (is_array($element)) {

                foreach ($element as $innerKey => $innerElement) {
                    $arr[$key][$innerKey] = trim($element[$innerKey]);
                }
            } else {
                $arr[$key] = trim($arr[$key]);
            }
        }
    }
    return $arr;
}

/**
 * Typecast array
 *
 * @param array $arr
 * @param string $type
 * @return array
 */
function typecastArray($arr = array(), $type = 'int')
{
    if (true == valArr($arr)) {
        foreach ($arr as $key => $value) {
            if ($type == 'string') {
                $arr[$key] = (string)$arr[$key];
            } else if ($type == 'array') {
                $arr[$key] = (array)$arr[$key];
            } else {
                $arr[$key] = (int)$arr[$key];
            }
        }
    }
    return $arr;
}

/**
 * Get list of timezones
 *
 * @return mixed
 */
function getTimezones()
{
    static $regions = array(
        DateTimeZone::ALL
    );
    $timezones = array();
    foreach ($regions as $region) {
        $timezones = array_merge($timezones, DateTimeZone::listIdentifiers($region));
    }

    $timezoneOffsets = array();
    foreach ($timezones as $timezone) {
        $tz = new DateTimeZone($timezone);
        $timezoneOffsets[$timezone] = $tz->getOffset(new DateTime);
    }

    // sort timezone by offset
    asort($timezoneOffsets);

    $timezoneList = array();
    foreach ($timezoneOffsets as $timezone => $offset) {
        $offsetPrefix = $offset < 0 ? '-' : '+';
        $offsetFormatted = gmdate('H:i', abs($offset));

        $offsetTotal = "${offsetPrefix}${offsetFormatted}";
        $prettyOffset = "UTC${offsetTotal}";

        $timezoneRow['timezone'] = $timezone;
        $timezoneRow['offset'] = $offsetTotal;
        $timezoneRow['name'] = "(${prettyOffset}) $timezone";
        $timezoneList[] = $timezoneRow;
    }
    return $timezoneList;
}

/**
 * Check string starts with character
 *
 * @param $haystack
 * @param $needle
 * @return bool
 */
function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}

/**
 * Merge recursively nested array keys
 * @param array $array1
 * @param array $array2
 * @return array
 */
function array_merge_recursive_ex(array $array1, array $array2)
{
    $merged = $array1;

    foreach ($array2 as $key => $value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = array_merge_recursive_ex($merged[$key], $value);
        } else
            $merged[$key] = $value;
    }

    return $merged;
}

/**
 * Function to validate User Input parameters
 * @param array $defaultInputs
 * @param array $rules
 * @param bool $onlyJson
 * @return mixed
 */
function validateInput($defaultInputs = array(), $rules = array(), $onlyJson = false)
{
    $inputs = array();
    if (!$onlyJson) {
        $inputs = \Illuminate\Support\Facades\Input::all();
    }
    if (empty($inputs)) {
        $inputs = \Illuminate\Support\Facades\Input::json()->all();
    }

    //merge, trim users Input and apply strip_tags if input is string
    $inputs = cleanInputData(array_merge_recursive_ex($defaultInputs, $inputs));
    //check Any rules are available
    if (empty($rules)) {
        return \ApplicationBase\Facades\Api::success(6010, $inputs, array('Validation'));
    }

    $inputs = unsetKeys($inputs, array('timestamp', 'signature'));

    //Validate Inputs
    $validator = \Illuminate\Support\Facades\Validator::make($inputs, $rules);
    if ($validator->fails()) {
        return \ApplicationBase\Facades\Api::error(1000, array(), array($validator->messages()->first()));
    }

    return \ApplicationBase\Facades\Api::success(6010, $inputs, array('Validation'));
}

/**
 * Function to validate and merge Array
 * @param null $defaultInputs
 * @param array $inputs
 * @param null $rules
 * @return array
 */
function validateInputArray($defaultInputs = null, $inputs = array(), $rules = null)
{
    $data = array();

    //validate complete array of input and merge with default values

    //merge Input
    $mergedInputs = array_merge_recursive_ex($defaultInputs, $inputs);

    //Validate Inputs
    $validator = \Illuminate\Support\Facades\Validator::make($mergedInputs, $rules);

    if ($validator->fails()) {
        return array('success' => false, 'data' => $validator->messages()->first());
    }

    return array('success' => true, 'data' => $mergedInputs);
}

/**
 * Function to clean input parameters
 * @param array $data
 * @return array|bool
 */
function cleanInputData($data = array())
{
    //check data is available
    if (empty($data)) {
        return $data;
    }
    //GO through all all data
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $data[$key] = cleanInputData($value);
        }
        //Clean Input values And also check the datatype of the values
        if (is_string($value)) {
            $data[$key] = addslashes(trim($value));
        }
    }

    return $data;
}

/**
 * Function to generate random string which can be used in forgotPassword functionality
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * For processing inputs to convert parameters required for mongo query
 *
 * @param $columns
 * @param $inputs
 * @param $Query
 * @return mixed
 */
function processMongoGridInputs($strQuery, $columns, $inputs)
{

    if (!empty($inputs['search_value'])) {

        //if empty search by then add all searchable columns
        if (empty($inputs['search_by'])) {
            foreach ($columns as $key => $val) {
                if ($val['search']) {
                    $inputs['search_by'][] = $key;
                }
            }
        } else {
            foreach ($inputs['search_by'] as $searchByIndex => $searchBy) {
                if (!array_key_exists($searchBy, $columns) || $columns[$searchBy]['search'] == false) {
                    unset($inputs['search_by'][$searchByIndex]);
                }
            }
        }
        foreach ($inputs['search_by'] as $intKey => $val) {
            $strQuery->orWhere(function ($strQuery) use ($inputs, $columns, $val, $intKey) {
                if ('integer' == $columns[$val]['data_type'] && true == is_numeric($inputs['search_value'])) {
                    $strQuery->Where($columns[$val]['db_name'], '=', (int)$inputs['search_value']);
                } elseif ('string' == $columns[$val]['data_type']) {
                    $strQuery->Where($columns[$val]['db_name'], 'regexp', "/.*" . $inputs['search_value'] . "/i");
                }

            });

        }

    } else {
        $inputs['search_by'] = array();
    }

    if (valArr($inputs['sort'])) {
        foreach ($inputs['sort'] as $sortIndex => $sort) {
            if ($columns[$sort['sort_by']]['sort'] != '') {
                $strQuery->orderBy($columns[$sort['sort_by']]['db_name'], $inputs['sort'][$sortIndex]['order_by']);
            }
        }
    }

    return $strQuery;
}

/**
 * For processing inputs to convert parameters required for cypher query
 *
 * @param $columns
 * @param $APIInputs
 * @param array $defaultAPIInputs
 * @param string $defaultWhere
 * @return mixed
 */
function processGridInputs($columns, $APIInputs, $defaultWhere = '', $defaultAPIInputs = array())
{
    global $appConfig;
    $grid = $appConfig['grid'];

    $gridInputs = $grid['defaultInputs'];
    if (true == valArr($defaultAPIInputs)) {
        $gridInputs = array_merge($gridInputs, $defaultAPIInputs);
    }

    $inputs = array_merge($gridInputs, $APIInputs);

    $searchByArray = array();
    $searchByQuery = '';

    if (!empty($inputs['search_value'])) {

        //if empty searchby then add all searchable columns
        if (empty($inputs['search_by'])) {
            foreach ($columns as $key => $val) {
                if ($val['search']) {
                    $inputs['search_by'][] = $key;
                }
            }
        } else {
            foreach ($inputs['search_by'] as $searchByIndex => $searchBy) {
                if (!array_key_exists($searchBy, $columns) || $columns[$searchBy]['search'] == false) {
                    unset($inputs['search_by'][$searchByIndex]);
                }
            }
        }

        foreach ($inputs['search_by'] as $val) {
            $searchByArray[] = $columns[$val]['db_name'] . ' =~  \'(?i).*' . $inputs['search_value'] . '.*\' ';
        }
    } else {
        $inputs['search_by'] = array();
    }

    if (!empty($searchByArray) || '' != trim($defaultWhere)) {
        $searchByQuery = ' WHERE ';
        $searchByQuery .= ('' != trim($defaultWhere)) ? trim($defaultWhere) . ' ' : '';
        $searchByQuery .= (!empty($searchByArray) && '' != trim($defaultWhere)) ? ' AND ' : '';
        $searchByQuery .= (!empty($searchByArray)) ? '(' . implode(' OR ', $searchByArray) . ')' : '';
    }

    if (!isset($inputs['sort'])) {
        $inputs['sort'] = array();
    }

    $newOrderedSort = array();
    foreach ($inputs['sort'] as $sortIndex => $sort) {
        if ($columns[$sort['sort_by']]['sort'] != '') {
            $newOrderedSort[$sort['sort_by']] = $inputs['sort'][$sortIndex];
            $newOrderedSort[$sort['sort_by']]['data_type'] = $columns[$sort['sort_by']]['data_type'];
        }
    }

    /*foreach ($columns as $sortIndex => $sort) {
        if ($sort['sort'] != '' && !isset($newOrderedSort[$sortIndex])) {
            $newOrderedSort[$sortIndex] = array('sort_by' => $sortIndex, 'order_by' => $columns[$sortIndex]['sort'], 'data_type' => $columns[$sortIndex]['data_type']);
        }
    }*/

    $sortByQuery = '';
    if (true == valArr($newOrderedSort)) {
        $sortByQuery = ' ORDER BY ';
        foreach ($newOrderedSort as $sort) {
            if ($sort['data_type'] == 'string') {
                $sort['sort_by'] = ' lower(' . $sort['sort_by'] . ') ';
            }
            $sortByQuery .= $sort['sort_by'] . ' ' . $sort['order_by'] . ', ';
        }
        $sortByQuery = rtrim(trim($sortByQuery), ',');
    }

    $returnString = ' RETURN ';

    $i = 0;
    foreach ($columns as $displayCol => $dbCol) {
        $i++;
        $returnString .= $dbCol['db_name'] . ' AS `' . $displayCol . ($i == count($columns) ? '` ' : '`, ');
    }

    if (!in_array($inputs['per_page'], $grid['perPageValues'])) {
        $inputs['per_page'] = $grid['perPageDefaultValue'];
    }
    if (!is_numeric($inputs['page']) || ($inputs['page'] < 1)) {
        $inputs['page'] = $grid['pageDefaultValue'];
    }

    $inputs['page'] = (int)$inputs['page'];
    $inputs['per_page'] = (int)$inputs['per_page'];

    $skipRecords = ($inputs['page'] * $inputs['per_page']) - $inputs['per_page'];
    $limitQuery = ' SKIP ' . $skipRecords . ' LIMIT ' . $inputs['per_page'];

    $data['inputs'] = $inputs;
    $data['limitQuery'] = $limitQuery;
    $data['sortByQuery'] = $sortByQuery;
    $data['searchByQuery'] = $searchByQuery;
    $data['returnString'] = $returnString;

    return $data;
}

/**
 * Function to replace all data points in template with provided values
 * @param null $template
 * @param array $dataPoints
 * @return bool|mixed|null
 */
function addDataPoints($template = null, $dataPoints = array())
{

    //If template is null the return
    if ($template == null)
        return false;

    //Check data Points are available
    if (!is_array($dataPoints) || count($dataPoints) < 1)
        return false;

    //replace all data points with values
    foreach ($dataPoints as $key => $value) {
        //check data points are available if yes then replace it with values
        if (strpos($template, '{{' . $key . '}}') != false) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
    }
    return $template;
}

/**
 * Function to get all the hash tags from string
 * @param null $string
 * @return null
 */
function getHashTags($string = null)
{
    //check string is null
    if ($string == null)
        return null;

    //get all hash tags from the string
    preg_match_all('/#\S*\w/i', $string, $matches);
    //return all the hash tags
    return $matches[0];
}

/**
 * Function will replace '.' from micro time with '_' and return
 * @return mixed
 */
function getCurrentTimestamp()
{
    return str_replace('.', '_', LARAVEL_START);
}

/**
 * Exception details for Email
 * @param $e
 * @return mixed
 */
function exception($e)
{
    global $globalConfig, $appConn;
    $msg = \Illuminate\Support\Facades\Lang::get("api.3080");
    if (!is_a($e, 'Exception')) {
        return \ApplicationBase\Facades\Api::error(100, array(), array($msg), true, array($msg));
    }

    $userMsg = $e->getMessage() . '. FILE:: ' . $e->getFile() . ' LINE:: ' . $e->getLine();
    $trace = $userMsg . PHP_EOL;

    // Add Trace to details if debug mode is true
    if (isset($globalConfig['isDebugMode']) && $globalConfig['isDebugMode'] == true) {
        $trace .= $e->getTraceAsString();
    }

    /**
     * If apiAnalytics module present then update api logs
     */
    if (isset($globalConfig['apiAnalytics']['status']) && $globalConfig['apiAnalytics']['status'] == true && isset($appConn['analytics_id'])) {

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

        $update['success'] = false;
        $update['id'] = 100;
        $update['description'] = $msg;
        $update['trace'] = $trace;

        $appConn['mongo']->collection('api_analytics')->where('_id', $appConn['analytics_id'])->update($update);
    }

    return \ApplicationBase\Facades\Api::error(100, array($trace), array($msg), true, array($trace), $e->getMessage());
}

/**
 * Get Contents of a file
 * @param $file
 * @param int $startLine
 * @param int $endLine
 * @return string
 */
function getContentsOfFile($file, $startLine = 0, $endLine = 0)
{
    // File get contents
    $content = '';
    $f = fopen($file, 'r');
    $lineNo = 0;

    while ($line = fgets($f)) {
        $lineNo++;
        if ($lineNo >= $startLine) {
            $content .= $line;
        }
        if ($lineNo == $endLine) {
            break;
        }
    }
    fclose($f);
    return $content;
}

/**
 * Get Error Contents of a file
 * @param $file
 * @param int $startLine
 * @param int $endLine
 * @return string
 */
function getErrorContentsOfFile($file, $startLine = 0, $endLine = 0)
{
    // File get contents
    $content = '';
    $f = fopen($file, 'r');
    $lineNo = 0;

    while ($line = fgets($f)) {
        if (strpos($line, 'Fatal error') !== false || strpos($line, 'Uncaught exception') !== false) {
            $lineNo++;
            if ($lineNo >= $startLine) {
                $content .= $line;
            }
        }
        if ($lineNo == $endLine) {
            break;
        }
    }
    fclose($f);
    return $content;
}

/**
 * Send email of error lines
 * @param $file
 * @param $from
 * @param $to
 * @param string $location
 * @param string $ipAddress
 */
function sendEmailOfErrorLines($file, $from, $to, $location = 'Daemon', $ipAddress = '')
{
    // File get contents
    $emailContent = getErrorContentsOfFile($file, $from, $to);
    $errorInfo = '<pre>' . $emailContent . '</pre>';
    $errorTitle = 'Error recorded at' . (strlen($ipAddress) > 0 ? ' ' . $ipAddress : '') . ' Log file - ' . $file;
    sendErrorEmail($location, $errorTitle, $errorInfo, $ipAddress, $file);
}

/**
 * Send Exception Email
 * @param $exception
 * @param $location
 * @param string $ipAddress
 * @param string $additionalInfo
 */
function sendExceptionEmail($exception, $location, $ipAddress = '', $additionalInfo = '')
{
    $errorTitle = $exception->getMessage() . '. FILE:: ' . $exception->getFile() . ' LINE:: ' . $exception->getLine();
    $errorInfo = '<pre>' . $exception->getTraceAsString() . '</pre>';
    $errorInfo .= $additionalInfo;
    sendErrorEmail($location, $errorTitle, $errorInfo, $ipAddress, $exception->getMessage());
}

/**
 * Send log error email
 * @param $location
 * @param $type
 * @param array $details
 * @param string $ipAddress
 */
function sendLogErrorEmail($location, $type, $details = array(), $ipAddress = '')
{
    $errorTitle = 'Error recorded at Location - ' . $location . (strlen($ipAddress) > 0 ? ' ' . $ipAddress : '') . ' ' . $type;
    $errorInfo = '<pre>' . arrayDisplay($details) . '</pre>';
    sendErrorEmail($location, $errorTitle, $errorInfo, $ipAddress, $type);
}

/**
 * Send Log Info email
 * @param $location
 * @param $type
 * @param array $details
 * @param string $ipAddress
 */
function sendLogInfoEmail($location, $type, $details = array(), $ipAddress = '')
{
    $infoTitle = 'Info recorded at Location - ' . $location . (strlen($ipAddress) > 0 ? ' ' . $ipAddress : '') . ' ' . $type;
    $additionalInfo = '<pre>' . arrayDisplay($details) . '</pre>';
    sendInfoEmail($location, $infoTitle, $additionalInfo, $ipAddress, $type);
}

/**
 * Display Array
 * @param $input
 * @return string
 */
function arrayDisplay($input)
{
    return implode(
        PHP_EOL,
        array_map(
            function ($v, $k) {
                if (true == is_array($v)) {
                    $v = '{' . arrayDisplay($v) . '}';
                } elseif (true == is_object($v)) {
                    $v = '';
                }
                return sprintf("%s => %s", $k, $v);
            },
            $input,
            array_keys($input)
        )
    );
}

/**
 * Send Error email
 * @param string $location
 * @param string $errorTitle
 * @param string $errorInfo
 * @param string $ipAddress
 * @param string $shortInfo
 */
function sendErrorEmail($location = 'Api', $errorTitle = '', $errorInfo = '', $ipAddress = '', $shortInfo = '')
{
    global $globalConfig, $appConfig, $appConn;
    $timestampBefore = LARAVEL_START - $globalConfig['error']['same_email_frequency'];
    $api = \Illuminate\Support\Facades\Request::url();
    $email = array();
    $sendEmail = true;

    /**
     * Check same error email is sent or not
     */
    if (true == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
        $email = $appConn['mongo']->collection('system_errors')
            ->where('created_at', '>=', $timestampBefore)
            ->where('api', '=', $api)
            ->where('error_title', '=', $errorTitle)
            ->first();
    } else {
        $timestampErrorEmail = (file_exists($globalConfig['error']['timestamp_file'])) ? (float)file_get_contents($globalConfig['error']['timestamp_file']) : 0;

        if ($timestampErrorEmail != 0 && $timestampBefore < $timestampErrorEmail) {
            $sendEmail = false;
        }
    }

    if (0 == count($email) && true == $sendEmail) {
        /**
         * Send Email
         */
        $dataPoints = array(
            'api_env' => $globalConfig['apiEnv'],
            'project_key' => $globalConfig['projectKey'],
            'location' => $location,
            'error_title' => $errorTitle,
            'error_info' => $errorInfo,
            'ip_address' => $ipAddress,
            'time' => date('Y-m-d h:i A', time()),
        );
        $template = \Illuminate\Support\Facades\View::make('EmailTemplate-ExceptionError', $dataPoints)->render();

        $subject = strtoupper($globalConfig['projectKey'] . ' ' . $globalConfig['apiEnv']) . ' : Error : ' . $location . (strlen($ipAddress) > 0 ? ' ' . $ipAddress : '') . (strlen($shortInfo) > 0 ? ' ' . $shortInfo : '');
        $sendEmailData = array(
            'to_email' => implode(',', $globalConfig['techops']['email_to']),
            'subject' => $subject,
            'content' => $template,
            'type' => 'EXCEPTION_ERROR',
            'from_email' => $appConfig['emails']['defaultFromEmail'],
            'reply_to_email' => $appConfig['emails']['defaultFromEmail'],
            'from_username' => $globalConfig['smtp']['SMTP_FROM_USERNAME'],
        );

        addToEmailQueue($sendEmailData, false);

        if (true == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
            /**
             * Add in Mongo
             */
            $error = array(
                'api' => $api,
                'error_title' => $errorTitle,
                'error_info' => $errorInfo,
                'location' => $location,
                'created_at' => LARAVEL_START,
                'status' => 'NEW',
            );
            $appConn['mongo']->collection('system_errors')->insert($error);
        } else {
            $fileName = $globalConfig['error']['timestamp_file'];
            $fileHandle = fopen($fileName, 'w');
            $txt = microtime(true);
            fwrite($fileHandle, $txt);
            fclose($fileHandle);
        }
    }
}

/**
 * Send Info email
 * @param string $location
 * @param string $infoTitle
 * @param string $additionalInfo
 * @param string $ipAddress
 * @param string $shortInfo
 */
function sendInfoEmail($location = 'Api', $infoTitle = '', $additionalInfo = '', $ipAddress = '', $shortInfo = '')
{
    global $globalConfig, $appConfig, $appConn;
    $timestampBefore = LARAVEL_START - $globalConfig['error']['same_email_frequency'];
    $api = \Illuminate\Support\Facades\Request::url();
    $email = array();
    $sendEmail = true;

    /**
     * Check same error email is sent or not
     */
    if (true == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
        $email = $appConn['mongo']->collection('system_errors')
            ->where('created_at', '>=', $timestampBefore)
            ->where('api', '=', $api)
            ->where('error_title', '=', $infoTitle)
            ->first();
    } else {
        $timestampErrorEmail = (file_exists($globalConfig['error']['timestamp_file'])) ? (float)file_get_contents($globalConfig['error']['timestamp_file']) : 0;

        if ($timestampErrorEmail != 0 && $timestampBefore < $timestampErrorEmail) {
            $sendEmail = false;
        }
    }

    if (0 == count($email) && true == $sendEmail) {
        /**
         * Send Email
         */
        $dataPoints = array(
            'api_env' => $globalConfig['apiEnv'],
            'project_key' => $globalConfig['projectKey'],
            'location' => $location,
            'info_title' => $infoTitle,
            'additional_info' => $additionalInfo,
            'ip_address' => $ipAddress,
            'time' => date('Y-m-d h:i A', time()),
        );
        $template = \Illuminate\Support\Facades\View::make('EmailTemplate-Info', $dataPoints)->render();

        $subject = strtoupper($globalConfig['projectKey'] . ' ' . $globalConfig['apiEnv']) . ' : Info : ' . $location . (strlen($ipAddress) > 0 ? ' ' . $ipAddress : '') . (strlen($shortInfo) > 0 ? ' ' . $shortInfo : '');
        $sendEmailData = array(
            'to_email' => implode(',', $globalConfig['techops']['email_to']),
            'subject' => $subject,
            'content' => $template,
            'type' => 'INFO_EMAIL',
            'from_email' => $appConfig['emails']['defaultFromEmail'],
            'reply_to_email' => $appConfig['emails']['defaultFromEmail'],
            'from_username' => $globalConfig['smtp']['SMTP_FROM_USERNAME'],
        );

        addToEmailQueue($sendEmailData, false);

        if (true == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
            /**
             * Add in Mongo
             */
            $error = array(
                'api' => $api,
                'error_title' => $infoTitle,
                'error_info' => $additionalInfo,
                'location' => $location,
                'created_at' => LARAVEL_START,
                'status' => 'NEW',
            );
            $appConn['mongo']->collection('system_errors')->insert($error);
        } else {
            $fileName = $globalConfig['error']['timestamp_file'];
            $fileHandle = fopen($fileName, 'w');
            $txt = microtime(true);
            fwrite($fileHandle, $txt);
            fclose($fileHandle);
        }
    }
}

/**
 * Unset array keys
 * @param $inputs
 * @param array $unset
 * @return mixed
 */
function unsetKeys($inputs, $unset = array())
{
    if (!is_array($inputs)) {
        return $inputs;
    }

    foreach ($unset as $value) {
        unset($inputs[$value]);
    }
    return $inputs;
}

/**
 * Get Client IP address
 * @return string
 */
function getClientIp()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = '0.0.0.0';
    return $ipaddress;
}

/**
 * Remove string start
 * @param $needle
 * @param $haystack
 * @return string
 */
function removeStringStartsWith($needle, $haystack)
{
    if (is_array($haystack) || is_array($needle) || is_object($haystack) || is_object($needle)) {
        return false;
    }

    if (substr($haystack, 0, strlen($needle)) == $needle) {
        $haystack = substr($haystack, strlen($needle));
    }
    return $haystack;
}

/**
 * Process Search Query
 * @param $columns
 * @param $APIInputs
 * @param string $defaultWhere
 * @param array $defaultAPIInputs
 * @return mixed
 */
function processSearchQuery($columns, $APIInputs, $defaultWhere = '', $defaultAPIInputs = array())
{
    global $appConfig;
    $grid = $appConfig['grid'];

    $gridInputs = $grid['defaultInputs'];
    if (true == valArr($defaultAPIInputs)) {
        $gridInputs = array_merge($gridInputs, $defaultAPIInputs);
    }

    $inputs = array_merge($gridInputs, $APIInputs);

    $searchByArray = array();
    $searchByQuery = '';

    if (!empty($inputs['search_value'])) {

        //if empty searchby then add all searchable columns
        if (empty($inputs['search_by'])) {
            foreach ($columns as $key => $val) {
                if ($val['search']) {
                    $inputs['search_by'][] = $key;
                }
            }
        } else {

            foreach ($inputs['search_by'] as $searchByIndex => $searchBy) {

                if (!array_key_exists($searchBy, $columns) || $columns[$searchBy]['search'] == false) {
                    unset($inputs['search_by'][$searchByIndex]);
                }
            }
        }

        foreach ($inputs['search_by'] as $val) {
            $searchByArray[] = $columns[$val]['db_name'] . ' =~  \'(?i).*' . $inputs['search_value'] . '.*\' ';
        }
    } else {
        $inputs['search_by'] = array();
    }

    if (!empty($searchByArray) || '' != trim($defaultWhere)) {
        $searchByQuery = ' WHERE ';
        $searchByQuery .= ('' != trim($defaultWhere)) ? trim($defaultWhere) . ' ' : '';
        $searchByQuery .= (!empty($searchByArray) && '' != trim($defaultWhere)) ? ' AND ' : '';
        $searchByQuery .= (!empty($searchByArray)) ? '(' . implode(' OR ', $searchByArray) . ')' : '';
    }

    $returnString = ' RETURN ';

    $i = 0;
    foreach ($columns as $displayCol => $dbCol) {
        $i++;
        $returnString .= $dbCol['db_name'] . ' AS `' . $displayCol . ($i == count($columns) ? '` ' : '`, ');
    }

    $data['searchByQuery'] = $searchByQuery;
    $data['returnString'] = $returnString;

    return $data;
}

/**
 * Get SSL file with CURL
 * @param $url
 * @return mixed
 */
function getSslFile($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * Get URL data with CURL
 * @param $url
 * @param int $timeout
 * @return bool
 */
function checkValidUrlData($url, $timeout = 3)
{
    $bool = false;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // For Not displaying result on screen
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // timeout in seconds
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($ch);

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($status === 200) {
        $bool = true;
    }
    curl_close($ch);

    return $bool;
}

/**
 * Find associative array
 *
 * @param $array
 * @return boolean
 */
function is_assoc($array)
{
    return (bool)count(array_filter(array_keys($array), 'is_string'));
}

/**
 * Calculate String to Sign for SignatureVersion 2
 * @param array $parameters request parameters
 * @return String to Sign
 */
function calculateStringToSignV2($url, $method, array $parameters)
{
    $data = $method;
    $data .= "\n";
    $url = str_replace(array('http://', 'https://'), array('', ''), $url);
    $data .= $url;
    $data .= "\n";
    uksort($parameters, 'strcmp');
    $data .= _getParametersAsString($parameters);
    return $data;
}

function _urlencode($value)
{
    return str_replace('%7E', '~', rawurlencode($value));
}

/**
 * Convert paremeters to Url encoded query string
 */
function _getParametersAsString(array $parameters)
{
    $queryParameters = array();
    foreach ($parameters as $key => $value) {
        if (is_bool($value)) {
            if ($value === false) {
                $value = 'false';
            } else {
                $value = 'true';
            }
        }

//        $queryParameters[] = $key . '=' . _urlencode($value);
        $queryParameters[] = $key . '=' . $value;
    }
    return implode('&', $queryParameters);
}


/**
 * Computes RFC 2104-compliant HMAC signature.
 */
function hmacSignature($data, $key, $algorithm = 'HmacSHA256')
{
    if ($algorithm === 'HmacSHA1') {
        $hash = 'sha1';
    } else if ($algorithm === 'HmacSHA256') {
        $hash = 'sha256';
    } else {
        throw new Exception ("Non-supported signing method specified");
    }
    return base64_encode(
        hash_hmac($hash, $data, $key, true)
    );
}

/**
 * @function make multilevel array to single level
 * @param array $arrData
 * @param array $path
 * @return array
 */
function getSingleLevelArray(array $arrData, array $path = array())
{
    $arrResult = array();

    //input data array
    foreach ($arrData as $key => $value) {
        //find out depth of array
        $depth = array_merge($path, array($key));

        //check input data is array or not
        if (is_array($value)) {
            $arrResult = array_merge($arrResult, getSingleLevelArray($value, $depth));
        } else {
            $arrResult[join('.', $depth)] = $value;
        }
    }
    ksort($arrResult);
    return $arrResult;
}

/**
 * Check URL is valid or not
 * @param $url
 * @return bool
 */
function valUrl($url)
{
    try {
        $array = get_headers($url);
        $string = $array[0];
        if (strpos($string, '200')) {
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Sort array of arrays
 * @param $array
 * @param $subField
 * @param int $sortOrder
 * @return mixed
 */
function sortArrayOfArray($array, $subField, $sortOrder = SORT_ASC)
{
    $sortArray = array();
    foreach ($array as $key => $row) {
        $sortArray[$key] = $row[$subField];
    }

    array_multisort($sortArray, $sortOrder, $array);

    return $array;
}

/**
 * Log Time for command prompt
 * Ex. [2015/08/31 10:29:41.884600] [info]
 * @param string $type
 * @return string
 */
function logTime($type = 'info')
{
    $time = microtime();
    $dateTime = explode(' ', $time);
    return '[' . date('Y/m/d H:i:s.', $dateTime[1]) . (round(1000000 * $dateTime[0] / 1000)) . '] [' . $type . '] ';
}

/**
 * Count Lines of a File
 * @param $filePath
 * @return int
 */
function countLines($filePath)
{
    /*** open the file for reading ***/
    $handle = fopen($filePath, "r");
    /*** set a counter ***/
    $count = 0;
    /*** loop over the file ***/
    while (fgets($handle)) {
        /*** increment the counter ***/
        $count++;
    }
    /*** close the file ***/
    fclose($handle);
    /*** show the total ***/
    return $count;
}

/**
 * Count error lines in file
 * @param $filePath
 * @return int
 */
function countErrorLines($filePath)
{
    try {
        /*** set a counter ***/
        $count = 0;
        if (file_exists($filePath)) {
            /*** open the file for reading ***/
            $handle = fopen($filePath, "r");
            /*** loop over the file ***/
            while ($line = fgets($handle)) {
                /*** increment the counter ***/
                if (strpos($line, 'Fatal error') !== false || strpos($line, 'Uncaught exception') !== false) {
                    $count++;
                }
            }
            /*** close the file ***/
            fclose($handle);
        }
        /*** show the total ***/
        return $count;
    } catch (\Exception $e) {
        return 0;
    }
}