<?php

require_once 'modules/Meetings/clients/base/api/MeetingsApi.php';
require_once 'clients/base/api/FilterApi.php';

/**
 * RS-81
 * Prepare Meetings Api
 * Test asserts only success of result, not result data.
 */
class RS81Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var MeetingsApi */
    protected $api = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();

        $this->meetingsApi = new MeetingsApi();
        $this->filterApi = new FilterApi();
        SugarTestMeetingUtilities::createMeeting();
    }

    public function tearDown()
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts behavior of filterList method without wildcard search
     */
    public function testGlobalSearchWithoutQ()
    {
        $actual = $this->filterApi->filterList($this->service, array(
            'module' => 'Meetings',
        ));
        $this->assertArrayHasKey('records', $actual);
    }

    /**
     * Test asserts behavior of filterList method with wildcard search
     */
    public function testGlobalSearchWithQ()
    {
        $actual = $this->filterApi->filterList($this->service, array(
            'module' => 'Meetings',
            'q' => 'anything',
        ));
        $this->assertArrayHasKey('records', $actual);
    }

    /**
     * Test asserts behavior of getAgenda method
     */
    public function testGetAgenda()
    {
        $actual = $this->meetingsApi->getAgenda($this->service, array());
        $this->assertArrayHasKey('today', $actual);
        $this->assertArrayHasKey('tomorrow', $actual);
        $this->assertArrayHasKey('upcoming', $actual);
    }
}
