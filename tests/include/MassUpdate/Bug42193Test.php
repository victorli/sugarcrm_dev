<?php

require_once('modules/EmailMan/EmailMan.php');
require_once('include/MassUpdate.php');

class Bug42193Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
	{
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	    $GLOBALS['current_user']->is_admin = '1';
	    $GLOBALS['current_user']->save();
	}
	
	public function tearDown()
	{
        unset($GLOBALS['current_user']);
	}
    
    public function testDateConversionMassUpdate()
    {
        $emailMan = new EmailMan();
        
        $mass = new MassUpdate();
        
        $mass->setSugarBean($emailMan);
        $pattern = '/07\/22\/2011 [0-9]{2}:[0-9]{2}/';
        $this->assertRegExp(
			$pattern,
			$mass->date_to_dateTime('send_date_time', '07/22/2011')
		);        
    }
}