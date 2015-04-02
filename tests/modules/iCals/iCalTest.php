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

require_once 'modules/iCals/iCal.php';

class iCalTest extends Sugar_PHPUnit_Framework_TestCase
{
    var $timedate;
    var $project;

    public function setUp()
    {
        $this->timedate = new TimeDate();

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $meeting->name = "VeryImportantMeeting";
        $meeting->date_start = $this->timedate->to_display_date_time(gmdate("Y-m-d H:i:s", mktime(12, 30, 00, date("m"), date("d")+1, date("Y"))));
        $meeting->save();
        $GLOBALS['db']->query(sprintf("INSERT INTO meetings_users (id, meeting_id, user_id, required, accept_status, date_modified, deleted) VALUES ('%s', '%s', '%s', '1', 'none', NULL, '0')", create_guid(), $meeting->id, $GLOBALS['current_user']->id));

        $task = SugarTestTaskUtilities::createTask();
        $task->assigned_user_id = $GLOBALS['current_user']->id;
        $task->name = "VeryImportantTask";
        $task->save();

        $this->project = SugarTestProjectUtilities::createProject();
        $projectId = $this->project->id;
        $projectTaskData = array (
            'project_id' => $projectId,
            'parent_task_id' => '',
            'project_task_id' => 1,
            'percent_complete' => 50,
            'name' => 'VeryImportantProjectTask'
        );
        $projectTask = SugarTestProjectTaskUtilities::createProjectTask($projectTaskData);
        $projectTask->assigned_user_id = $GLOBALS['current_user']->id;
        $projectTask->save();

    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestProjectUtilities::removeAllCreatedProjects();
        SugarTestProjectTaskUtilities::removeAllCreatedProjectTasks();
        unset($this->timedate);
        unset($this->project);
        unset($GLOBALS['current_user']);
    }

    public function testGetVcalIcal()
    {
        $iCal = new iCal();
        $iCalString = $iCal->getVcalIcal($GLOBALS['current_user'], null);

        // echo "***********BEGIN iCalString*************\n".$iCalString."***********END iCalString***************\n";

        $this->assertContains("VeryImportantMeeting", $iCalString, "Cannot find VEVENT: VeryImportantMeeting");
        $this->assertContains("VeryImportantTask", $iCalString, "Cannot find VTODO: VeryImportantTask");
        $this->assertContains("VeryImportantProjectTask", $iCalString, "Cannot find VTODO: VeryImportantProjectTask");
    }

}
