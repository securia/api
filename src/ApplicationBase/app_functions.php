<?php

/**
 * Function to get users information from MONGODB
 * @param $strToken
 * @param bool $allDetails
 */
function getAdminUserByToken($strToken, $allDetails = false)
{
    try {
        global $appConfig;

        if (0 > strlen($strToken) || '' == $strToken)
            return false;

        //Fetch user from mongoDB
        $userTokenInfo = \Illuminate\Support\Facades\DB::connection('mongodb')->collection($appConfig['mongodb']['collection']['admin_sessions'])->where('_id', $strToken)->first();

        //check User Token info is available in mongo DB
        if (valArr($userTokenInfo) == true) {

            //Check All details required OR not
            if ($allDetails == false) {
                return (int)$userTokenInfo['id'];
            }

            return $userTokenInfo;
        }
        return null;
    } catch (Exception $e) {
        die(exception($e));
    }
}

/**
 * Get Device token info
 * @param array $wheres
 */
function getTokenInfo($wheres = array(array('token', '=', '')))
{
    global $appConn, $appConfig;
    try {
        $db = $appConn['mongo']->collection('sessions');
        foreach ($wheres as $where) {
            if (count($where) == 2) {
                $where[2] = $where[1];
                $where[1] = '=';
            }
            $db->where($where[0], $where[1], $where[2]);
        }

        $data = (array)$db->first();

        if (empty($data)) { //token info not found
            return \ApplicationBase\Facades\Api::error(5040, array(), array('User information'));
        }

        if ($appConfig["sessions"]['app']['ttl'] == 0) { //session is forever
            return \ApplicationBase\Facades\Api::success(6010, $data, array('Token info fetched'));
        }

        if ((time() + $appConfig["session"]['app']['ttl']) < $data['token_alive_untill']) { //check for token expiry
            return \ApplicationBase\Facades\Api::success(6010, $data, array('Token info fetched'));
        }

        //token expired
        return \ApplicationBase\Facades\Api::error(5030, array(), array('Token'));
    } catch (Exception $e) {
        die(exception($e));
    }
}

/**
 * Add Email to email queue
 * @param $data
 * @param bool $singleEmail
 */
function addToEmailQueue($data, $singleEmail = true)
{
    // Add Email to SenEmail Queue here
    if (isset($data['to_email']) && ($data['to_email'])) {
        // Individual Email to all recipients
        if (true === $singleEmail) {
            if (true == valStr($data['to_email'])) {
                $emailTo = explode(',', $data['to_email']);
            } else {
                $emailTo = $data['to_email'];
            }

            foreach ($emailTo as $email) {
                $data['to_email'] = $email;
                \Illuminate\Support\Facades\Queue::push('ApplicationBase\SendEmail', $data);
            }
        } else {
            \Illuminate\Support\Facades\Queue::push('ApplicationBase\SendEmail', $data);
        }
    }
}

/**
 * Call refresh dashboard queue
 * @param $data
 */
function addToRefreshDashboardQueue($data)
{
    $status = \Illuminate\Support\Facades\Queue::push('ApplicationBase\RefreshDashboard', $data);

}

/**
 * Resize image queue
 * @param $data
 */
function addToResizeImageQueue($data)
{

    if (true == valArr($data)) {
        //Add Image to resize image queue
        $status = \Illuminate\Support\Facades\Queue::push('ApplicationBase\ResizeImage', $data);
    }
}

/**
 * Remove old files from s3 using queue
 * @param $data
 */
function addToRemoveOldFilesQueue($data)
{

    if (true == valArr($data)) {
        //Add Image to resize image queue
        $status = \Illuminate\Support\Facades\Queue::push('ApplicationBase\RemoveOldFiles', $data);
    }
}

/**
 * Generate CSV of players and
 * @param $data
 */
function addToGeneratePlayerCSVQueue($data)
{

    if (true == valArr($data)) {
        //Add Image to resize image queue
        $status = \Illuminate\Support\Facades\Queue::push('ApplicationBase\ProcessPlayersCSV', $data);
    }
}

