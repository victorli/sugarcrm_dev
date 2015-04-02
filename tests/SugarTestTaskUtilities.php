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
 
require_once 'modules/Tasks/Task.php';

class SugarTestTaskUtilities
{
    private static $_createdTasks = array();

    private function __construct() {}

    public static function createTask($id = '') 
    {
        $time = mt_rand();
    	$name = 'SugarTask';
    	$email1 = 'task@sugar.com';
    	$task = new Task();
        $task->name = $name . $time;
        $task->email1 = 'task@'. $time. 'sugar.com';
        if(!empty($id))
        {
            $task->new_with_id = true;
            $task->id = $id;
        }
        $task->save();
        self::$_createdTasks[] = $task;
        return $task;
    }

    public static function setCreatedTask($task_ids) {
    	foreach($task_ids as $task_id) {
    		$task = new Task();
    		$task->id = $task_id;
        	self::$_createdTasks[] = $task;
    	} // foreach
    } // fn
    
    public static function removeAllCreatedTasks() 
    {
        $task_ids = self::getCreatedTaskIds();
        $GLOBALS['db']->query('DELETE FROM tasks WHERE id IN (\'' . implode("', '", $task_ids) . '\')');
    }
        
    public static function getCreatedTaskIds() 
    {
        $task_ids = array();
        foreach (self::$_createdTasks as $task) {
            $task_ids[] = $task->id;
        }
        return $task_ids;
    }
}
?>