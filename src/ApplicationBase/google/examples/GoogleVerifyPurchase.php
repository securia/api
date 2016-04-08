<?php
namespace ApplicationBase\google;
require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

class GoogleVerifyPurchase
{
    private $clientId = '';
    private $serviceAccountName = '';
    private $keyFilePath = '';
    private $scopes = array();
    private $packageName = '';
    private $productId = '';
    private $token = '';
    private $client = null;

    public function __construct()
    {

    }

    public function getGoogleClient($clientId, $serviceAccountName, $keyFileName, $scopes, $applicationName)
    {
        try {
            $this->clientId = $clientId;
            $this->serviceAccountName = $serviceAccountName;
            $this->keyFilePath = $keyFileName;
            $this->scopes = $scopes;
            if (strpos($clientId, "googleusercontent") == false || !strlen($serviceAccountName) || !strlen($keyFileName)) {
                return \ApplicationBase\Facades\Api::error(5020, array(), array('parameters'));
            }

            /**
             * Create google client object
             */
            $this->client = new \Google_Client();

            /**
             * Create credential object and assert it in $client object
             */
            $cred = new \Google_Auth_AssertionCredentials(
                $serviceAccountName,
                $scopes,
                file_get_contents($keyFileName)
            );

            $this->client->setAssertionCredentials($cred);
            $this->client->setApplicationName($applicationName);
            return $this->client;
        } catch (\Exception $e) {
            die(exception($e));
        }
    }

    /**
     * Valid response is as below
     * Google_Service_AndroidPublisher_ProductPurchase Object
     * (
     * [internal_gapi_mappings:protected] => Array
     * (
     * )
     *
     * [consumptionState] => 1
     * [developerPayload] =>
     * [kind] => androidpublisher#productPurchase
     * [purchaseState] => 0
     * [purchaseTimeMillis] => 1435972708239
     * [modelData:protected] => Array
     * (
     * )
     *
     * [processed:protected] => Array
     * (
     * )
     *
     * )
     */

    public function getPurchaseProductStatus($packageName, $productId, $token)
    {
        try {
            $this->packageName = $packageName;
            $this->productId = $productId;
            $this->token = $token;

            /**
             * Call androidpublisher.purchases.products.get API
             * If response contains consumptionState then that is valid purchase
             * If response is throwing any error means purchase is invalid
             */

            $service = new \Google_Service_AndroidPublisher($this->client);
            try {
                $response = $service->purchases_products->get($packageName, $productId, $token);
            } catch (\Exception $e) {
                return \ApplicationBase\Facades\Api::error(3060, array(), array('Purchase'));
            }

            if (isset($response->consumptionState)) {
                return \ApplicationBase\Facades\Api::success(2220, array(), array('Purchase'));
            }
        } catch (\Exception $e) {
            die(exception($e));
        }
    }


}