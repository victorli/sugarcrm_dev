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


require_once 'modules/Notes/Note.php';
require_once 'include/SearchForm/SearchForm2.php';

/**
 * @group Bug45966
 */
class Bug45966 extends Sugar_PHPUnit_Framework_TestCase {

    var $module = 'Notes';
    var $action = 'index';
    var $seed;
    var $form;
    var $array;

    public function setUp() {

        parent::setUp();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('timedate');

        require "modules/".$this->module."/metadata/searchdefs.php";
        require "modules/".$this->module."/metadata/SearchFields.php";
        require "modules/".$this->module."/metadata/listviewdefs.php";

        $this->seed = BeanFactory::getBean($this->module);
        $this->form = new SearchForm($this->seed, $this->module, $this->action);
        $this->form->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl', "advanced_search", $listViewDefs);

        $this->array = array(
            'module'=>$this->module,
            'action'=>$this->action,
            'searchFormTab'=>'advanced_search',
            'query'=>'true',
            'date_entered_advanced_range_choice'=>'',
            'range_date_entered_advanced' => '',
            'start_range_date_entered_advanced' => '',
            'end_range_date_entered_advanced' => '',
        );
        $GLOBALS['current_user']->setPreference('datef', 'm/d/Y');
        $GLOBALS['current_user']->setPreference('timef', 'H:i:s');
        $GLOBALS['current_user']->setPreference('timezone', 'America/Denver');
        $GLOBALS['timedate']->allow_cache = false;
        sugar_cache_clear($GLOBALS['timedate']->get_date_time_format_cache_key(null));
    }

    public function tearDown()
    {
        unset(
            $this->array,
            $this->form,
            $this->seed
        );
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testSearchDateEqualsAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = '12/31/2011';

        $adjDate = $timedate->getDayStartEndGMT($testDate, $current_user);

        $expected = $this->getExpectedPart('>=', $adjDate['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjDate['end'], 'datetime');

        $this->assertResultQuery($expected, '=', $testDate);
    }

    public function testSearchNotOnDateAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = '12/31/2011';

        $adjDate = $timedate->getDayStartEndGMT($testDate, $current_user);

        $expected = strtolower($this->module) . ".date_entered IS NULL OR " .
            $this->getExpectedPart('<', $adjDate['start'], 'datetime') .
            " OR ". $this->getExpectedPart('>', $adjDate['end'], 'datetime');

        $this->assertResultQuery($expected, 'not_equal', $testDate);
    }

    public function testSearchAfterDateAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = '12/31/2011';

        $adjDate = $timedate->getDayStartEndGMT($testDate, $current_user);

        $expected = $this->getExpectedPart('>', $adjDate['end'], 'datetime');

        $this->assertResultQuery($expected, 'greater_than', $testDate);
    }

    public function testSearchBeforeDateAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = '01/01/2011';

        $adjDate = $timedate->getDayStartEndGMT($testDate, $current_user);

        $expected = $this->getExpectedPart('<', $adjDate['start'], 'datetime');

        $this->assertResultQuery($expected, 'less_than', $testDate);
    }

    public function testSearchLastSevenDaysAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = 'last_7_days';

        $adjToday = $timedate->getDayStartEndGMT($timedate->getNow(true), $current_user);
        $adjStartDate = $timedate->getDayStartEndGMT($timedate->getNow(true)->get("-6 days"), $current_user);

        $expected = $this->getExpectedPart('>=', $adjStartDate['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjToday['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchNextSevenDaysAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = 'next_7_days';

        $adjToday = $timedate->getDayStartEndGMT($timedate->getNow(true), $current_user);
        $adjEndDate = $timedate->getDayStartEndGMT($timedate->getNow(true)->get("+6 days"), $current_user);

        $expected = $this->getExpectedPart('>=', $adjToday['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjEndDate['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchLastThirtyDaysAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = 'last_30_days';

        $adjToday = $timedate->getDayStartEndGMT($timedate->getNow(true), $current_user);
        $adjStartDate = $timedate->getDayStartEndGMT($timedate->getNow(true)->get("-29 days"), $current_user);

        $expected = $this->getExpectedPart('>=', $adjStartDate['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjToday['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchNextThirtyDaysAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = 'next_30_days';

        $adjToday = $timedate->getDayStartEndGMT($timedate->getNow(true), $current_user);
        $adjEndDate = $timedate->getDayStartEndGMT($timedate->getNow(true)->get("+29 days"), $current_user);

        $expected = $this->getExpectedPart('>=', $adjToday['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjEndDate['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchLastMonthAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = 'last_month';

        $now = $timedate->getNow(true);
        $month_number = $now->month == 1 ? 12 : $now->month-1;
        $year_number = $now->month == 1 ? $now->year - 1 : $now->year;
        $month = $now->get_day_begin(1, $month_number, $year_number);

        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT(
            $month->get_day_begin($month->days_in_month),
            $current_user
        );

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchThisMonthAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = 'this_month';

        $month = $timedate->getNow(true)->get_day_begin(1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT(
            $month->get_day_begin($month->days_in_month),
            $current_user
        );

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchNextMonthAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = 'next_month';

        $now = $timedate->getNow(true);
        $month = $now->get_day_begin(1, $now->month+1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT(
            $month->get_day_begin($month->days_in_month),
            $current_user
        );

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchLastYearAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = 'last_year';

        $now = $timedate->getNow(true);
        $month = $now->get_day_begin(1, 1, $now->year-1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin(31, 12), $current_user);

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchThisYearAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = 'this_year';

        $month = $timedate->getNow(true)->get_day_begin(1, 1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin(31, 12), $current_user);

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchNextYearAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testDate = 'next_year';

        $now = $timedate->getNow(true);
        $month = $now->get_day_begin(1, 1, $now->year+1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin(31, 12), $current_user);

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchDateIsBetweenAdjustsForTimeZone() {
        global $timedate, $current_user;

        $testStartDate = '01/01/2011';
        $testEndDate = '12/31/2011';

        $this->array['start_range_date_entered_advanced'] = $testStartDate;
        $this->array['end_range_date_entered_advanced'] = $testEndDate;

        $adjStartDate = $timedate->getDayStartEndGMT($testStartDate, $current_user);
        $adjEndDate = $timedate->getDayStartEndGMT($testEndDate, $current_user);

        $expected = $this->getExpectedPart('>=', $adjStartDate['start'], 'datetime') .
            " AND " . $this->getExpectedPart('<=', $adjEndDate['end'], 'datetime');

        $this->assertResultQuery($expected, 'between', '');
    }

    protected function getExpectedPart($compare, $value, $type)
    {
        $db = DBManagerFactory::getInstance();
        $tablename = strtolower($this->seed->table_name);
        return "{$tablename}.date_entered {$compare} {$db->convert($db->quoted($value), $type)}";
    }

    protected function assertResultQuery($expected, $date, $range)
    {
        $this->array['date_entered_advanced_range_choice'] = $date;
        $this->array['range_date_entered_advanced'] = $range;
        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);
        $this->assertContains($expected, $query[0]);
    }
}
