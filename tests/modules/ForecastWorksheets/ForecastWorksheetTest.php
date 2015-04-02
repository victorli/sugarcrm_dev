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

require_once 'tests/SugarTestDatabaseMock.php';

class ForecastWorksheetTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var SugarTestDatabaseMock
     */
    protected $db;

    public function setUp()
    {

        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        SugarTestForecastUtilities::setUpForecastConfig();
        // this is needed to preload vardefs & ACLs so DB mocking won't mess with them
        BeanFactory::getBean('ForecastWorksheets');
        BeanFactory::getBean('Accounts');

        $this->db = SugarTestHelper::setUp('mock_db');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        SugarTestForecastUtilities::tearDownForecastConfig();
    }

    public function testGetRelatedNameReturnsEmpty()
    {
        $this->db->addQuerySpy(
            'accountQuery',
            '/my_test_id/',
            array(
                array(
                    'name' => 'My Test Account'
                ),
            )
        );

        $forecast_worksheet = BeanFactory::getBean('ForecastWorksheets');
        $return = SugarTestReflection::callProtectedMethod(
            $forecast_worksheet,
            'getRelatedName',
            array('Accounts', 'test_id')
        );
        $this->assertEmpty($return);
    }

    public function testGetRelatedNameReturnsName()
    {
        $acc_name = 'My Test Account';
        $acc_id = 'my_test_id';
        $this->db->addQuerySpy(
            'accountQuery',
            '/' . $acc_id . '/',
            array(
                array(
                    'name' => $acc_name
                ),
            )
        );

        $forecast_worksheet = BeanFactory::getBean('ForecastWorksheets');
        $return = SugarTestReflection::callProtectedMethod(
            $forecast_worksheet,
            'getRelatedName',
            array('Accounts', $acc_id)
        );
        $this->assertEquals($acc_name, $return);
    }
}
