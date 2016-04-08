<?php

namespace admin;

/**
 * Class AuthController
 * @package admin
 */
class AuthController extends \BaseController
{
    /**
     * @return string
     */
    public function login()
    {

        try {
            $defaultInputs = array(
                'username' => null,
                'password' => null,
                'remember_me' => false
            );

            //validation rule
            $rules = array(
                'username' => 'required|min:2|max:50',
                'password' => 'required|min:6|max:20',
            );

            // Validate Input Parameters @var  $inputs
            $inputs = validateInput($defaultInputs, $rules);

            //Check validate input function response if false then return
            if (!isset($inputs['success']) || $inputs['success'] == false) {
                return json_encode($inputs);
            }
            $inputs = $inputs['data'];

            //Get User Information from Mongo DB
            $arrUser = \PSC\Models\Mongo\Admin::where('is_deleted', '<>', true)->orWhereNull('is_deleted')->where('username', strtolower($inputs['username']))->orWhere('email', strtolower($inputs['username']))->first();

            //if user not found in mongo
            if (false == valObj($arrUser, '\PSC\Models\Mongo\Admin')) {
                return \ApplicationBase\Facades\Api::error(1020, [], [' Username or password']);
            }

            //Compare Users Password with input password
            if (\Illuminate\Support\Facades\Hash::check($inputs['password'], $arrUser['password']) == false) {
                return \ApplicationBase\Facades\Api::error(1020, [], [' Username or password']);
            }

            $response = $this->storeUserLoginInfo($inputs, $arrUser);

            return $response;

        } catch (\Exception $e) {
            return exception($e);
        }
    }

    /**
     * @return string
     */
    public function logout()
    {
        try {

            $defaultInputs = array(
                'token' => null
            );

            //Set Validation Rules
            $rules = array(
                'token' => 'required'
            );

            //Validate Input Parameters @var  $inputs
            $inputs = validateInput($defaultInputs, $rules);

            //Check validate input function response if false then return
            if (!isset($inputs['success']) || $inputs['success'] == false) {
                return json_encode($inputs);
            }
            $inputs = $inputs['data'];

            //get user by token
            $arrUser = getAdminUserByToken($inputs['token'], true);

            //Check status
            if (valArr($arrUser)) {
                //Set mongoDB connection
                $status = \PSC\Models\Mongo\AdminSession::where('_id', '=', $arrUser['_id'])->delete();
                //check status of object
                if (!$status) {
                    return \ApplicationBase\Facades\Api::error(1050, [], ['User logged out']);
                }

            }

            return \ApplicationBase\Facades\Api::success(2020, $inputs['token'], ['Token']);
        } catch (\Exception $e) {
            return exception($e);
        }
    }


    /**
     * Function to send reset password link to user
     * @return string
     */