/**
 * Generate CSV of devices and
 * @param $data
 */
function addToGenerateDeviceCSVQueue($data)
{

    if (true == valArr($data)) {
        //Add Image to resize image queue
        $status = \Illuminate\Support\Facades\Queue::push('ApplicationBase\ProcessDevicesCSV', $data);
    }
}

/**
 * Get System settings of App
 * @param array $inputs
 * @return mixed
 */
function getSystemSettings($inputs = array())
{
    global $appConn;

    $objSettings = $appConn['mongo']->collection('platform_settings')
        ->where('platform', $inputs['platform'])
        ->where('version', $inputs['version']);

    if (isset($inputs['type']) && !empty($inputs['type'])) {
        $objSettings->where('type', $inputs['type']);
    }
    $objSettings = $objSettings->get();
    if (false == valArr($objSettings)) {
        return \ApplicationBase\Facades\Api::error(3090, array('msg' => 'Settings does not exist'), array());
    }

    $return = array();

    if (count($objSettings) == 1) {
        $objSettings = $objSettings[0];
        unset($objSettings['_id']);
        $return = $objSettings['settings'];
    } else {
        foreach ($objSettings as $index => $objSetting) {
            unset($objSetting[$index]['_id']);
            $return[$objSetting['type']] = $objSetting['settings'];
        }
    }
    return \ApplicationBase\Facades\Api::success(2040, $return, 'Setting');
}

/**
 * Get Todays date
 * @return int
 */
function getTodaysDate()
{
    global $appConfig;

    $currentTime = time();

    $dayStartTime = strtotime(date('Ymd ' . $appConfig['day_changed_at'], $currentTime));

    if ($currentTime >= $dayStartTime) {
        return (int)date('Ymd', strtotime('+1 day', $currentTime));
    }

    return (int)date('Ymd', $currentTime);
}

function isWeekEnd($timestamp)
{
    global $appConfig;
    $currentDay = strtolower(date('l', $timestamp));
    $currentDate = date('Ymd', $timestamp);
    if ($currentDay == 'friday') {
        $dayStartTime = strtotime($currentDate . $appConfig['day_changed_at']);
        return $timestamp >= $dayStartTime ? true : false;

    } else if ($currentDay == 'sunday') {
        $dayEndTime = strtotime($currentDate . $appConfig['day_changed_at']);
        return $timestamp < $dayEndTime ? true : false;
    } else if ($currentDay == 'saturday') {
        return true;
    } else {
        return false;
    }
}


/**
 * Get day of week 10PM to 10PM
 * 0 - Sun
 * 6 - Sat
 * @return bool|string
 */
function getDayOfWeek()
{
    global $appConfig;
    $currentTime = time();
    $dayStartTime = strtotime(date('Ymd ' . $appConfig['day_changed_at'], $currentTime));

    if ($currentTime >= $dayStartTime) {
        return date('w', strtotime('+1 day', $currentTime));
    } else {
        return date('w', $currentTime);
    }
}

/**
 * Get Week range from Sunday to Sunday
 * @param null $currentTime
 * @return mixed
 */
function getWeekDateRange($currentTime = null)
{
    global $appConfig;

    if (is_null($currentTime)) {
        $currentTime = time();
    }

    $todaysDay = strtolower(date('l', $currentTime));

    $return['start_timestamp'] = strtotime(date('Y-m-d', strtotime('last sunday', $currentTime)) . ' ' . $appConfig['day_changed_at']);
    $return['end_timestamp'] = strtotime(date('Y-m-d', strtotime('next sunday', $currentTime)) . ' ' . $appConfig['day_changed_at']);

    if ($todaysDay == "sunday") {
        $tempDateTime = date('Y-m-d', $currentTime) . " " . $appConfig['day_changed_at'];
        $tempTimestamp = strtotime($tempDateTime, $currentTime);
        if ($currentTime < $tempTimestamp) {
            $return['end_timestamp'] = strtotime(date('Y-m-d', strtotime('sunday', $currentTime)) . ' ' . $appConfig['day_changed_at']);
            $return['start_timestamp'] = strtotime(date('Y-m-d', strtotime('last sunday', $return['end_timestamp'])) . ' ' . $appConfig['day_changed_at']);
        } else {
            $return['start_timestamp'] = strtotime(date('Y-m-d', strtotime('sunday', $currentTime)) . ' ' . $appConfig['day_changed_at']);
            $return['end_timestamp'] = strtotime(date('Y-m-d', strtotime('next sunday', $currentTime)) . ' ' . $appConfig['day_changed_at']);
        }
    }
    return $return;
}

