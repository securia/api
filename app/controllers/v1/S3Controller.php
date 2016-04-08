<?php

namespace v4;

/**
 * Class S3Controller
 * @package v4
 */
class S3Controller extends \BaseController
{
    /**
     * Get S3 token
     */
    public function sessionToken()
    {
        try {
            //Create Temporary session token
            global $globalConfig, $appConn;

            /**
             * Input validations
             */
            $defaultInputs = array(
                'token' => null
            );

            $rules = array(
                'token' => 'required'
            );

            $inputs = validateInput($defaultInputs, $rules);

            if (isset($inputs['success']) && $inputs['success'] === false) {
                return $inputs;
            } else {
                $inputs = $inputs['data'];
            }

            // Set Mongo Client
            if (false == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
                $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
            }

            /**
             * Get Device Info
             */
            try {
                $token = new \MongoId($inputs['token']);
            } catch (\Exception $e) {
                return \ApplicationBase\Facades\Api::error(3070, array(), array());
            }

            $conditions = array(array('_id', '=', $token));
            $deviceInfo = getTokenInfo($conditions);
            if (!isset($deviceInfo['success']) || $deviceInfo['success'] == false) {
                return $deviceInfo;
            }
            $deviceInfo = $deviceInfo['data'];
            $appConn['platform'] = $deviceInfo['platform'];

            //Create Instance of STS Client
            $sts = \Aws\Sts\StsClient::factory(array(
                'key' => $globalConfig['diag']['key'],
                'secret' => $globalConfig['diag']['secret'],
            ));

            //Fetch session token and Temporary Access Key
            $credentials = $sts->getSessionToken()->get('Credentials');

            //check credentials created
            if (isset($credentials['SessionToken']) && isset($credentials['SecretAccessKey']) && isset($credentials['AccessKeyId'])) {
                $return = array(
                    'access_key_id' => $credentials['AccessKeyId'],
                    'secret_access_key' => $credentials['SecretAccessKey'],
                    'session_token' => $credentials['SessionToken'],
                    'expiration' => isset($credentials['Expiration']) ? $credentials['Expiration'] : 0,
                    'bucket' => $globalConfig['diag']['bucket'],
                    'device_id' => $deviceInfo['device_id']
                );
                return \ApplicationBase\Facades\Api::success(2040, $return, 'Credentials');
            }
            return \ApplicationBase\Facades\Api::error(1120, array(), 'Credentials');

        } catch (\Exception $e) {
            die(exception($e));
        }
    }
}
