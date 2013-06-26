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

require_once 'modules/SchedulersJobs/SchedulersJob.php';

/**
 * Bug #56537 : Schedule Jobs don't work with classes
 *
 * @ticket 56537
 */
class Bug56573Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var string job id
     */
    protected $id;

    public function setUp()
    {
        $this->id = null;
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        if (!empty($this->id)) {
            $job = new SchedulersJob();
            $job->mark_deleted($this->id);
        }
        SugarTestHelper::tearDown();
    }

    protected function execJob($name, $data)
    {
        require_once('include/SugarQueue/SugarJobQueue.php');

        $job = new SchedulersJob();
        $job->name = "Bug56573Test Alert Job - '{$name}'";
        $job->data = $data;
        $job->target = "class::Bug56573TestJob";
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $jq = new SugarJobQueue();
        $jq->submitJob($job);
        $this->id = $job->id;
        $job->runJob();
        return $job;
    }

    public static function provider()
    {
        return array(
            array('Success', true, SchedulersJob::JOB_SUCCESS),
            array('Failure', false, SchedulersJob::JOB_FAILURE)
        );
    }

    /**
     * Job executed or failed
     * @dataProvider provider
     * @group 56537
     */
    public function testJob($name, $result, $resolution)
    {
        $job = $this->execJob($name, $result);
        $this->assertEquals($resolution, $job->resolution, "Wrong resolution");
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, "Wrong status");
    }
}

class Bug56573TestJob implements RunnableSchedulerJob
{

    public function run($data)
    {
        return $data;
    }

    public function setJob(SchedulersJob $job)
    {
        $this->job = $job;
    }
}
