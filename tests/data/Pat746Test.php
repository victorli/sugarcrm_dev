<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

class Pat746Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $lead;

    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->lead = new Pat746Test_SugarBean();
    }

    protected function tearDown() {
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testPopulateFromRow()
    {
        global $current_user;
        $current_user->setPreference('default_locale_name_format', 'f l');

        $this->lead->populateFromRow(
            array(
                'first_name' => 'John',
                'last_name' => 'Doe',
            )
        );

        $this->assertEquals('John Doe', $this->lead->name);
    }
}

class Pat746Test_SugarBean extends SugarBean
{
    public $object_name = 'Pat746Test';
    public $createLocaleFormattedName = true;
    public $module_name = 'Leads';
    public $field_defs = array(
        'name' => array(
            'name' => 'name',
            'rname' => 'name',
            'type' => 'fullname',
        ),
    );
    public $name_format_map = array(
        'f' => 'first_name',
        'l' => 'last_name',
        's' => 'salutation',
        't' => 'title',
    );
}
