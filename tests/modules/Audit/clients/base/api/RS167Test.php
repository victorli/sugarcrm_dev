<?php

require_once 'modules/Audit/clients/base/api/AuditApi.php';

/**
 * RS-167: Prepare Audit Api
 */
class RS167Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Account */
    protected $account = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->account = SugarTestAccountUtilities::createAccount();
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts behavior of viewChangeLog method
     */
    public function testViewChangeLog()
    {
        $this->account->retrieve($this->account->id);
        $this->account->name = 'Test 1';
        $this->account->save();
        $this->account->name = 'Test 2';
        $this->account->save();

        $api = new AuditApi();
        $data = $api->viewChangeLog(SugarTestRestUtilities::getRestServiceMock(), array(
                'module' => 'Accounts',
                'record' => $this->account->id,
            ));
        $this->assertArrayHasKey('records', $data);
        $this->assertEquals(2, count($data['records']));
    }
}
