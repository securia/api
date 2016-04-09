<?php

namespace admin;

/**
 * Class UserController
 * @package v1
 */
class UserController extends \BaseController
{
    var $mongoColumns = array(
        'name' => array('display_name' => 'User Name', 'db_name' => 'name', 'data_type' => 'string', 'show' => true, 'sort' => true, 'search' => true),
        'position' => array('display_name' => 'Position', 'db_name' => 'position', 'data_type' => 'string', 'show' => true, 'sort' => true, 'search' => true),
        'contact_number' => array('display_name' => 'Contact Number', 'db_name' => 'contact_number', 'data_type' => 'string', 'show' => true, 'sort' => false, 'search' => true),
        'email' => array('display_name' => 'Email', 'db_name' => 'email', 'data_type' => 'string', 'show' => true, 'sort' => false, 'search' => true),
        'is_active' => array('display_name' => 'Active', 'db_name' => 'is_active', 'data_type' => 'boolean', 'show' => true, 'sort' => true, 'search' => false),
        'is_deleted' => array('display_name' => 'Deleted', 'db_name' => 'is_deleted', 'data_type' => 'boolean', 'show' => true, 'sort' => true, 'search' => false),
        'last_located_at' => array('display_name' => 'Last Location', 'db_name' => 'last_located_at', 'data_type' => 'string', 'show' => true, 'sort' => false, 'search' => false),
        'last_located_address' => array('display_name' => 'Last Location Address', 'db_name' => 'last_located_address', 'data_type' => 'string', 'show' => true, 'sort' => false, 'search' => false),
        'last_login_at' => array('display_name' => 'Inactivity', 'db_name' => 'last_login_at', 'data_type' => 'double', 'show' => true, 'sort' => true, 'search' => false)
    );

