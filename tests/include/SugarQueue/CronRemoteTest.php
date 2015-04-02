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
require_once 'include/SugarQueue/SugarJobQueue.php';
require_once 'include/SugarQueue/SugarCronRemoteJobs.php';

class CronRemoteTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        // clean up queue
		$GLOBALS['db']->query("DELETE FROM job_queue WHERE status='queued'");
		$GLOBALS['sugar_config']['job_server'] = "http://test.job.server/";
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['sugar_config']['job_server']);
    }

    public function setUp()
    {
        $this->jq = $jobq = new SugarCronRemoteJobs();
        $this->client = new CronHttpMock();
        $this->jq->setClient($this->client);
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
    }

    public function testQueueJob()
    {
        $this->markTestIncomplete('This is not working due to bad encoding of the call_data. FRM team will fix');
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "Unit test Job";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->client->return = json_encode(array("ok" => $job->id));

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertTrue($this->jq->runOk());

        $this->assertEquals("http://test.job.server/submitJob", $this->client->call_url);
        parse_str($this->client->call_data, $qdata);
        $data = json_decode($qdata['data'], true);
        $this->assertEquals($jobid, $data['job']);
        $this->assertEquals($this->jq->getMyId(), $data['client']);
        $this->assertEquals($GLOBALS['sugar_config']['site_url'], $data['instance']);

        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, "Wrong status");
        $this->assertEquals($this->jq->getMyId(), $job->client, "Wrong client");
    }

    public function testServerFailure()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "Unit test Job";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->client->return = ''; // return nothing

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse($this->jq->runOk());
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
    }

    public function testServerFailureWithError()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = "Unit test Job";
        $job->target = "function::CronTest::cronJobFunction";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->client->return = 'This is not the server you are looking for';

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse($this->jq->runOk());
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
        $this->assertContains('This is not the server you are looking for', $job->message, "Wrong message");
    }
}

class CronHttpMock extends SugarHttpClient
{
     public $call_url;
     public $call_data;
     public $return;
     public function callRest($url, $data) {
         $this->call_url = $url;
         $this->call_data = $data;
         return $this->return;
     }
}
