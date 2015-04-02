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
 
class Bug40989 extends Sugar_PHPUnit_Framework_TestCase
{
    var $contact;
/*
	public static function setUpBeforeClass()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
       
	}

	public static function tearDownAfterClass()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}
*/
	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->contact = SugarTestContactUtilities::createContact();
	}

	public function tearDown()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestContactUtilities::removeAllCreatedContacts();
	}

    /*
     * @group bug40989
     */
    public function testRetrieveByStringFieldsFetchedRow()
    {
        $loadedContact = BeanFactory::getBean('Contacts');
        $loadedContact = $loadedContact->retrieve_by_string_fields(array('last_name'=>'SugarContactLast'));
        $this->assertEquals('SugarContactLast', $loadedContact->fetched_row['last_name']);
    }

    public function testProcessFullListQuery()
    {
        $loadedContact = new Contact(); // loadBean('Contacts');
        $loadedContact->disable_row_level_security = true;
        $contactList = $loadedContact->get_full_list();
        $exampleContact = array_pop($contactList);	
        $this->assertNotNull($exampleContact->fetched_row['id']);
    }
}
