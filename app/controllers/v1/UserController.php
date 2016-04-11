<?php

namespace v1;

/**
 * Class UserController
 * @package v1
 */
class UserController extends \BaseController
{
    /**
     * Record doctor call in database
     */
    public function getMyDoctors()
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
                'token' => null
            ];

            $rules = array(
                'token' => 'required|alpha_num'
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

            $doctorsInfo = \SEC\Models\Mongo\User::getMyDoctors($tokenInfo['user_id']);
            if (empty($doctorsInfo) || (isset($doctorsInfo['success']) && $doctorsInfo['success'] == false)) {
                return $doctorsInfo;
            }
            $doctorsInfo = $doctorsInfo['data'];

            foreach($doctorsInfo as $key => $doctors){
                $doctorsInfo[$key] = unsetKeys($doctorsInfo[$key], array('created_at', 'updated_at'));
                $doctorsInfo[$key]['_id'] = $doctorsInfo[$key]['_id']->{'$id'};
            }

            $output = array(
                'doctors' => $doctorsInfo
            );
            return \ApplicationBase\Facades\Api::success(2040, $output, array('Doctors'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Get my calls from database
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
                'from_date' => (int)date('Ymd', time()),
                'to_date' => (int)date('Ymd', time()),
                'doctor_id' => null
            ];

            $rules = array(
                'token' => 'required|alpha_num',
                'from_date' => 'required|numeric',
                'to_date' => 'required|numeric',
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
                array('call_time', '=', array('$gte' => $inputs['from_date'], '$lte' => $inputs['to_date']))
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

            $doctorIds = array();
            foreach($callsInfo as $key => $call){
                $doctorIds[] = $call['doctor_id'];
            }

            $doctorsInfo = $appConn['mongo']->collection('doctors')->where('_id', '=', array('$in' =>$doctorIds))->get();
            foreach($doctorsInfo as $key => $doctor){
                $doctorsInfo[$key]['_id'] = $doctorsInfo[$key]['_id']->{'$id'};
                $doctorsInfo[$key] = unsetKeys($doctorsInfo[$key], array('created_at', 'updated_at'));
            }
            foreach($callsInfo as $key => $call){
                foreach($doctorsInfo as $doctor){
                    if($call['doctor_id'] == $doctor['_id']){
                        $callsInfo[$key]['doctor'] = $doctor;
                        unset($callsInfo[$key]['doctor_id']);
                        break;
                    }
                }
                $callsInfo[$key]['_id'] = $callsInfo[$key]['_id']->{'$id'};
                $callsInfo[$key]['user_id'] = $callsInfo[$key]['user_id']->{'$id'};
                $callsInfo[$key] = unsetKeys($callsInfo[$key], array('created_at', 'updated_at'));
            }

            $output = array(
                'calls' => $callsInfo
            );
            return \ApplicationBase\Facades\Api::success(2040, $output, array('Calls'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Change password of user
     */
    public function changePassword()
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
                'old_password' => null,
                'new_password' => null
            ];

            $rules = array(
                'token' => 'required|alpha_num',
                'old_password' => 'required|min:6',
                'new_password' => 'required|min:6'
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
             * get user details from email and password
             */
            $conditions = array(
                array('_id', '=', $tokenInfo['user_id']),
                array('is_active', '=', true),
                array('is_deleted', '=', false)
            );
            $userInfo = \SEC\Models\Mongo\User::getUser($conditions);
            if (empty($userInfo) || (isset($userInfo['success']) && $userInfo['success'] == false)) {
                return $userInfo;
            }

            $userInfo = $userInfo['data'];

            if(empty($userInfo)){
                return \ApplicationBase\Facades\Api::error(5040, array(), array('User'));
            }

            if(!\Illuminate\Support\Facades\Hash::check($inputs['old_password'], $userInfo['password'])){
                return \ApplicationBase\Facades\Api::error(1020, array(), array('old password'));
            }

            /**
             * Update user location and last login time
             */
            $userDetails= array(
                'user_id' => $userInfo['_id'],
                'password' => \Illuminate\Support\Facades\Hash::make($inputs['new_password'])
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

            return \ApplicationBase\Facades\Api::success(2050, array(), array('Password'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}