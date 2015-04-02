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

require_once "modules/Opportunities/Opportunity.php";

class MockOpportunity extends Opportunity {

    public $mailWasSent = false;
    public $notify_inworkflow = true;
    public $set_created_by = false;
    
    public function send_assignment_notifications() {
        $this->mailWasSent = true;
    }
}

class Bug42727Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_opportunity;
    protected $_opportunityIds = array();

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $this->_opportunity = new MockOpportunity();
        $this->_opportunity->date_closed = TimeDate::getInstance()->getNow()->asDbDate();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query('DELETE FROM opportunities WHERE id IN (\'' . implode("', '", $this->_opportunityIds) . '\')');
        SugarTestHelper::tearDown();
    }

    
    public function testSentMail() 
    {
        $this->_opportunity->created_by = $this->_opportunity->assigned_user_id = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->_opportunityIds[] = $this->_opportunity->save();
        $this->assertTrue($this->_opportunity->isOwner($this->_opportunity->created_by));
        $this->assertFalse($this->_opportunity->mailWasSent);
    }
    
    public function testNotSentMail() 
    {
        $this->_opportunity->created_by = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->_opportunity->assigned_user_id = SugarTestUserUtilities::createAnonymousUser()->id;
        $this->_opportunityIds[] = $this->_opportunity->save(true);
        $this->assertFalse($this->_opportunity->isOwner($this->_opportunity->created_by));
        $this->assertTrue($this->_opportunity->mailWasSent);    
    }
}
