<?php

namespace v1;

/**
 * Class AuthController
 * @package v1
 */
class AuthController extends \BaseController
{
    /**
     * Generic login function for all platforms
     */
    public function login()
    {
        global $appConn;
        try {
            $rawInputs = \Illuminate\Support\Facades\Input::all();
            if (empty($rawInputs)) {
                $rawInputs = \Illuminate\Support\Facades\Input::json()->all();
            }

            /**
             * Input Validations
             */
            $defaultInputs = [
                'email' => null,
                'password' => null,
                'location' => array()
            ];

            $rules = array(
                'email' => 'required|email',
                'password' => 'required|min:6',
                'location.0' => 'numeric',
                'location.1' => 'numeric'
            );

            $inputs = validateInput($defaultInputs, $rules);
            if (isset($inputs['success']) && $inputs['success'] === false) {
                return $inputs;
            }
            $inputs = $inputs['data'];
            
            // Set Mongo Client
            if (false == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
                $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
            }

            /**
             * get user details from email and password
             */
            $conditions = array(
                array('email', '=', $inputs['email']),
                array('password', '=', $inputs['password']),
                array('is_active', '=', true),
                array('is_deleted', '=', false)
            );
            $userInfo = \SEC\Models\Mongo\User::getUser($conditions);
            if (empty($userInfo) || (isset($userInfo['success']) && $userInfo['success'] == false)) {
                return $userInfo;
            }

            $userInfo = $userInfo['data'];
            
            if(empty($userInfo)){
                return \ApplicationBase\Facades\Api::error(1020, array(), array('email or password'));
            }

            /**
             * Create session for user
             */
            $sessionInfo= array(
                'user_id' => $userInfo['_id'],
                'token_alive_untill' => 0
            );
            $sessionInfo = \SEC\Models\Mongo\Session::saveSession($sessionInfo);
            if (empty($sessionInfo) || (isset($sessionInfo['success']) && $sessionInfo['success'] == false)) {
                return $sessionInfo;
            }
            $sessionInfo = $sessionInfo['data'];

            /**
             * Update user location and last login time
             */
            $userDetails= array(
                'user_id' => $userInfo['_id'],
                'last_located_at' => $inputs['location'],
                'last_login_at' => LARAVEL_START,
                'last_used_at' => LARAVEL_START
            );
            $userInfo = \SEC\Models\Mongo\User::saveUser($userDetails);
            if (empty($userInfo) || (isset($userInfo['success']) && $userInfo['success'] == false)) {
                return $userInfo;
            }
            $userInfo = $userInfo['data'];

            /**
             * Commit Mongo Transactions
             */
            \SEC\Models\Mongo\Common::commitMongoTransactions();
            $output = array('token' => $sessionInfo['_id']->{'$id'});
            return \ApplicationBase\Facades\Api::success(2010, $output, array());
        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}