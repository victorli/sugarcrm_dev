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

require_once 'modules/vCals/vCal.php';

/**
 * RS-135: Prepare vCals Module
 * Test cover correct execution of methods, not their logic
 */
class RS135Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var vCal */
    protected $bean = null;

    public function setup()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('timedate');
        $this->bean = new vCal();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testGetFreeBusyLinesCache()
    {
        $actual = $this->bean->get_freebusy_lines_cache($GLOBALS['current_user']);
        $this->assertEmpty($actual);
    }

    public function testCreateSugarFreeBusy()
    {
        $actual = $this->bean->create_sugar_freebusy($GLOBALS['current_user'], new SugarDateTime(), new SugarDateTime());
        $this->assertEmpty($actual);
    }

    public function testGetVcalFreeBusy()
    {
        $actual = $this->bean->get_vcal_freebusy($GLOBALS['current_user']);
        $this->assertNotEmpty($actual);
    }

    public function testCacheSugarVcal()
    {
        $actual = vCal::cache_sugar_vcal($GLOBALS['current_user']);
        $this->assertEmpty($actual);
    }

    public function testCacheSugarVcalFreeBusy()
    {
        $actual = vCal::cache_sugar_vcal_freebusy($GLOBALS['current_user']);
        $this->assertEmpty($actual);
    }

    public function testGetIcalEvent()
    {
        $meeting = new Meeting();
        $meeting->date_start = '2013-01-01 00:00:00';
        $meeting->date_end = '2013-01-01 02:00:00';
        $actual = vCal::get_ical_event($meeting, $GLOBALS['current_user']);
        $this->assertNotEmpty($actual);
    }
}
