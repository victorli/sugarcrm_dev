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

require_once 'modules/DynamicFields/FieldCases.php';

/**
 * @ticket 59155
 */
class Bug59155Test extends Sugar_PHPUnit_Framework_TestCase
{
    private static $custom_field_def = array(
        'formula'     => 'related($accounts,"name")',
        'name'        => 'bug_59155',
        'type'        => 'text',
        'label'       => 'LBL_CUSTOM_FIELD',
        'module'      => 'ModuleBuilder',
        'view_module' => 'Cases',
    );

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', array(true, 1));

        $mbc = new ModuleBuilderController();
        $_REQUEST = self::$custom_field_def;
        $mbc->action_SaveField();

        VardefManager::refreshVardefs('Cases', 'Case');
    }

    public static function tearDownAfterClass()
    {
        $mbc = new ModuleBuilderController();

        $custom_field_def = self::$custom_field_def;
        $custom_field_def['name'] .= '_c';
        $_REQUEST = $custom_field_def;
        $mbc->action_DeleteField();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        $_REQUEST = array();
        SugarCache::$isCacheReset = false;

        SugarTestHelper::tearDown();
    }

    public function testCaseCalcFieldIsConsidered()
    {
        $account = new Bug59155Test_Account();
        $fields = $account->get_fields_influencing_linked_bean_calc_fields('cases');
        $this->assertContains('name', $fields);
    }
}

class Bug59155Test_Account extends Account
{
    public function get_fields_influencing_linked_bean_calc_fields($linkName)
    {
        return parent::get_fields_influencing_linked_bean_calc_fields($linkName);
    }
}
