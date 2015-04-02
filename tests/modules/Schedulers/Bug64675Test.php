<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

/**
 * @ticket 64675
 */
class Bug64675Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('timedate');
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider providerShouldQualify()
     */
    public function testShouldQualify($schedule, $now)
    {
        // server timezone is ahead UTC
        $result = $this->qualify($schedule, $now, 'Europe/Helsinki');
        $this->assertTrue($result, 'The job has not been qualified to run');
    }

    /**
     * @dataProvider providerShouldNotQualify()
     */
    public function testShouldNotQualify($schedule, $now)
    {
        // server timezone is behind UTC
        $result = $this->qualify($schedule, $now, 'America/Los_Angeles');
        $this->assertFalse($result, 'The job has been qualified to run');
    }

    public static function providerShouldQualify()
    {
        return array(
            array(
                // schedule is "Every minute on Tuesday"
                '*::*::*::*::2',
                // now is "Tuesday, 23:30"
                '2013-01-01 23:30:00',
            ),
            array(
                // schedule is "Every minute in January"
                '*::*::*::1::*',
                // now is "January 31st, 23:30"
                '2013-01-31 23:30:00',
            ),
            array(
                // schedule is "Every minute of the 1st day of month"
                '*::*::1::*::*',
                // now is "January 1st, 23:30"
                '2013-01-01 23:30:00',
            ),
        );
    }

    public static function providerShouldNotQualify()
    {
        return array(
            array(
                // schedule is "Every minute on Tuesday"
                '*::*::*::*::2',
                // now is "Wednesday, 00:30"
                '2013-01-02 00:30:00',
            ),
            array(
                // schedule is "Every minute in January"
                '*::*::*::1::*',
                // now is "February 1st, 00:30"
                '2013-02-01 00:30:00',
            ),
            array(
                // schedule is "Every minute of the 1st day of month"
                '*::*::1::*::*',
                // now is "January 2nd, 00:30"
                '2013-01-02 00:30:00',
            ),
        );
    }

    protected function qualify($schedule, $time, $serverTimezone)
    {
        global $timedate;
        global $current_user;

        $this->iniSet('date.timezone', $serverTimezone);

        $time = $timedate->fromString($time);
        $timedate->setNow($time);

        $scheduler = new Scheduler();
        $scheduler->id = 'test';
        $scheduler->date_time_start = '2013-01-01 00:00:00';
        $scheduler->job_interval = $schedule;
        $scheduler->user = $current_user;

        return $scheduler->fireQualified();
    }
}
