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

require_once 'modules/ForecastManagerWorksheets/ForecastManagerWorksheetHooks.php';

class ForecastManagerWorksheetHooksTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderSetManagerSavedFlag
     * @param array $data
     * @param boolean $expected
     */
    public function testSetManagerSavedFlag($data, $expected)
    {
        /** @var ForecastManagerWorksheet $worksheet */
        $worksheet = $this->getMock('ForecastManagerWorksheet', array('save'));

        foreach ($data as $key => $value) {
            $worksheet->$key = $value;
        }

        ForecastManagerWorksheetHooks::setManagerSavedFlag($worksheet, 'before_save');

        $this->assertEquals($expected, $worksheet->manager_saved);
    }

    public static function dataProviderSetManagerSavedFlag()
    {
        return array(
            array(
                array(
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => false
                ),
                true
            ),
            array(
                array(
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 0,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => false
                ),
                false
            ),
            array(
                array(
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => true
                ),
                true
            ),
            array(
                array(
                    'assigned_user_id' => 'test_user_1',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'worksheet',
                    'manager_saved' => false
                ),
                false
            ),
            array(
                array(
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 1,
                    'draft_save_type' => 'assign_quota',
                    'manager_saved' => false
                ),
                false
            ),
            array(
                array(
                    'assigned_user_id' => 'test_user',
                    'modified_user_id' => 'test_user',
                    'draft' => 0,
                    'draft_save_type' => 'assign_quota',
                    'manager_saved' => false
                ),
                false
            )
        );
    }
}
