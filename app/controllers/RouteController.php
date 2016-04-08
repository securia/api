<?php

class RouteController extends BaseController
{
    /**
     * Function to route API Calls
     *
     * @param null $module
     * @param null $version
     * @param null $controller
     * @param null $function
     * @return mixed
     */
    public function route($module = null, $version = null, $controller = null, $function = null)
    {
        try {
            // $routeConfig should have all configs required for this function
            global $routeConfig, $globalConfig, $appConn;
            $routeConfig['route']['module'] = $module;
            $routeConfig['route']['version'] = $version;
            $routeConfig['route']['controller'] = $controller;
            $routeConfig['route']['function'] = $function;

            // Check route options are available in config file.
            if (!isset($routeConfig['api'][$module][$version][$controller . '/' . $function])) {
                if(!isset($globalConfig['deprecatedApiVersions'][$module])){
                    $globalConfig['deprecatedApiVersions'][$module] = array();
                }
                if (true == in_array($version, $globalConfig['deprecatedApiVersions'][$module])) {
                    return \ApplicationBase\Facades\Api::error(3100, array('msg' => 'Deprecated API Call'), array());
                } else {
                    return \ApplicationBase\Facades\Api::error(3080, array('msg' => 'Invalid API Call'), array());
                }
            }

            $function = $routeConfig['api'][$module][$version][$controller . '/' . $function];

            // check route Method Is valid. (GET/POST/DELETE/PUT)
            if (!isset($function['method']) && ((strtolower($function['method']) != strtolower(\Illuminate\Support\Facades\Input::method())) || 'ANY' != $function['method'])) {
                return \ApplicationBase\Facades\Api::error(3080, array('msg' => 'Invalid API Method'), array());
            }

            $filters = array();
            if (isset($function['filters'])) {
                $filters = $function['filters'];
            }

            /**
             * If apiAnalytics module present then add api logs
             */
            if (isset($globalConfig['apiAnalytics']['status']) && $globalConfig['apiAnalytics']['status'] == true) {
                $rawInputs = \Illuminate\Support\Facades\Input::all();
                if (empty($rawInputs)) {
                    $rawInputs = \Illuminate\Support\Facades\Input::json()->all();
                }

                try {
                    $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
                } catch (\Exception $e) {
                    $content = array('success' => false, 'message' => array('id' => 100, 'description' => 'Failed to connect.'), 'data' => array('mongo' => false));
                    header('HTTP/1.1 500 component down', true, 500);
                    die(json_encode($content));
                }
                $insert = $routeConfig['route'];

                $insert['created_at'] = LARAVEL_START;
                $insert['updated_at'] = LARAVEL_START;
                $insert['duration'] = 0;
                $insert['success'] = false;
                $insert['id'] = 0;
                $insert['client_ip'] = getClientIp();
                $insert['inputs'] = $rawInputs;
                if (isset($rawInputs['token'])) {
                    $insert['token'] = $rawInputs['token'];
                }
                $appConn['platform'] = $insert['platform'] = 'unknown';

//                $appConn['analytics_id'] = $insert['_id'] = new MongoId();
//                $appConn['mongo']->collection('api_analytics')->insert($insert);

                $inserted = false;
                $retry = 3;
                while (!$inserted) {
                    try {
                        $appConn['mongo']->selectCollection('api_analytics')->insert($insert);
                        $appConn['analytics_id'] = $insert['_id'];
                        $inserted = true;
                        $retry--;
                    } catch (\Exception $e) {
                        $retry--;
                        if ($retry == 0) {
                            die(exception($e));
                        }
                        unset($insert['_id']);
                    }
                }
            }

            $requestMethod = (string)$function['function'];

            //check Is there any Filters needs to be apply on this route
            if (isset($filters)) {
                foreach ($filters as $filter) {
                    if (!empty($filter)) {
                        $status = call_user_func(array($this, $filter));
                        if (isset($status['success']) && $status['success'] == false) {
                            return json_encode($status);
                        }
                    }
                }
            }

            $version = (int)$version;

            //Create Call request for controller function
            $app = app();

            switch ($module) {
                case 'app' :
                    /**
                     * Controllers\\v1\\AuthController
                     */
                    $controller = $app->make('v' . $version . '\\' . $function['controller']);
                    return $controller->callAction($requestMethod, array());

                case 'admin' :
                    /**
                     * Controllers\\admin\\AuthController
                     */
                    $controller = $app->make('admin\\' . $function['controller']);
                    return $controller->callAction($requestMethod, array());
            }

            return \ApplicationBase\Facades\Api::error(3080, array('msg' => 'Invalid API Call'), array());

        } catch (\Exception $e) {
            return exception($e);
        }
    }

