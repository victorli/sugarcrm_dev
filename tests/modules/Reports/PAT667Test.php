<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'modules/Reports/Report.php';

/**
 * Filtering Report on Multiselect field with "Is One Of" returns "false positives"
 * @ticket PAT-667
 * @author bsitnikovski@sugarcrm.com
 */
class BugPAT667Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Report
     */
    private $report;

    /**
     * @var array
     */
    private static $custom_field_def = array(
        'name'        => 'test_bugpat667',
        'type'        => 'multienum',
        'module'      => 'ModuleBuilder',
        'view_module' => 'Accounts',
        'options'     => 'aaa_list',
        'default'     => '^Consultants^,^International Consultants^',
    );

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('custom_field', array('Accounts', static::$custom_field_def));

        $this->report = new Report();
        $this->report->layout_manager->setAttribute("context", "Filter");
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Test correct filter for Multienum field.
     */
    public function testReportsFilterMultienum()
    {
        $res = '';
        $data = array(
            "operator" => "AND",
            0 => array(
                "name" => self::$custom_field_def['name'] . '_c',
                "table_key" => "self",
                "qualifier_name" => "one_of",
                "input_name0" => array("Consultants")
            )
        );

        $expected = "LIKE '%^Consultants^%'";
        $this->report->filtersIterate($data, $res);
        $this->assertContains($expected, $res);
    }
}
