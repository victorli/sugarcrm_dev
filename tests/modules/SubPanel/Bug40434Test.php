<?php

require_once "include/SubPanel/SubPanelDefinitions.php";

class Bug40434Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->is_admin = true;
        $user->save();
        $GLOBALS['current_user'] = $user;
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group bug40434
     */
    public function testNameOfModifiedByNameField()
    {
        $contact = new Contact();
        $contact->create_new_list_query("", "");
        $this->assertEquals($contact->field_defs['modified_by_name']['name'], "modified_by_name", "Name of modified by name field should be 'modified_by_name'");
    }
}