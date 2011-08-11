<?php

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
        $this->_opportunity = new MockOpportunity();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query('DELETE FROM opportunities WHERE id IN (\'' . implode("', '", $this->_opportunityIds) . '\')');
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
        $this->_opportunityIds[] = $this->_opportunity->save();
        $this->assertFalse($this->_opportunity->isOwner($this->_opportunity->created_by));
        $this->assertTrue($this->_opportunity->mailWasSent);    
    }
}