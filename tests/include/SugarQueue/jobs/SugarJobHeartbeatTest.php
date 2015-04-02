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

require_once 'include/SugarQueue/jobs/SugarJobHeartbeat.php';

/**
 * Class SugarJobHeartbeatTest
 * @group BR-1722
 */
class SugarJobHeartbeatTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarJobHeartbeat
     */
    protected $job;

    protected function setUp()
    {
        $this->job = $this->getMockBuilder('SugarJobHeartbeat')
            ->setMethods(array('sendHeartbeat', 'getSystemInfo'))
            ->getMock();
    }

    public function testRunFailsOnBadHeartbeatResponse()
    {
        $this->mockStuff();
        $schedulersJob = $this->getMock('SchedulersJob');
        $schedulersJob->expects($this->once())
            ->method("failJob");

        $this->job->setJob($schedulersJob);
        $this->job->expects($this->once())
            ->method('sendHeartbeat')
            ->will($this->returnValue(false));

        $this->assertEquals(false, $this->job->run(null));
    }

    public function testRunSuccessOnHeartbeatSuccess()
    {
        $this->mockStuff();
        $schedulersJob = $this->getMock('SchedulersJob');
        $schedulersJob->expects($this->once())
            ->method("succeedJob");

        $this->job->setJob($schedulersJob);
        $this->job->expects($this->once())
            ->method('sendHeartbeat')
            ->will($this->returnValue(true));

        $this->assertEquals(true, $this->job->run(null));
    }

    protected function mockStuff()
    {
        $systemInfo = $this->getMockBuilder('SugarSystemInfo')
            ->disableOriginalConstructor()
            ->setMethods(array('getInfo', 'getActiveUsersXDaysCount'))
            ->getMock();

        $systemInfo->expects($this->once())
            ->method('getActiveUsersXDaysCount')
            ->will($this->returnValue(0));

        $this->job->expects($this->once())
            ->method('getSystemInfo')
            ->will($this->returnValue($systemInfo));
    }
}
