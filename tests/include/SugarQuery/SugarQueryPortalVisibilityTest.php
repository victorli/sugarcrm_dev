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

require_once 'include/SugarQuery/SugarQuery.php';
require_once 'data/SugarVisibility.php';
require_once 'data/visibility/SupportPortalVisibility.php';

class SugarQueryPortalVisibilityTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $bean = null;
    public $vis = null;
    public $query = null;

    public static function setupBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test we call the proper methods
     */
    public function testVisibilityCall()
    {
        $bean = $this->getMock('SugarBean', array('loadVisibility'));
        $vis = $this->getMock('SupportPortalVisibility', array('addVisibilityFromQuery', 'addVisibilityWhereQuery'), array($bean));
        $bean->expects($this->any())->method('loadVisibility')->will($this->returnValue($vis));
        $bean->module_dir = 'test';
        $query = new SugarQuery();
        $vis->expects($this->once())->method('addVisibilityFromQuery')->with($query)->will($this->returnValue($query));
        $vis->expects($this->once())->method('addVisibilityWhereQuery')->with($query)->will($this->returnValue($query));
        $bean->addVisibilityQuery($query);
        unset($vis);
        unset($bean);
        unset($query);
    }

    public function testQueryReturnWithAccounts()
    {
        $contact = new ContactsPortalVisibilityQueryMock();
        $contact->setVisibility(new SupportPortalVisibilityQueryMock($contact));
        $contact->id = 1;
        $_SESSION['contact_id'] = 1;
        $_SESSION['type'] = 'support_portal';
        $query = new SugarQuery();
        $query->select('*');
        $query->from($contact);
        $contact->addVisibilityQuery($query);
        $queryShouldBe = "INNER JOIN  accounts_contacts ON contacts.id=accounts_contacts.contact_id AND accounts_contacts.deleted=0
 INNER JOIN  accounts accounts_pv ON accounts_pv.id=accounts_contacts.account_id AND accounts_pv.deleted=0
 AND accounts_pv.id IN ('1','2','3','4')  WHERE contacts.deleted";


        $this->assertContains($queryShouldBe, $query->compileSql(), "The query does not match");
        unset($_SESSION);
        unset($contact);
        unset($query);
    }

    public function testQueryReturnWithoutAccounts()
    {
        $this->markTestIncomplete('Bug in SugarQuery Remove this when fixed: https://sugarcrm.atlassian.net/browse/BR-210');
        $contact = new ContactsPortalVisibilityQueryMock();
        $contact->setVisibility(new SupportPortalVisibility($contact));
        $contact->id = 1;
        $_SESSION['contact_id'] = 1;
        $_SESSION['type'] = 'support_portal';
        $query = new SugarQuery();
        $query->from($contact);
        $contact->addVisibilityQuery($query);

        $queryShouldBe = "SELECT  * FROM contacts WHERE contacts.deleted = 0 AND  ( contacts.id = '1' )";

        $this->assertEquals($queryShouldBe, $query->compileSql(), "The query does not match");
        unset($_SESSION);
        unset($contact);
        unset($query);
    }
}

class ContactsPortalVisibilityQueryMock extends Contact
{
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }
}

class SupportPortalVisibilityQueryMock extends SupportPortalVisibility
{
    public function getAccountIds()
    {
        return $this->accountIds = array('1','2','3','4');
    }
}
