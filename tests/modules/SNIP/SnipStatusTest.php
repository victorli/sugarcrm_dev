<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once ('modules/SNIP/SugarSNIP.php');

/*
 * Tests the getStatus() function of SugarSNIP to ensure that the return value is correct for various server responses.
 */
class SnipStatusTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $snip;

    public function setUp(){
    	$this->snip = SugarSNIP::getInstance();
    }

    public function testStatusPurchased() { $this->statusTest(json_encode(array('result'=>'ok','status'=>'success')),'purchased'); }

    public function testStatusNotPurchased1() { $this->statusTest(json_encode(array('result'=>'instance not found')),'notpurchased'); }
    public function testStatusNotPurchased2() { $this->statusTest(json_encode(array('result'=>'instance not found','status'=>'fasdfkuaseyrkajsdfh udd')),'notpurchased'); }

    public function testStatusDown1()
    {
        $this->markTestIncomplete('Failing. Need to be fixed by FRM team');
        $this->statusTest(json_encode(array('result' => 'asdofi7aso8fdus', 'status' => 'dafso8dfuds')), 'down');
    }

    public function testStatusDown2()
    {
        $this->markTestIncomplete('Failing. Need to be fixed by FRM team');
        $this->statusTest(json_encode(array('result' => 'asdofi7aso8fdus')), 'down');
    }

    public function testStatusDown3()
    {
        $this->markTestIncomplete('Failing. Need to be fixed by FRM team');
        $this->statusTest('This is not valid', 'down');
    }

    public function testStatusDown4()
    {
        $this->markTestIncomplete('Failing. Need to be fixed by FRM team');
        $this->statusTest('', 'down');
    }

    public function testStatusDown5()
    {
        $this->markTestIncomplete('Failing. Need to be fixed by FRM team');
        $this->statusTest(NULL, 'down');
    }

    public function testStatusDownShowEnableScreen() { $this->statusTest(json_encode(array('result'=>'asdofi7aso8fdus','status'=>'dafso8dfuds')),'notpurchased',null,false); }

    public function testStatusPurchasedError1() { $this->statusTest(json_encode(array('result'=>'ok')),'purchased_error',null); }
    public function testStatusPurchasedError2() { $this->statusTest(json_encode(array('result'=>'ok', 'status'=>'this is a test error status')),'purchased_error','this is a test error status'); }



    protected function statusTest($serverResponse,$expectedStatus,$expectedMessage=null,$snipEmailExists=true)
    {
    	//give snip our mock client
    	$this->snip->setClient(new MockClient($this->snip,$this,$serverResponse));
        $oldemail = $this->snip->getSnipEmail();
        if ($snipEmailExists){
            $this->snip->setSnipEmail("snip-test-182391820@sugarcrm.com");
        }else{
            $this->snip->setSnipEmail("");
        }

    	//call getStatus on snip
    	$status = $this->snip->getStatus();

        $this->snip->setSnipEmail($oldemail);

    	//check to make sure the status is an array with the correct values
    	$this->assertTrue(is_array($status),"getStatus() should always return an associative array of the form array('status'=>string,'message'=>string|null). But it did not return an array.");
    	$this->assertEquals($expectedStatus,$status['status'],"Expected status: '$expectedStatus'. Returned status: '{$status['status']}'");
    	$this->assertEquals($expectedMessage,$status['message'],"Expected message: ".(is_null($expectedMessage)?"null":"'$expectedMessage'").". Returned message: '{$status['message']}'");
    }
}

class MockClient extends SugarHttpClient
{
	private $snip;
	private $hasfailed=false;
	private $testcase;
	private $status;

	/**
	* Construct the mock snip client. Example:
	* $mc = new MockClient(SugarSNIP::getInstance(),new Sugar_PHPUnit_Framework_TestCase,json_encode(array('result'=>'ok','status'=>'success')));
	*  - this example would cause the mock client to return the {'result' : 'ok', 'status' : 'success'}, which is the result returned when the Sugar instance has a SNIP license.
	* @param SugarSNIP $snip The SugarSNIP object
	* @param Sugar_PHPUnit_Framework_TestCase $testcase The testcase that is currently running (used to trigger exceptions/assertions).
	* @param string $status The status message that should be returned from the mock server (should be a string that is a json-encoded object).
	*/
	public function __construct($snip,$testcase,$status)
	{
		$this->snip=$snip;
		$this->testcase = $testcase;
		$this->status = $status;
	}

	//overrides callRest to provide a status message based on the prameter
	public function callRest($url, $postArgs)
    {
    	if (preg_match('`^'.$this->snip->getSnipURL().'status/?`',$url))
    	{
    		return $this->status;
    	}
    	$this->testcase->throwException(new Exception("The MockClient can only handle callRest calls that query the status."));
    }
}
