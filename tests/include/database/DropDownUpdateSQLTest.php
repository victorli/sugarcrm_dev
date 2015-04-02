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

/**
 * Class DropDownUpdateSQLTest
 */
class DropDownUpdateSQLTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarBean::clearLoadedDef('Lead');
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if DBManager updateSQL properly processes dropdown fields
     * based on the vardefs
     *
     * @dataProvider dropDownUpdateSQLDataProvider
     */
    public function testDropDownUpdateSQL($fieldDefs, $value, $expected)
    {
        $dbManagerFactory = new DBManagerFactory();
        $dbManager = $dbManagerFactory->getInstance();

        $bean = SugarTestLeadUtilities::createLead();
        $bean->field_defs = $fieldDefs;
        $bean->$fieldDefs['status']['name'] = $value;

        $sql = $dbManager->updateSQL($bean);

        $this->assertContains($expected, $sql);
    }

    public static function dropDownUpdateSQLDataProvider()
    {
        return array(
            array(
                array(
                    'id' => array(
                        'name' => 'id',
                    ),
                    'status' => array(
                        'name' => 'status',
                        'type' => 'enum',
                        'options' => 'lead_status_dom',
                        'default' => 'In Process',
                        'required' => true,
                    ),
                ),
                '',
                "status='In Process'",
            ),
            array(
                array(
                    'id' => array(
                        'name' => 'id',
                    ),
                    'status' => array(
                        'name' => 'status',
                        'type' => 'enum',
                        'options' => 'lead_status_dom',
                        'default' => 'In Process',
                        'required' => true,
                    ),
                ),
                'Value',
                "status='Value'",
            ),
            array(
                array(
                    'id' => array(
                        'name' => 'id',
                    ),
                    'status' => array(
                        'name' => 'status',
                        'type' => 'enum',
                        'options' => 'lead_status_dom',
                        'default' => 'In Process',
                        'required' => false,
                    ),
                ),
                '',
                "status=NULL",
            ),
            array(
                array(
                    'id' => array(
                        'name' => 'id',
                    ),
                    'status' => array(
                        'name' => 'status',
                        'type' => 'enum',
                        'options' => 'lead_status_dom',
                        'default' => 'In Process',
                        'required' => false,
                    ),
                ),
                'Value',
                "status='Value'",
            ),
        );
    }
}
