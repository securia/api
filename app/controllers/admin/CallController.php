<?php

namespace admin;

/**
 * Class CallController
 * @package v1
 */
class CallController extends \BaseController
{
    public function get()
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
                'user_id' => null,
                'from_date' => (int)date('Ymd', time()),
                'to_date' => (int)date('Ymd', time()),
                'doctor_id' => null
            ];

            $rules = array(
                'token' => 'required|alpha_num',
                'doctor_id' => 'required|alpha_num',
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
                $inputs['user_id'] = new \MongoId($inputs['user_id']);
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
                array('user_id', '=', $inputs['user_id']),
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

    public function getReports()
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
                'user_id' => null,
                'from_date' => date('Y-m-d', time()),
                'to_date' => date('Y-m-d', time()),
                'doctor_id' => null
            ];

            $rules = array(
                'doctor_id' => 'required|alpha_num',
                'from_date' => 'required|date|date_format:Y-m-d',
                'to_date' => 'required|date|date_format:Y-m-d',
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
                if(!empty($inputs['user_id'])){
                    $inputs['user_id'] = new \MongoId($inputs['user_id']);
                }
            }catch (\Exception $e){
                return \ApplicationBase\Facades\Api::error(5020, array(), array('user id'));
            }
            
            /**
             * get doctor's calls between specified date range
             */
            $conditions = array();
            if(!empty($inputs['user_id'])){
                $conditions[] = array('user_id', '=', $inputs['user_id']);
            }
            $conditions[] = array('call_time', '=', array('$gte' => $inputs['from_date'], '$lte' => $inputs['to_date']));
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

            $userIds = array();
            foreach($callsInfo as $key => $call){
                $userIds[] = $call['user_id'];
            }

            $usersById= array();
            $userInfo = $appConn['mongo']->collection('users')->where('_id', '=', array('$in' =>$userIds))->get(array('name'));

            foreach($userInfo as $key => $user){
                $user['_id'] = $user['_id']->{'$id'};
                $usersById[$user['_id']] = $user;
                unset($userInfo[$key]);
            }
            foreach($callsInfo as $key => $call){
                $callsInfo[$key]['user_id'] = $callsInfo[$key]['_id']->{'$id'};
                $callsInfo[$key]['user_name'] = isset($usersById[$callsInfo[$key]['user_id']]) ? $usersById[$callsInfo[$key]['user_id']]['name'] : '-';
                $callsInfo[$key] = unsetKeys($callsInfo[$key], array('updated_at'));
            }

            $output = array(
                'calls' => $callsInfo
            );
            return \ApplicationBase\Facades\Api::success(2040, $output, array('Calls'));
        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}