    /**
     * @return array|string
     */
    public function authApp()
    {
        try {

            global $routeConfig;
            $inputs = \Illuminate\Support\Facades\Input::all();
            if (valArr(\Illuminate\Support\Facades\Input::json()->all()) == true) {
                $inputs = array_merge($inputs, \Illuminate\Support\Facades\Input::json()->all());
            }

            if (true == empty($inputs['token']) || '' == $inputs['token']) {
                return array('status' => false, 'data' => \ApplicationBase\Facades\Api::error(1040, array(), 'Access Token'));
            }

            // Getting user info using token
            $userInfo = getAdminUserByToken(true);

            if (valArr($userInfo) == false) {
                return array('status' => false, 'data' => \ApplicationBase\Facades\Api::error(1010, array(), 'User'));
            }

//            //Checking for the expiry time
//            if (isset($userInfo['expiry_time']) && ($userInfo['expiry_time'] < strtotime('now'))) {
//                // Deleting user token info from collection
//                $mongoObj = $this->setMongoConnection($routeConfig['mongodb']['collection']['user_token']);
//                $this->mongoDB->collection('user_token')->where('token', $inputs['token'])->delete();
//            } else {
//
//                // Assigning expiry time based on remember me.
//                if (isset($userInfo['remember_me']) && $userInfo['remember_me']) {
//                    $expiryTime = strtotime($routeConfig['user']['rememberMeExpiration']);
//                } else {
//                    $expiryTime = strtotime($routeConfig['user']['normalExpiration']);
//                }
//
//                // Updating the token expiration time
//                $dt = new \DateTime();
//                $now = $dt->format('Y-m-d H:i:s');
//                $savedAt = new \MongoDate(strtotime($now));
//
//                $mongoObj = $this->setMongoConnection($routeConfig['mongodb']['collection']['user_token']);
//                $mongoObj->where('token', $inputs['token'])->update(array('expiry_time' => $expiryTime, 'saved_at' => $savedAt));
//            }

            return array('status' => true, 'data' => []);

        } catch (\Exception $e) {
            return exception($e);
        }
    }


    /**
     * @return array|string
     */
    public function authAdmin()
    {
        try {

            global $routeConfig;
            $inputs = \Illuminate\Support\Facades\Input::all();
            if (valArr(\Illuminate\Support\Facades\Input::json()->all()) == true) {
                $inputs = array_merge($inputs, \Illuminate\Support\Facades\Input::json()->all());
            }

            if (true == empty($inputs['token']) || '' == $inputs['token']) {
                return array('status' => false, 'data' => \ApplicationBase\Facades\Api::error(1040, array(), 'Access Token'));
            }

            // Getting user info using token
            $userInfo = getAdminUserByToken(true, true);

            if (valArr($userInfo) == false) {
                return array('status' => false, 'data' => \ApplicationBase\Facades\Api::error(1010, array(), 'User'));
            }

            //Checking for the expiry time
            if (isset($userInfo['expiry_time']) && ($userInfo['expiry_time'] < strtotime('now'))) {
                // Deleting user token info from collection
                $mongoObj = $this->setMongoConnection($routeConfig['mongodb']['collection']['admin']);
                $this->mongoDB->collection('admin')->where('token', $inputs['token'])->delete();
            } else {

                // Assigning expiry time based on remember me.
                if (isset($userInfo['remember_me']) && $userInfo['remember_me']) {
                    $expiryTime = strtotime($routeConfig['user']['rememberMeExpiration']);
                } else {
                    $expiryTime = strtotime($routeConfig['user']['normalExpiration']);
                }

                // Updating the token expiration time
                $dt = new \DateTime();
                $now = $dt->format('Y-m-d H:i:s');
                $savedAt = new \MongoDate(strtotime($now));

                $mongoObj = $this->setMongoConnection($routeConfig['mongodb']['collection']['admin']);
                $mongoObj->where('token', $inputs['token'])->update(array('expiry_time' => $expiryTime, 'saved_at' => $savedAt));
            }

            return array('status' => true, 'data' => []);

        } catch (\Exception $e) {
            return exception($e);
        }
    }

    /**
     * @function is used for checking api security
     * @return array|string
     */
    public function apiSecurity()
    {
        global $globalConfig;
        try {
            if ($globalConfig['api_security']['status'] == false) {
                return \ApplicationBase\Facades\Api::success(3000, array(), 'Signature validated successfully');
            }

            parse_str($_SERVER["QUERY_STRING"], $query);
            $inputs = \Illuminate\Support\Facades\Input::json()->all();
            $temp = $inputs = array_merge_recursive_ex($query, $inputs);

            if (empty($inputs['signature'])) {
                return \ApplicationBase\Facades\Api::error(1040, array(), 'Signature');
            }

            $inputs['signature'] = urldecode($inputs['signature']);


            $temp = unsetKeys($temp, array('method', 'url', 'signature'));
            $parameters = getSingleLevelArray($temp);

            if (!empty($inputs['method']) && !empty($inputs['url'])) {
                $stringToSign = calculateStringToSignV2($inputs['url'], $inputs['method'], $parameters);
            } else {
                $stringToSign = calculateStringToSignV2(\Illuminate\Support\Facades\Request::url(), \Illuminate\Support\Facades\Request::method(), $parameters);
            }

            $signature = hmacSignature($stringToSign, $globalConfig['api_security']['public_key'], 'HmacSHA256');

            if ($inputs['signature'] != $signature) {
                return \ApplicationBase\Facades\Api::error(5010, array(), 'Signature validation');
            } else {
                return \ApplicationBase\Facades\Api::success(3000, array(), 'Signature validated successfully');
            }
        } catch (\Exception $e) {
            return exception($e);
        }
    }
}