    public function getAll()
    {
        global $appConn;
        try {

            global $appConfig;
            $defaultInputs = array(
                'token' => null,
                'per_page' => $appConfig['grid']['perPageDefaultValue'],
                'page' => $appConfig['grid']['pageDefaultValue'],
                'sort' => array(),
                'search_by' => array(),
                'search_value' => null
            );

            $rules = array(
                'token' => 'required|string',
                'per_page' => 'required|integer',
                'page' => 'required|integer',
                'sort' => 'array',
                'search_by' => 'array'
            );

            //Process input and apply validation
            $inputs = validateInput($defaultInputs, $rules, true);

            //Check validate input function response if false then return
            if (!isset($inputs['success']) || $inputs['success'] == false) {
                return json_encode($inputs);
            }
            $inputs = $inputs['data'];

            // Set Mongo Client
            if (false == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
                $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
            }

            /**
             * get session details from token
             */
            try{
                $inputs['token'] = new \MongoId($inputs['token']);
            }catch (\Exception $e){
                return \ApplicationBase\Facades\Api::error(5020, array(), array('token'));
            }
            $tokenInfo = getTokenInfo(array(array('_id', '=', $inputs['token'])));
            if (empty($tokenInfo) || (isset($tokenInfo['success']) && $tokenInfo['success'] == false)) {
                return $tokenInfo;
            }
            $tokenInfo = $tokenInfo['data'];

            if (false == valArr($inputs['sort'])) {
                $inputs['sort'] = array(
                    array("sort_by" => "created_at", "order_by" => "desc"),
                );
            }
            $output = \SEC\Models\Mongo\User::processGrid($inputs, $this->mongoColumns);

            return \ApplicationBase\Facades\Api::success(2040, $output, ['Users']);
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    public function get()
    {
        global $appConn;
        try {

            $defaultInputs = array(
                'token' => null,
                'user_id' => null
            );

            $rules = array(
                'token' => 'required|alpha_num',
                'user_id' => 'required|alpha_num'
            );

            //Process input and apply validation
            $inputs = validateInput($defaultInputs, $rules, true);

            //Check validate input function response if false then return
            if (!isset($inputs['success']) || $inputs['success'] == false) {
                return json_encode($inputs);
            }
            $inputs = $inputs['data'];

            // Set Mongo Client
            if (false == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
                $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
            }

            /**
             * get session details from token
             */
            try{
                $inputs['token'] = new \MongoId($inputs['token']);
                $inputs['user_id'] = new \MongoId($inputs['user_id']);
            }catch (\Exception $e){
                return \ApplicationBase\Facades\Api::error(5020, array(), array('token'));
            }
            $tokenInfo = getTokenInfo(array(array('_id', '=', $inputs['token'])));
            if (empty($tokenInfo) || (isset($tokenInfo['success']) && $tokenInfo['success'] == false)) {
                return $tokenInfo;
            }
            $tokenInfo = $tokenInfo['data'];

            $where = array(
                array('_id', '=', $inputs['user_id'])
            );
            $userInfo = \SEC\Models\Mongo\User::getUser($where);
            if (empty($userInfo) || (isset($userInfo['success']) && $userInfo['success'] == false)) {
                return $userInfo;
            }
            $userInfo = $userInfo['data'];
            $userInfo['_id'] = $userInfo['_id']->{'$id'};
            $userInfo = unsetKeys($userInfo, array('created_at', 'updated_at', 'password'));

            return \ApplicationBase\Facades\Api::success(2040, $userInfo, ['User']);
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    public function save()
    {
        global $appConn;
        try {

            $defaultInputs = array(
                'token' => null,
                'user_id' => null,
                'name' => null,
                'position' => null,
                'contact_number' => null,
                'email' => null,
                'password' => null,
                'type' => null
            );

            $rules = array(
                'token' => 'required|alpha_num',
                'user_id' => 'alpha_num',
                'name' => 'required|string',
                'position' => 'required|string',
                'contact_number' => 'required|alpha_num',
                'email' => 'required|email',
                'password' => 'required|min:6',
                'type' => 'required|in:admin,employee',
            );

            //Process input and apply validation
            $inputs = validateInput($defaultInputs, $rules, true);

            //Check validate input function response if false then return
            if (!isset($inputs['success']) || $inputs['success'] == false) {
                return json_encode($inputs);
            }
            $inputs = $inputs['data'];

            // Set Mongo Client
            if (false == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
                $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
            }

            /**
             * get session details from token
             */
            try{
                $inputs['token'] = new \MongoId($inputs['token']);
                if(!empty($inputs['user_id'])){
                    $inputs['user_id'] = new \MongoId($inputs['user_id']);
                }
            }catch (\Exception $e){
                return \ApplicationBase\Facades\Api::error(5020, array(), array('token'));
            }
            $tokenInfo = getTokenInfo(array(array('_id', '=', $inputs['token'])));
            if (empty($tokenInfo) || (isset($tokenInfo['success']) && $tokenInfo['success'] == false)) {
                return $tokenInfo;
            }
            $tokenInfo = $tokenInfo['data'];

            $conditions = array(
                array('email', '=', $inputs['email']),
                array('is_active', '=', true),
                array('is_deleted', '=', false)
            );
            $userInfo = \SEC\Models\Mongo\User::getUser($conditions);
            if (!empty($userInfo['data'])) {
                return \ApplicationBase\Facades\Api::error(1090, array(), array('User'));
            }

            $data = array(
                'user_id' => $inputs['user_id'],
                'name' => $inputs['name'],
                'position' => $inputs['position'],
                'contact_number' => $inputs['contact_number'],
                'email' => $inputs['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($inputs['password']),
                'type' => $inputs['type'],
                'is_active' => true,
                'is_deleted' => false,
                'last_located_at' => array(),
                'last_located_address' => null,
                'last_login_at' => null
            );

            $userInfo = \SEC\Models\Mongo\User::saveUser($data);
            if (empty($userInfo) || (isset($userInfo['success']) && $userInfo['success'] == false)) {
                return $userInfo;
            }
            $userInfo = $userInfo['data'];

            /**
             * Commit Mongo Transactions
             */
            \SEC\Models\Mongo\Common::commitMongoTransactions();

            return \ApplicationBase\Facades\Api::success(2030, array(), ['User']);
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    public function delete()
    {
        global $appConn;
        try {

            $defaultInputs = array(
                'token' => null,
                'user_id' => null
            );

            $rules = array(
                'token' => 'required|alpha_num',
                'user_id' => 'required|alpha_num'
            );

            //Process input and apply validation
            $inputs = validateInput($defaultInputs, $rules, true);

            //Check validate input function response if false then return
            if (!isset($inputs['success']) || $inputs['success'] == false) {
                return json_encode($inputs);
            }
            $inputs = $inputs['data'];

            // Set Mongo Client
            if (false == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
                $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
            }

            /**
             * get session details from token
             */
            try{
                $inputs['token'] = new \MongoId($inputs['token']);
                $inputs['user_id'] = new \MongoId($inputs['user_id']);
            }catch (\Exception $e){
                return \ApplicationBase\Facades\Api::error(5020, array(), array('token'));
            }
            $tokenInfo = getTokenInfo(array(array('_id', '=', $inputs['token'])));
            if (empty($tokenInfo) || (isset($tokenInfo['success']) && $tokenInfo['success'] == false)) {
                return $tokenInfo;
            }
            $tokenInfo = $tokenInfo['data'];

            $where = array(
                array('_id', '=', $inputs['user_id'])
            );
            $userInfo = \SEC\Models\Mongo\User::deleteUser($where);

            return \ApplicationBase\Facades\Api::success(2060, array(), ['User']);
        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}