    public function forgotPassword()
    {
        try {

            global $appConn, $appConfig;
            $defaultInputs = array(
                'email' => null
            );

            //Validation Rules @var  $rules
            $rules = array(
                'email' => 'required|email
                 ');

            //Validate Input Parameters @var  $inputs
            $inputs = validateInput($defaultInputs, $rules);

            //Check validate input function response if false then return
            if (!isset($inputs['success']) || $inputs['success'] == false) {
                return json_encode($inputs);
            }
            $inputs = $inputs['data'];

            // Set Mongo Client
            if (false == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
                $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
            }

            $objAdmin = null;
            //check this user with provided Email is exist or not
            $objAdmin = \PSC\Models\Mongo\Admin::where('is_deleted', '<>', true)
                ->orWhereNull('is_deleted')->where('email', strtolower($inputs['email']))->first();

            if (false == valObj($objAdmin, 'PSC\Models\Mongo\Admin')) {
                return \ApplicationBase\Facades\Api::error(1100, [], ['Admin']);
            }

            $arrAdmin = $objAdmin->toarray();

            //Get One random string as a password
            $newPassword = generateRandomString($appConfig['user']['resetPasswordLength']);

            //Update Admin Password into the database
            $hashPassword = \Illuminate\Support\Facades\Hash::make($newPassword);

            //Updated users password into mongoDB
            $boolStatus = \PSC\Models\Mongo\Admin::where('id', $arrAdmin['id'])->update(array('password' => $hashPassword));

            if (false == $boolStatus) {
                return \ApplicationBase\Facades\Api::error(1100, array(), 'Forgot Password');
            }

            $dataPoints = array('username' => $arrAdmin['username'], 'password' => $newPassword);
            $template = \Illuminate\Support\Facades\View::make('EmailTemplate-PASSWORD_RESET', $dataPoints)->render();
            $subject = $appConfig['emailNotifications']['PASSWORD_RESET']['subject'];

            $sendEmailData = array(
                'to_email' => $arrAdmin['email'],
                'subject' => $subject,
                'content' => $template,
                'type' => 'PASSWORD_RESET',
                'from_email' => $appConfig['emails']['defaultFromEmail'],
                'reply_to_email' => $appConfig['emails']['defaultFromEmail'],
            );

            addToEmailQueue($sendEmailData);

            return \ApplicationBase\Facades\Api::success(4000, '', ['The instructions to recover your password has been sent to your email.']);

        } catch (\Exception $e) {
            return exception($e);
        }
    }

    public function verifyToken()
    {
        try {
            global $appConn;
            $defaultInputs = array(
                'token' => null
            );

            //Validation Rules @var  $rules
            $rules = array(
                'token' => 'required'
            );

            //Validate Input Parameters @var  $inputs
            $inputs = validateInput($defaultInputs, $rules);

            //Check validate input function response if false then return
            if (!isset($inputs['success']) || $inputs['success'] == false) {
                return json_encode($inputs);
            }
            $inputs = $inputs['data'];

            // Set Mongo Client
            if (false == valObj($appConn['mongo'], 'Jenssegers\Mongodb\Connection')) {
                $appConn['mongo'] = \Illuminate\Support\Facades\DB::connection('mongodb');
            }

            //Fetch user from mongoDB
            $objToken = \PSC\Models\Mongo\AdminSession::where('_id', $inputs['token'])->first();

            if (false == valObj($objToken, 'PSC\Models\Mongo\AdminSession')) {
                return \ApplicationBase\Facades\Api::error(1180, [], []);
            }

            return \ApplicationBase\Facades\Api::success(2070, [], ['User']);

        } catch (\Exception $e) {
            return exception($e);
        }
    }

    /**
     * @param array $inputs
     * @param array $user
     * @return mixed
     */
    public function storeUserLoginInfo($inputs = array(), $user = array())
    {
        try {
            global $appConfig;
            // Add user Token details into MONGO
            $userToken['id'] = $user['id'];

            $userToken['created_at'] = microtime(true);

            // Saving remember me in mongo
            if (!isset($inputs['remember_me']))
                $inputs['remember_me'] = false;

            $userToken['remember_me'] = $inputs['remember_me'];
            $userToken['username'] = $user['username'];
            $userToken['email'] = $user['email'];
            $userToken['password'] = $user['password'];

            //check remember_me is true or not
            $userToken['expiry_time'] = ($inputs['remember_me']) ? strtotime($appConfig['user']['rememberMeExpiration']) : strtotime($appConfig['user']['normalExpiration']);

            //Insert Users token and token_expire time in mongo db
            $userToken['_id'] = new \MongoId();
            $status = \PSC\Models\Mongo\AdminSession::insert($userToken);

            //check status of object
            if (!$status) {
                return \ApplicationBase\Facades\Api::error(1050, [], ['User logged in']);
            }
            $intDisputeCount = \PSC\Models\Mongo\Dispute::where('is_resolved', false)->count();

            $response['token'] = $userToken['_id']->{'$id'};
            $response['id'] = $user['id'];
            $response['username'] = $user['username'];
            $response['email'] = $user['email'];
            $response['disputes_count'] = $intDisputeCount;
            $response['permissions'] = array();

            return \ApplicationBase\Facades\Api::success(2010, $response, ['User']);

        } catch (\Exception $e) {
            return exception($e);
        }
    }

}