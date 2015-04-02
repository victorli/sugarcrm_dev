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

 
require_once('include/SugarFields/Fields/Relate/SugarFieldRelate.php');
require_once('include/SugarFields/Fields/Encrypt/SugarFieldEncrypt.php');

class SugarFieldEncryptTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}
    
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    
    public function _providerEmailTemplateFormat()
    {
        require_once("include/utils/encryption_utils.php");
        return array(
            array(blowfishEncode(blowfishGetKey('encrypt_field'),'Test value'), 'Test value'),
            );
    }
    
    /**
     * @dataProvider _providerEmailTemplateFormat
     */
    public function testEmailTemplateFormat($unformattedValue, $expectedValue)
	{
        require_once('include/SugarFields/SugarFieldHandler.php');
   		$sfr = SugarFieldHandler::getSugarField('encrypt');
        $formattedValue = $sfr->getEmailTemplateValue($unformattedValue,array(), array('notify_user' => $GLOBALS['current_user']));
        $this->assertEquals($expectedValue, $formattedValue);    	
    }
}

?>