/**
 * Validate Date
 * @param $date
 * @return bool
 */
function validateDate($date)
{
    $d = DateTime::createFromFormat('Ymd', $date);
    return $d && $d->format('Ymd') == $date;
}

/**
 * Make Valid date range
 * @param $data
 * @return mixed
 */
function makeValidDateRange($data)
{
    global $appConfig;

    if ($data['download_from'] < date('Ymd', strtotime($appConfig['first_puzzle_date']))) {
        $data['download_from'] = (int)date('Ymd', strtotime($appConfig['first_puzzle_date']));
    }

    if ($data['download_till'] < date('Ymd', strtotime($appConfig['first_puzzle_date']))) {
        $data['download_till'] = (int)date('Ymd', strtotime($appConfig['first_puzzle_date']));
    } else if ($data['download_till'] > getTodaysDate()) {
        $data['download_till'] = getTodaysDate();
    }

    return $data;
}

function makeValidDateRangeForClear($data)
{
    global $appConfig;

    if ($data['clear_from'] < date('Ymd', strtotime($appConfig['first_puzzle_date']))) {
        $data['clear_from'] = (int)date('Ymd', strtotime($appConfig['first_puzzle_date']));
    }

    if ($data['clear_till'] < date('Ymd', strtotime($appConfig['first_puzzle_date']))) {
        $data['clear_till'] = (int)date('Ymd', strtotime($appConfig['first_puzzle_date']));
    } else if ($data['clear_till'] > getTodaysDate()) {
        $data['clear_till'] = getTodaysDate();
    }

    return $data;
}

/**
 * Process leader board data based on Result
 * @param $meId
 * @param $players
 * @param $limit
 * @return array
 */
function processLeaderBoardDisplay($meId, $players, $limit = 10)
{
    if (false == valArr($players)) {
        return array();
    }
    try {
        $leaderBoard = array();
        $rank = 1;
        $start = 0;
        $isMeIncluded = false;
        foreach ($players as $player) {
            if ($start < $limit) {
                if ($player['player_id'] == $meId) {
                    $isMeIncluded = true;
                }
                if (($isMeIncluded === false && $start < ($limit - 1)) || ($isMeIncluded === true)) {
                    $leaderBoardUser = $player;
                    $leaderBoardUser['rank'] = $rank;
                    $leaderBoard[] = $leaderBoardUser;
                    $start++;
                }
                $rank++;
            } else {
                break;
            }
        }
        return $leaderBoard;
    } catch (Exception $e) {
        die(exception($e));
    }
}

/**
 * Add Data to CSV
 * @param $data
 * @param $filename
 * @return bool
 */
