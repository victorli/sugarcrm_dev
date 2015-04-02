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


require_once 'modules/ProjectTask/ProjectTask.php';
class SugarTestProjectTaskUtilities extends SugarTestObjectUtilities
{
	public static $tableName = "project_task";

	private function __construct()
	{

	}

	public static function createProjectTask($projectTaskData)
	{
		try
		{
			$projectTask = new ProjectTask();
			$projectTask->project_id = $projectTaskData['project_id'];
			$projectTask->parent_task_id = $projectTaskData['parent_task_id'];
			$projectTask->project_task_id = $projectTaskData['project_task_id'];
			$projectTask->percent_complete = $projectTaskData['percent_complete'];
			$projectTask->name = $projectTaskData['name'];

            if (isset($projectTaskData['duration']))
            {
                $projectTask->duration = $projectTaskData['duration'];
            }

            if (isset($projectTaskData['duration_unit']))
            {
                $projectTask->duration_unit = $projectTaskData['duration_unit'];
            }

			$projectTask->save();
			self::pushObject($projectTask);
			return $projectTask;
		}
		catch (Exception $ex)
		{
			die("Not all needed params were defined for new project task");
		}
	}

	public static function pushProject($project)
	{
		parent::pushObject($project);
	}

	public static function removeAllCreatedProjectTasks()
	{
		parent::removeAllCreatedObjects(self::$tableName);
	}
}
