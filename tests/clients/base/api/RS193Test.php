<?php

require_once 'clients/base/api/FilterApi.php';

class RS193Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testAddTrackerFilter()
    {
        $api = new FilterApi();
        $actual = $api->filterList(SugarTestRestUtilities::getRestServiceMock(), array(
                '__sugar_url' => 'v10/Accounts/filter',
                'filter' => array(
                    array(
                        '$tracker' => '-7 DAY'
                    ),
                ),
                'fields' => 'id,name',
                'max_num' => 3,
                'module' => 'Accounts',
            ));
        $this->assertArrayHasKey('records', $actual);
    }
}
