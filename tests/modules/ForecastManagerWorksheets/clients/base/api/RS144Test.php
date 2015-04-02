<?php

require_once 'modules/ForecastManagerWorksheets/clients/base/api/ForecastManagerWorksheetsFilterApi.php';

/**
 * RS-144
 * Prepare ForecastManagerWorksheetsFilter Api
 */
class RS144Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var TimePeriod */
    protected $timeperiod = null;

    /** @var Quota */
    protected $quota = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        $this->quota = SugarTestQuotaUtilities::createQuota();
        $this->quota->user_id = $GLOBALS['current_user']->id;
        $this->quota->quota_type = 'Rollup';
        $this->quota->timeperiod_id = $this->timeperiod->id;
        $this->quota->save();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts that createFilter method will receive right parameters after forecastManagerWorksheetsGet method call
     *
     * @dataProvider getDataForForecastManagerWorksheetsGet
     *
     * @param array $args
     * @param mixed $expectedUserId
     * @param mixed $expectedTimePeriodId
     * @param mixed $expectedType
     */
    public function testForecastManagerWorksheetsGet($args, $expectedUserId, $expectedTimePeriodId, $expectedType)
    {
        $api = $this->getMock('ForecastManagerWorksheetsFilterApi', array('createFilter', 'filterList'));
        $api->expects($this->once())->method('createFilter')->with($this->equalTo($this->service), $this->equalTo($expectedUserId), $this->equalTo($expectedTimePeriodId), $this->equalTo($expectedType));
        $api->forecastManagerWorksheetsGet($this->service, $args);
    }

    /**
     * Data Provider for testForecastManagerWorksheetsGet
     *
     * @return array
     */
    public function getDataForForecastManagerWorksheetsGet()
    {
        return array(
            array(
                array(
                    'module' => 'ForecastManagerWorksheets',
                ),
                false,
                false,
                false,
            ),
            array(
                array(
                    'module' => 'ForecastManagerWorksheets',
                    'user_id' => 1,
                    'timeperiod_id' => 2,
                    'type' => 3,
                ),
                1,
                2,
                3,
            ),
        );
    }

    /**
     * Test asserts that chart data has right structure if we pass user & time period
     */
    public function testForecastManagerWorksheetsChartGet()
    {
        $api = $this->getMock('ForecastManagerWorksheetsFilterApi', array('ForecastManagerWorksheetsGet', 'getDirectHierarchyUsers'));
        $api->expects($this->once())->method('ForecastManagerWorksheetsGet')->will($this->returnValue(array('records' => array())));
        $api->expects($this->once())->method('getDirectHierarchyUsers')->will($this->returnValue(array('records' => array(
                    array(
                        'id' => $GLOBALS['current_user']->id,
                        'full_name' => $GLOBALS['current_user']->full_name,
                    ),
                ))));
        $actual = $api->forecastManagerWorksheetsChartGet($this->service, array(
                'timeperiod_id' => $this->timeperiod->id,
                'user_id' => $GLOBALS['current_user']->id,
            ));
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('data', $actual);
        $this->assertNotEmpty('data', $actual);
        $this->assertArrayHasKey('quota', $actual);
    }

    /**
     * Test asserts that chart data has right structure if we don't pass user & time period
     */
    public function testForecastManagerWorksheetsChartGetNoData()
    {
        $api = $this->getMock('ForecastManagerWorksheetsFilterApi', array('ForecastManagerWorksheetsGet', 'getDirectHierarchyUsers'));
        $api->expects($this->never())->method('ForecastManagerWorksheetsGet');
        $api->expects($this->never())->method('getDirectHierarchyUsers');
        $actual = $api->forecastManagerWorksheetsChartGet($this->service, array(
                'no_data' => 1,
            ));
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('data', $actual);
        $this->assertArrayHasKey('quota', $actual);
    }

    /**
     * Test asserts that we have target_quota if we need that
     */
    public function testForecastManagerWorksheetsChartGetTargetQuota()
    {
        $api = $this->getMock('ForecastManagerWorksheetsFilterApi', array('ForecastManagerWorksheetsGet', 'getDirectHierarchyUsers'));
        $api->expects($this->never())->method('ForecastManagerWorksheetsGet');
        $api->expects($this->never())->method('getDirectHierarchyUsers');
        $actual = $api->forecastManagerWorksheetsChartGet($this->service, array(
                'no_data' => 1,
                'target_quota' => 1,
                'timeperiod_id' => $this->timeperiod->id,
                'user_id' => $GLOBALS['current_user']->id,
            ));
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('target_quota', $actual);
        $this->assertEquals($this->quota->amount, $actual['target_quota']);
    }

    /**
     * We should get current_user if there are no parameters
     */
    public function testGetDirectHierarchyUsers()
    {
        $GLOBALS['current_user']->reports_to_id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user']->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'getDirectHierarchyUsers', array(
                $this->service,
                array(),
            ));
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('records', $actual);
        $record = reset($actual['records']);
        $this->assertNotEmpty($record);
        $this->assertEquals($GLOBALS['current_user']->id, $record['id']);
    }

    /**
     * We should get exception if current_user isn't manager
     *
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testGetDirectHierarchyUsersNotAuthorized()
    {
        $api = new ForecastManagerWorksheetsFilterApi();
        SugarTestReflection::callProtectedMethod($api, 'getDirectHierarchyUsers', array(
                $this->service,
                array(),
            ));
    }

    /**
     * We should get custom user if current_user is manager and request him
     */
    public function testGetDirectHierarchyUsersCustomUser()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->reports_to_id = $GLOBALS['current_user']->id;
        $user->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'getDirectHierarchyUsers', array(
                $this->service,
                array(
                    'user_id' => $user->id,
                ),
            ));
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('records', $actual);
        $record = reset($actual['records']);
        $this->assertNotEmpty($record);
        $this->assertEquals($user->id, $record['id']);
    }

    /**
     * We should get exception if custom user doesn't present in system
     *
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testGetDirectHierarchyUsersCustomUserInvalidParameter()
    {
        $GLOBALS['current_user']->reports_to_id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user']->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        SugarTestReflection::callProtectedMethod($api, 'getDirectHierarchyUsers', array(
                $this->service,
                array(
                    'user_id' => '-1',
                ),
            ));
    }

    /**
     * Test asserts that createFilter method will receive right parameters after filterList method call
     *
     * @dataProvider getDataForFilterList
     * @param array $args
     * @param mixed $expectedAssignedUser
     * @param mixed $expectedTimePeriod
     * @param mixed $expectedType
     */
    public function testFilterList($args, $expectedAssignedUser, $expectedTimePeriod, $expectedType)
    {
        $api = $this->getMock('ForecastManagerWorksheetsFilterApi', array('createFilter'));
        $api->expects($this->once())->method('createFilter')->with(
            $this->equalTo($this->service),
            $this->equalTo($expectedAssignedUser),
            $this->equalTo($expectedTimePeriod),
            $this->equalTo($expectedType)
        );
        $api->filterList($this->service, $args);
    }

    /**
     * Data Provider for testFilterList
     *
     * @return array
     */
    public function getDataForFilterList()
    {
        return array(
            array(
                array(
                    'module' => 'ForecastManagerWorksheets',
                ),
                false,
                false,
                false,
            ),
            array(
                array(
                    'module' => 'ForecastManagerWorksheets',
                    'filter' => 15,
                ),
                false,
                false,
                false,
            ),
            array(
                array(
                    'module' => 'ForecastManagerWorksheets',
                    'filter' => array(
                        array(
                            'assigned_user_id' => 1,
                        ),
                        array(
                            'timeperiod_id' => 2,
                        ),
                        array(
                            'type' => 3,
                        ),
                    ),
                ),
                1,
                2,
                3,
            ),
        );
    }

    /**
     * Test asserts that we have correct filter if there are no parameters
     */
    public function testCreateFilter()
    {
        $GLOBALS['current_user']->reports_to_id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user']->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                false,
                false,
            ));
        $this->assertNotEmpty($actual);
        $result = array();
        foreach ($actual as $value) {
            $result[key($value)] = current($value);
        }
        $this->assertArrayHasKey('assigned_user_id', $result);
        $this->assertEquals($this->service->user->id, $result['assigned_user_id']);
        $this->assertArrayHasKey('draft', $result);
        $this->assertEquals(1, $result['draft']);
        $this->assertArrayHasKey('timeperiod_id', $result);
        $this->assertEquals(TimePeriod::getCurrentId(), $result['timeperiod_id']);
    }

    /**
     * We should get exception if current_user isn't manager
     *
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testCreateFilterNotAuthorized()
    {
        $api = new ForecastManagerWorksheetsFilterApi();
        SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                false,
                false,
            ));
    }

    /**
     * We should get customer user if current_user is manager
     */
    public function testCreateFilterCustomUser()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->reports_to_id = $GLOBALS['current_user']->id;
        $user->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                $user->id,
                false,
            ));
        $this->assertNotEmpty($actual);
        $result = array();
        foreach ($actual as $value) {
            $result[key($value)] = current($value);
        }
        $this->assertArrayHasKey('assigned_user_id', $result);
        $this->assertEquals($user->id, $result['assigned_user_id']);
    }

    /**
     * We should get exception if custom user isn't present in system
     *
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testCreateFilterCustomUserInvalidParameter()
    {
        $GLOBALS['current_user']->reports_to_id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user']->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                '-1',
                false,
            ));
    }

    /**
     * We should get correct time period if it's passed
     */
    public function testCreateFilterTimePeriod()
    {
        $GLOBALS['current_user']->reports_to_id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user']->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                false,
                $this->timeperiod->id,
            ));
        $this->assertNotEmpty($actual);
        $result = array();
        foreach ($actual as $value) {
            $result[key($value)] = current($value);
        }
        $this->assertArrayHasKey('timeperiod_id', $result);
        $this->assertEquals($this->timeperiod->id, $result['timeperiod_id']);
    }

    /**
     * We should get exception if time period isn't present in system
     *
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testCreateFilterTimePeriodInvalidParameter()
    {
        $GLOBALS['current_user']->reports_to_id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user']->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        SugarTestReflection::callProtectedMethod($api, 'createFilter', array(
                $this->service,
                false,
                create_guid(),
            ));
    }
}