function dataToCsv($data, $filename)
{
    try {
        $filename = public_path() . '/' . $filename;
        $fp = fopen($filename, 'w');
        foreach ($data as $row) {
            fputcsv($fp, (array)$row);
        }
        fclose($fp);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Make CSV Groups of Data
 *
 * @param $startId
 * @param $limit
 * @param $maxId
 * @param $table
 * @param $csvPath
 */
function makeCsvGroups($startId, $limit, $maxId, $table, $csvPath)
{
    global $globalConfig, $bootstrap;
    if (true == in_array($globalConfig['apiEnv'], $bootstrap['migration']['maxLimitEnv'])) {
        if ($table == 'Devices' && $maxId > $bootstrap['migration']['maxLimitDevices']) {
            $maxId = $bootstrap['migration']['maxLimitDevices'];
        } elseif ($table == 'Players' && $maxId > $bootstrap['migration']['maxLimitPlayers']) {
            $maxId = $bootstrap['migration']['maxLimitPlayers'];
        } elseif ($table == 'Puzzles' && $maxId > $bootstrap['migration']['maxLimitPuzzles']) {
            $maxId = $bootstrap['migration']['maxLimitPuzzles'];
        } else if ($table == 'PlayerDevices' && $maxId > $bootstrap['migration']['maxLimitPlayerDevices']) {
            $maxId = $bootstrap['migration']['maxLimitPlayerDevices'];
        } else if (($table == 'PlayerPuzzles' || $table == 'PlayerPuzzlesArchived') && $maxId > $bootstrap['migration']['maxLimitPlayerPuzzles']) {
            $maxId = $bootstrap['migration']['maxLimitPlayerPuzzles'];
        }
    }

    while (true) {
        $csv['from'] = $startId;
        $csv['to'] = ($maxId > ($startId + $limit - 1)) ? ($startId + $limit - 1) : $maxId;
        makeCsv($table, $csv, $csvPath);
        $startId = $csv['to'] + 1;
        if ($startId > $maxId) {
            break;
        }
    }
    return;
}

/**
 * Make CSV of Players from Range of Id
 *
 * @param string $table
 * @param array $inputs
 * @param $csvPath
 */
function makeCsv($table = '', $inputs = array(), $csvPath)
{
    if ($table == 'Devices') {
        makeCsvOfDevices($inputs, $csvPath);
    } elseif ($table == 'Players') {
        makeCsvOfPlayers($inputs, $csvPath);
    } elseif ($table == 'Puzzles') {
        makeCsvOfPuzzles($inputs, $csvPath);
    } else if ($table == 'PlayerDevices') {
        makeCsvOfPlayerDevices($inputs, $csvPath);
    } else if ($table == 'PlayerPuzzles' || $table == 'PlayerPuzzlesArchived') {
        makeCsvOfPlayerPuzzles($inputs, $csvPath, $table);
    }
    return;
}

/**
 * Make CSV of Players
 * @param array $inputs
 * @param $csvPath
 */
function makeCsvOfPlayers($inputs = array(), $csvPath)
{
    global $appConn, $bootstrap, $globalConfig;

    $select = 'SELECT
                ' . implode($bootstrap['migration']['playerColumns'], ', ') . '
                FROM
                    fb_players
                WHERE
                    is_migrated = 0
                    AND fb_player_id BETWEEN ' . $inputs['from'] . ' AND ' . $inputs['to'] . '
                ';

    $players = $appConn['mysql']->select($select);
    if (valArr($players)) {
        $fileName = $csvPath . 'Players_' . $inputs['from'] . '_' . $inputs['to'] . '.csv';
        $status = dataToCsv($players, $fileName);

        if (true === $status) {
            $csv = array(
                'from' => (int)$inputs['from'],
                'to' => (int)$inputs['to'],
                'is_migrated' => 0,
                'type' => 'Players',
                'file_name' => $globalConfig['apiUrl'] . $fileName,
                'created_at' => LARAVEL_START,
                'updated_at' => LARAVEL_START,
            );

            $appConn['mongo']->collection('mysql_csv')->where('file_name', $globalConfig['apiUrl'] . $fileName)->update($csv, array('upsert' => true));
        }
    }
    return;
}

/**
 * Make CSV of Devices
 * @param array $inputs
 * @param $csvPath
 */
function makeCsvOfDevices($inputs = array(), $csvPath)
{
    global $appConn, $bootstrap, $globalConfig;

    $select = 'SELECT
                    ' . implode($bootstrap['migration']['deviceColumns'], ', ') . '
                  FROM
                      devices d
                      JOIN device_balance b ON d.device_id = b.devices_device_id
                  WHERE
                      device_id BETWEEN ' . $inputs['from'] . ' AND ' . $inputs['to'] . '
                      AND is_migrated = 0
                  ';

    $devices = $appConn['mysql']->select($select);
    if (valArr($devices)) {
        $fileName = $csvPath . 'Devices_' . $inputs['from'] . '_' . $inputs['to'] . '.csv';
        $status = dataToCsv($devices, $fileName);
        if (true === $status) {
            $csv = array(
                'from' => (int)$inputs['from'],
                'to' => (int)$inputs['to'],
                'is_migrated' => 0,
                'type' => 'Devices',
                'file_name' => $globalConfig['apiUrl'] . $fileName,
                'created_at' => LARAVEL_START,
                'updated_at' => LARAVEL_START,
            );

            $appConn['mongo']->collection('mysql_csv')->where('file_name', $globalConfig['apiUrl'] . $fileName)->update($csv, array('upsert' => true));
        }
    }
    return;
}

/**
 * Make CSV of Puzzles
 * @param array $inputs
 * @param $csvPath
 */
function makeCsvOfPuzzles($inputs = array(), $csvPath)
{
    global $appConn, $bootstrap, $globalConfig;

    $select = 'SELECT
                    ' . implode($bootstrap['migration']['puzzleColumns'], ', ') . '
                  FROM
                      games g
                  WHERE
                      game_id BETWEEN ' . $inputs['from'] . ' AND ' . $inputs['to'] . '
                      AND is_migrated = 0
                  ';

    $devices = $appConn['mysql']->select($select);
    if (valArr($devices)) {
        $fileName = $csvPath . 'Puzzles_' . $inputs['from'] . '_' . $inputs['to'] . '.csv';
        $status = dataToCsv($devices, $fileName);

        if (true === $status) {
            $csv = array(
                'from' => (int)$inputs['from'],
                'to' => (int)$inputs['to'],
                'is_migrated' => 0,
                'type' => 'Puzzles',
                'file_name' => $globalConfig['apiUrl'] . $fileName,
                'created_at' => LARAVEL_START,
                'updated_at' => LARAVEL_START,
            );

            $appConn['mongo']->collection('mysql_csv')->where('file_name', $globalConfig['apiUrl'] . $fileName)->update($csv, array('upsert' => true));
        }
    }
    return;
}

/**
 * Make CSV of Player Devices
 * @param array $inputs
 * @param $csvPath
 */
function makeCsvOfPlayerDevices($inputs = array(), $csvPath)
{
    global $appConn, $bootstrap, $globalConfig;

    $select = 'SELECT
                    ' . implode($bootstrap['migration']['playerDeviceColumns'], ', ') . '
                  FROM
                      devices d
                      JOIN player_devices pd ON d.device_id = pd.devices_device_id
                  WHERE
                      device_id BETWEEN ' . $inputs['from'] . ' AND ' . $inputs['to'] . '
                      AND device_type = "FACEBOOK"
                      AND is_using = 0
                  ';

    $devices = $appConn['mysql']->select($select);

    if (valArr($devices)) {
        $fileName = $csvPath . 'PlayerDevices_' . $inputs['from'] . '_' . $inputs['to'] . '.csv';
        $status = dataToCsv($devices, $fileName);

        if (true === $status) {
            $csv = array(
                'from' => (int)$inputs['from'],
                'to' => (int)$inputs['to'],
                'is_migrated' => 0,
                'type' => 'PlayerDevices',
                'file_name' => $globalConfig['apiUrl'] . $fileName,
                'created_at' => LARAVEL_START,
                'updated_at' => LARAVEL_START,
            );

            $appConn['mongo']->collection('mysql_csv')->where('file_name', $globalConfig['apiUrl'] . $fileName)->update($csv, array('upsert' => true));
        }
    }
    return;
}

/**
 * Make CSV of Player Devices
 * @param array $inputs
 * @param $csvPath
 * @param $table
 */
function makeCsvOfPlayerPuzzles($inputs = array(), $csvPath, $table)
{
    global $appConn, $bootstrap, $globalConfig;

    if ($table == 'PlayerPuzzlesArchived') {
        $tableName = 'player_games_archived';
    } else {
        $tableName = 'player_games';
    }

    /**
     * From player_games and player_games_archived
     * 1. In progress puzzles
     * 2. completed puzzles
     * Should be migrated only
     */
    $select = 'SELECT
                    ' . implode($bootstrap['migration']['playerPuzzleColumns'], ', ') . '
                    FROM
                        ' . $tableName . ' p
                        JOIN devices d
                        ON
                            p.devices_device_id = d.device_id
                            AND d.device_type = "FACEBOOK"
                    WHERE
                        player_game_id BETWEEN ' . $inputs['from'] . ' AND ' . $inputs['to'] . '
                        AND p.is_migrated = 0
                        AND p.is_purged = 0
                    ';

    $puzzles = $appConn['mysql']->select($select);

    if (valArr($puzzles)) {
        $fileName = $csvPath . 'PlayerPuzzles_' . $inputs['from'] . '_' . $inputs['to'] . '.csv';
        $status = dataToCsv($puzzles, $fileName);

        if (true === $status) {
            $csv = array(
                'from' => (int)$inputs['from'],
                'to' => (int)$inputs['to'],
                'is_migrated' => 0,
                'type' => $table,
                'file_name' => $globalConfig['apiUrl'] . $fileName,
                'created_at' => LARAVEL_START,
                'updated_at' => LARAVEL_START,
            );

            $appConn['mongo']->collection('mysql_csv')->where('file_name', $globalConfig['apiUrl'] . $fileName)->update($csv, array('upsert' => true));
        }
    }
    return;
}

/**
 * Get Date Time adjuster
 * @param $datetime
 * @return bool|string
 */
function dateTimeAdjuster($datetime)
{
    global $appConfig;
    $date = date('Y-m-d', $datetime);
    $time = date('H:i:s', $datetime);
    if ($time >= $appConfig['day_changed_at'] && $time <= '23:59:59') {
        $date = date('Y-m-d', strtotime("+1 day", strtotime($date)));
    }
    return $date;
}

/**
 * Check last login for Consecutive days count
 * @param $lastLogin
 * @return int
 */
function isConsecutiveDaysLogin($lastLogin)
{
    $lastLoginDate = dateTimeAdjuster((int)$lastLogin);
    $currentLogin = dateTimeAdjuster(time());
    $previousDate = date('Y-m-d', strtotime("-1 day", strtotime($currentLogin)));
    if ($lastLoginDate == $currentLogin) {
        return 1; //no increment
    } else if ($lastLoginDate == $previousDate) {
        return 2; //increment
    } else {
        return 3; //reset
    }
}

/**
 * Merge input Bits with stored bits
 * @param $stored
 * @param $inputs
 * @return int
 */
function mergeBits($stored, $inputs)
{
    return (int)$stored == 1 || (int)$inputs == 1 ? 1 : 0;
}

function decompress($version, $file)
{
    $out = tmpfile();
    // write 'uncompressed' header
    fwrite($out, pack('VV', $version, 0));
    while (1) {
        $len = readLong($file);
        if (feof($file))
            break;
        $block = fread($file, $len);
        fwrite($out, gzuncompress($block));
    }
    rewind($out);
    // seek past the headers
    fseek($out, 8, SEEK_CUR);
    fclose($file);
    return $out;
}

function readLong($file)
{
    $x = fread($file, 4);
    $pck = @unpack('Vdata', $x);
    return $pck['data'];
}

function readShort($file)
{
    $x = fread($file, 2);
    $pck = @unpack('vdata', $x);
    return $pck['data'];
}

function readByte($file)
{
    $x = fread($file, 1);
    $pck = @unpack('Cdata', $x);
    return $pck['data'];
}

function readNullTerminatedString($handle)
{
    $string = '';
    //Read bytes into a buffer until we hit a null byte
    do {
        $byte = readByte($handle);
        $string .= chr($byte);
    } while ($byte != 0x0);
    return cleanString(trim($string));
}

function cellNeedsAcrossNumber($row, $col)
{

}

function cleanString($text)
{
    $regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;
    return preg_replace($regex, '$1', $text);
}

function recursive_keys(array $array, array $path = array())
{
    $result = array();
    foreach ($array as $key => $val) {
        $currentPath = array_merge($path, array($key));
        if (is_array($val)) {
            $result = array_merge($result, recursive_keys($val, $currentPath));
        } else {
            $result[] = join('/', $currentPath);
        }
    }
    return $result;
}

function maskValues($input, $maskArray, $maskNumChar = 4)
{
    foreach ($maskArray as $path) {
        $value = array_get_path($input, $path, $delim = '/');
        if (strlen($value) > 10) {
            array_set_path(maskValue($value, $maskNumChar), $input, $path, $delimiter = '/');
        } else {
            array_set_path(maskValue($value, 2), $input, $path, $delimiter = '/');
        }
    }

    return $input;
}

function maskValue($value, $maskNumChar)
{
    $len = strlen($value);
    $str = '';
    $str .= substr($value, 0, $maskNumChar);
    for ($i = $maskNumChar; $i < ($len - $maskNumChar); $i++) {
        $str .= '*';
    }
    $str .= substr($value, ($len - $maskNumChar), $len);
    return $str;
}


function &array_get_path(&$array, $path, $delim = NULL, $value = NULL, $unset = false)
{

    $num_args = func_num_args();

    $element = &$array;


    if (!is_array($path) && strlen($delim = (string)$delim)) {

        $path = explode($delim, $path);

    }
    // if


    if (!is_array($path)) {

        // Exception?

    }

    // if


    while ($path && ($key = array_shift($path))) {

        if (!$path && $num_args >= 5 && $unset) {


            unset($element[$key]);

            unset($element);

            $element = NULL;

        } // if


        else {
            $element =& $element[$key];
        }
        // else

    }

    // while


    if ($num_args >= 4 && !$unset) {

        $element = $value;

    }
    // if


    return $element;

}

// array_get_path


function array_set_path($value, &$array, $path, $delimiter = NULL)
{

    array_get_path($array, $path, $delimiter, $value);


    return;

}

// array_set_path


function array_unset_path(&$array, $path, $delimiter = NULL)
{

    array_get_path($array, $path, $delimiter, NULL, true);


    return;

}

// array_unset_path


function array_has_path($array, $path, $delimiter = NULL)
{

    $has = false;


    if (!is_array($path)) {

        $path = explode($delimiter, $path);

    }
    // if


    foreach ($path as $key) {

        if ($has = array_key_exists($key, $array)) {

            $array = $array[$key];

        }
        // if

    }
    // foreach


    return $has;

}

// array_has_path;

function migrateFbAccount($inputs)
{
    global $globalConfig;

    $ch = curl_init($globalConfig['old_backend_api_url'] . 'session/signin');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($inputs));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function getCollectionName($type, $param)
{
    global $appConfig;
    switch ($type) {
        case 'player_daily_puzzles':
            $rangeStart = (int)($param / $appConfig['player_puzzles_partition_range']) * $appConfig['player_puzzles_partition_range'] + 1;
            $rangeEnd = $rangeStart + $appConfig['player_puzzles_partition_range'] - 1;
            return 'z_player_daily_puzzles_' . $rangeStart . '_' . $rangeEnd;
        case 'player_bonus_puzzles':
            return $type;
        case 'player_h2h_puzzles':
            return $type;
        case 'best_times':
            $date_regex = '/^([0-9]{4})([0-9]{2})([0-9]{2})$/';
            $validationStatus = preg_match($date_regex, $param, $matches);
            if (!$validationStatus) {
                return false;
            }
            //checkdate(month, day, year);
            $status = checkdate($matches[2], $matches[3], $matches[1]);

            if (!$status) {
                return false;
            }
            $yyyymm = date('Ym', strtotime($param));
            return 'z_best_times_' . $yyyymm;
    }
}