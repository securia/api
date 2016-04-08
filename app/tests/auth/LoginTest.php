<?php

class LoginTest extends TestCase {

    public function testEmptyUsername() {
        global $appConfig;
        $inputs = array('username' => '', 'password' => '');
        $this->client->request('POST', '/app/' . $appConfig["currentVersion"] . '/auth/testLogin', $inputs);

        $this->messageEquals(1000, array('The username field is required.'));
    }

    public function testEmptyPassword() {
        global $appConfig;
        $inputs = array('username' => 'admin', 'password' => '');
        $this->client->request('POST', '/app/' . $appConfig["currentVersion"] . '/auth/testLogin', $inputs);

        $this->messageEquals(1000, array('The password field is required.'));
    }

    public function testInvalidCredentials() {
        global $appConfig;
        $inputs = array('username' => 'admin', 'password' => '12345');
        $this->client->request('POST', '/app/' . $appConfig["currentVersion"] . '/auth/testLogin', $inputs);

        $this->messageEquals(1020, array('Credentials'));
    }

    public function testValidCredentials() {
        global $appConfig;
        $inputs = array('username' => 'admin', 'password' => 'admin');
        $this->client->request('POST', '/app/' . $appConfig["currentVersion"] . '/auth/testLogin', $inputs);

        $this->messageEquals(2010, array());
        $this->responseHasArrayKey('token');
        $this->responseArrayKeyHasValue('token', 'isljshsahsdfhslsdnfnsdf');
    }

}