<?php

require_once 'clients/base/api/ListApi.php';

/**
 * RS-176: Prepare List Api
 */
class RS176Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var ListApi
     */
    protected $listApi;

    /**
     * @var RestService
     */
    protected $serviceMock;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->listApi = new ListApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();

        for($i = 0; $i < 5; $i++) {
            SugarTestAccountUtilities::createAccount();
        }
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for list module test.
     *
     * @return array
     */
    public function listModuleDataProvider()
    {
        return array(
            array(
                array(
                    'module' => 'Accounts',
                ),
                'Accounts',
            ),
            array(
                array(
                    'module' => 'Accounts',
                    'max_num' => '3',
                ),
                'Accounts',
                3,
                true,
                true,
            ),
        );
    }

    /**
     * Test asserts behavior of listModule
     *
     * @dataProvider listModuleDataProvider
     */
    public function testListModule($args, $moduleName, $count = null, $checkCreatedIds = false)
    {

        $result = $this->listApi->listModule($this->serviceMock, $args);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('records', $result);
        $this->assertArrayHasKey('next_offset', $result);
        $this->assertNotEmpty($result['records']);

        if(null !== $count) {
            $this->assertEquals($count, count($result['records']), 'Returned too many results');
        }
        foreach($result['records'] as $record) {
            $this->assertArrayHasKey('id', $record);
            $this->assertArrayHasKey('_module', $record);
            $this->assertEquals($moduleName, $record['_module']);
            if($checkCreatedIds) {
                $this->assertTrue(in_array($record['id'], SugarTestAccountUtilities::getCreatedAccountIds()));
            }
        }
    }
}
