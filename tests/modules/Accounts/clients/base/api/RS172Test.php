<?php

require_once 'modules/Accounts/clients/base/api/AccountsRelateApi.php';

/**
 * RS-172: Prepare Accounts Relate Api
 */
class RS172Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var AccountsRelateApi */
    protected $api = null;

    /** @var Account */
    protected $account = null;

    /** @var Call */
    protected $call = null;

    /** @var Meeting */
    protected $meeting = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new AccountsRelateApi();

        $this->account = SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        if ($this->call instanceof Call) {
            $this->call->mark_deleted($this->call->id);
        }
        if ($this->meeting instanceof Meeting) {
            SugarTestMeetingUtilities::removeAllCreatedMeetings();
        }
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts correct result from query
     */
    public function testFilterRelatedCall()
    {
        $this->call = new Call();
        $this->call->name = 'Test Call';
        $this->call->assigned_user_id = $GLOBALS['current_user']->id;
        $this->call->save();
        $this->account->load_relationship('calls');
        $this->account->calls->add($this->call);

        $actual = $this->api->filterRelated($this->service, array(
                'module' => 'Accounts',
                'record' => $this->account->id,
                'link_name' => 'calls',
                'include_child_items' => true,
            ));
        $this->assertArrayHasKey('records', $actual);
        $actual = reset($actual['records']);
        $this->assertEquals($this->call->id, $actual['id']);
    }

    /**
     * Test asserts correct result from query
     */
    public function testFilterRelatedMeeting()
    {
        $this->meeting = SugarTestMeetingUtilities::createMeeting('', $GLOBALS['current_user']);
        $this->account->load_relationship('meetings');
        $this->account->meetings->add($this->meeting);

        $actual = $this->api->filterRelated($this->service, array(
                'module' => 'Accounts',
                'record' => $this->account->id,
                'link_name' => 'meetings',
                'include_child_items' => true,
            ));
        $this->assertArrayHasKey('records', $actual);
        $actual = reset($actual['records']);
        $this->assertEquals($this->meeting->id, $actual['id']);
    }
}
