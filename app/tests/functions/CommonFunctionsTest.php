<?php

class CommonFunctionsTest extends TestCase
{

    public function testException()
    {
        global $globalConfig;

        /**
         * invalid exception object checking
         */
        $e = "";
        $status = exception($e);
        $this->responseArrayKeyHasValue('message.description', 'Functional Exception', json_decode($status, true));

        /**
         * proper exception checking
         */
        $globalConfig['isDebugMode'] = false;
        $exceptionText = 'Test Exception';
        try {
            throw new \Exception($exceptionText);
        } catch (\Exception $e) {
            $status = exception($e);
            $this->responseArrayKeyHasValue('message.description', $exceptionText, json_decode($status, true));
        }

        /**
         * check debug mode is providing TRACE
         */
        $globalConfig['isDebugMode'] = true;
        try {
            throw new \Exception('Test Exception');
        } catch (\Exception $e) {
            $status = exception($e);
            $this->responseArrayKeyHasValue('message.description', 'TRACE::', json_decode($status, true));
        }
    }

    public function testUnsetKeys()
    {

        $input = array(
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
            'key4' => 'value4',
            'key5' => array(
                'key51'=> 'value51',
                'key52'=> 'value52',
                'key53'=> 'value53'
            )
        );
        /**
         * check whether same array is in response if empty array sent in input
         */
        $input1= $input;
        $output = unsetKeys($input1, array());
        $this->assertEquals($output, $input1);

        /**
         * check whether first level key exist after unset
         */
        $input1= $input;
        $output = unsetKeys($input1, array('key1'));
        $this->assertTrue(!isset($output['key1']));

    }


    public function testRemoveStringStartsWith(){
        /**
         * Check with string
         */
        $string = "johndoe";
        $output = removeStringStartsWith('john', $string);
        $this->assertEquals($output, 'doe');

        /**
         * Check with integer
         */
        $string = 1234567890;
        $output = removeStringStartsWith(12345, $string);
        $this->assertEquals($output, 67890);

        /**
         * Check with different needle
         */
        $string = "johndoe";
        $output = removeStringStartsWith('me', $string);
        $this->assertEquals($output, 'johndoe');

        /**
         * Check with full string as a needle
         */
        $string = "johndoe";
        $output = removeStringStartsWith($string, $string);
        $this->assertEquals($output, '');

        /**
         * Check with array as a input
         */
        $string = array("johndoe");
        $output = removeStringStartsWith('john', $string);
        $this->assertEquals($output, false);
    }

    public function testGetSslFile()
    {
        /**
         * check for valid file
         */

        $xml = getSslFile("https://s3.amazonaws.com/puz.puzzlesocial.com/puzzles/PS_CELEB/2012-10-01.xml");
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $this->responseArrayKeyHasValue('puzzleTitle', 'October 1, 2012 - Movie Monday', json_decode($json, true));

        /**
         * check for invalid file
         */

        $xml = getSslFile("https://s3.amazonaws.com/puz.puzzlesocial.com/puzzles/PS_CELEB/2012-09-28.xml");
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $this->responseArrayKeyHasValue('Code', 'AccessDenied', json_decode($json, true));
    }


}