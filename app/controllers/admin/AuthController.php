<?php

namespace admin;

/**
 * Class AuthController
 * @package admin
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
                'password' => null
            ];

            $rules = array(
                'email' => 'required|email',
                'password' => 'required|min:6'
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
                array('type', '=', 'admin'),
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

            if(!\Illuminate\Support\Facades\Hash::check($inputs['password'], $userInfo['password'])){
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
             * Commit Mongo Transactions
             */
            \SEC\Models\Mongo\Common::commitMongoTransactions();
            $output = array(
                'token' => $sessionInfo['_id']->{'$id'},
                'name' => $userInfo['name'],
                'email' => $userInfo['email'],
            );
            return \ApplicationBase\Facades\Api::success(2010, $output, array());
        } catch (\Exception $e) {
            die(exception($e));
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
}