<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase {

    /**
     * Creates the application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication() {
        $unitTesting = true;

        $testEnvironment = 'testing';

        return require __DIR__ . '/../../bootstrap/start.php';
    }

    public function getMessage($code, $data = array()) {
        if (!is_array($data)) {
            $temp = array();
            $data = $temp[] = $data;
        }
        return vsprintf(\Illuminate\Support\Facades\Lang::get('api.' . $code), $data);
    }

    public function response() {
        return json_decode($this->client->getResponse()->getContent(), true);
    }

    public function messageEquals($code, $data, $response = null) {
        if($response == null) {
            $response = $this->response();
        }
        $this->assertEquals($this->getMessage($code, $data), $response['message']['description']);
    }

    public function successCodeEquals($code, $response = null) {
        if($response == null) {
            $response = $this->response();
        }
        $this->assertEquals($code, $response['message']['id']);
    }

    public function responseHasArrayKey($key, $response = null) {
        if($response == null){
            $response = $this->response();
        }
        $this->assertTrue($this->findKey($key, $response));
    }

    public function responseArrayKeyHasValue($key, $value, $response = null) {
        if($response == null){
            $response = $this->response();
        }
        $this->assertTrue($this->compareKeyValue($key, $value, $response));
    }

    public function findKey($key, $array) {
        if (is_array($array)) {
            if (array_key_exists($key, $array)) {
                return true;
            }

            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    $found = $this->findKey($key, $v);
                    if ($found) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function compareKeyValue($key, $value, $array) {
        $keys = explode(".",$key);
        while ($key = array_shift($keys)) {
            $array = &$array[$key];
        }
        return strstr($array, $value) ? true : false;
    }

}
