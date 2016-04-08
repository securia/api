<?php

namespace v1;

/**
 * Class CallController
 * @package v1
 */
class CallController extends \BaseController
{
    /**
     * Record doctor call in database
     */
    public function save()
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
                'token' => null,
                'doctor_id' => null,
                'call_time' => date('M d, Y h:i:s A', time()),
                'comment' => null,
                'location' => array(),
                'location_address' => null,

            ];

            $rules = array(
                'token' => 'required|alpha_num',
                'doctor_id' => 'required|alpha_num',
                'call_time' => 'required',
                'comment' => 'alpha_num',
                'location.0' => 'required|numeric',
                'location.1' => 'required|numeric'
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
            
            
            /**
             * Create doctor call
             */
            try{
                $inputs['doctor_id'] = new \MongoId($inputs['doctor_id']);
            }catch (\Exception $e){
                return \ApplicationBase\Facades\Api::error(5020, array(), array('token'));
            }
            $callInfo= array(
                'user_id' => $tokenInfo['user_id'],
                'doctor_id' => $inputs['doctor_id'],
                'call_time' => $inputs['call_time'],
                'comment' => $inputs['comment'],
                'location' => $inputs['location'],
                'location_address' => $inputs['location_address'],
            );
            $callInfo = \SEC\Models\Mongo\Call::saveCall($callInfo);
            if (empty($callInfo) || (isset($callInfo['success']) && $callInfo['success'] == false)) {
                return $callInfo;
            }
            $callInfo = $callInfo['data'];

            /**
             * Update user location and last login time
             */
            $userDetails= array(
                'user_id' => $tokenInfo['user_id'],
                'last_located_at' => $inputs['location'],
                'last_located_address' => $inputs['location_address'],
                'last_used_at' => LARAVEL_START
            );
            \SEC\Models\Mongo\User::saveUser($userDetails);

            /**
             * Commit Mongo Transactions
             */
            \SEC\Models\Mongo\Common::commitMongoTransactions();
            return \ApplicationBase\Facades\Api::success(2110, array(), array('Call'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Record doctor call in database
     */
    public function getMyCalls()
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
                'token' => null,
                'from_date' => date('Ymd', time()),
                'to_date' => date('Ymd', time()),
                'doctor_id' => null
            ];

            $rules = array(
                'token' => 'required|alpha_num',
                'from_date' => 'required|alpha_num',
                'to_date' => 'required|alpha_num',
                'doctor_id' => 'alpha_num'
            );

            $inputs = validateInput($defaultInputs, $rules);
            if (isset($inputs['success']) && $inputs['success'] === false) {
                return $inputs;
            }
            $inputs = $inputs['data'];

            $inputs['from_date'] = (int) strtotime(date('Y-m-d 00:00:00', strtotime($inputs['from_date'])));
            $inputs['to_date'] = (int) strtotime(date('Y-m-d 23:59:59', strtotime($inputs['to_date'])));

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

            /**
             * get doctor's calls between specified date range
             */
            $conditions = array(
                array('user_id', '=', $tokenInfo['user_id']),
                array('created_at', '=', array('$gte' => $inputs['from_date'], '$lte' => $inputs['to_date']))
            );
            if(!empty($inputs['doctor_id'])){
                try{
                    $inputs['doctor_id'] = new \MongoId($inputs['doctor_id']);
                }catch (\Exception $e){
                    return \ApplicationBase\Facades\Api::error(5020, array(), array('token'));
                }
                $conditions[] = array('doctor_id', '=', $inputs['doctor_id']);
            }

            $callsInfo = \SEC\Models\Mongo\Call::getCalls($conditions);
            if (empty($callsInfo) || (isset($callsInfo['success']) && $callsInfo['success'] == false)) {
                return $callsInfo;
            }
            $callsInfo = $callsInfo['data'];

            return \ApplicationBase\Facades\Api::success(2110, array(), array('Call'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}
