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
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/


require_once('modules/Reports/Report.php');
require_once('modules/Reports/templates/templates_list_view.php');

/**
 * Bug #53360
 * Matrix Report gets broken when data is ordered by one of summary columns
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 53360
 */
class Bug53360Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $rowsAndColumnsData, $report;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        $this->report = new Report();
        $this->report->report_def = array('group_defs' => $this->_getDummyGroupDefs());
        $this->report->group_defs_Info = $this->_getDummyGroupDefsInfo();
        $this->rowsAndColumnsData = $this->_getData();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testGroupByFunctionZeroCount()
    {
        $ret = whereToStartGroupByRow($this->report, 0, $this->rowsAndColumnsData, null);

        $this->assertEquals(-1, $ret);
    }

    public function testGroupByFunctionUniqueRecord()
    {
        $row = $this->rowsAndColumnsData[0]; // this is chris, he is unique in our data set, should return -1
        $ret = whereToStartGroupByRow($this->report, 0, $this->rowsAndColumnsData, $row);

        $this->assertEquals(-1, $ret);
    }

    public function testGroupByFunctionNonUniqueRecord()
    {
        $row = $this->rowsAndColumnsData[1]; // this is first entry of sarah, she is not unique in our data set, should return index of first appearance
        $ret = whereToStartGroupByRow($this->report, 1, $this->rowsAndColumnsData, $row);
        $this->assertEquals(1, $ret); // first appearance of sarah is 1st index

        $row = $this->rowsAndColumnsData[2]; // this is second entry of sarah, she is not unique in our data set, should return index of first appearance
        $ret = whereToStartGroupByRow($this->report, 2, $this->rowsAndColumnsData, $row);
        $this->assertEquals(1, $ret); // first appearance of sarah is 1st index
    }

    private function _getDummyGroupDefs()
    {
        return array(
            0 => array('name' => 'user_name', 'label' => 'User Name', 'table_key' => 'Opportunities:assigned_user_link', 'type' => 'user_name'),
            1 => array('name' => 'sales_stage', 'label' => 'Sales Stage', 'table_key' => 'self', 'type' => 'enum'),
        );
    }

    private function _getDummyGroupDefsInfo()
    {
        return array(
            'user_name#Opportunities:assigned_user_link' => array('name' => 'user_name', 'label' => 'User Name', 'table_key' => 'Opportunities:assigned_user_link', 'type' => 'username', 'index' => 0),
            'sales_stage#self' => array('name' => 'sales_stage', 'label' => 'Sales Stage', 'table_key' => 'self', 'type' => 'enum', 'index' => 1),
        );
    }

    private function _getData()
    {
        return array(
            array('cells' => array("chris", "Value Proposition", "$10,000.00", "$10,000.00", "1"), 'count' => 1, 'User Name' => 'chris'),
            array('cells' => array("sarah", "Value Proposition", "$10,000.00", "$20,000.00", "2"), 'count' => 2, 'User Name' => 'sarah'),
            array('cells' => array("sarah", "Needs Analysis", "$10,000.00", "$10,000.00", "1"), 'count' => 1, 'User Name' => 'sarah'),
        );
    }

}
