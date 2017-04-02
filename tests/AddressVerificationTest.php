<?php

include_once './vendor/autoload.php';

use ADR\AddressVerification;


class AddressVerificationTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->testObj = new AddressVerification();
    }

    public function testVerifyAddressForStructure()
    {
        $address = ['address1' => '17192 Murphy Ave' ,'address2' => '', 'city' => 'Irvine', 'state' => 'CA', 'zip1' =>
            '92623'];
        $response = $this->testObj->verifyAddress($address);
        $this->assertFalse($response['status']);
    }

    public function testVerifyGoodAddress()
    {
        $address = ['address1' => '2700 PARK AVE' ,'address2' => '', 'city' => 'Tustin', 'state' => 'CA', 'zip' =>
            '92782', "zipExt" => '2708 '];
        $response = $this->testObj->verifyAddress($address);
        $this->assertTrue($response['status']);
    }

    public function testVerifyBadAddress()
    {
        $address = ['address1' => '2700 Test AVE' ,'address2' => '', 'city' => 'Tustin', 'state' => 'CA', 'zip' =>
            '92782', "zipExt" => '2708 '];
        $response = $this->testObj->verifyAddress($address);
        $this->assertFalse($response['status']);
    }

    public function testVerifyIncompleteAddress()
    {
        $address = ['address1' => '17192 Murphy Ave' ,'address2' => '', 'city' => 'Irvine', 'state' => 'CA', 'zip' =>
            '92623', "zipExt" => ' '];
        $response = $this->testObj->verifyAddress($address);
        $msg = 'The address you entered was found but more information is needed 
            (such as an apartment, suite, or box number) to match to a specific address.';
        $this->assertEquals($msg, $response['message']);
    }
}