<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once('include/workflow/action_utils.php');
require_once('modules/WorkFlow/WorkFlow.php');

class Pat756Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $task;

    function setUp()
    {
        $current_user = SugarTestUserUtilities::createAnonymousUser();

        $this->task = SugarTestTaskUtilities::createTask();
        $this->task->assigned_user_id = $current_user->id;
    }

    function tearDown()
    {

        SugarTestTaskUtilities::removeAllCreatedTasks();
        $GLOBALS['db']->query("DELETE FROM notes WHERE name = 'note756'", true);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        SugarTestHelper::tearDown();
    }

    function testProcessActionNewAssnUserId()
    {
        $action_array = array(
            'action_type' => 'new',
            'action_module' => 'notes',
            'rel_module' => '',
            'rel_module_type' => 'all',
            'basic' => array(
                'name' => 'note756',
            ),
            'basic_ext' => array(),
            'advanced' => array(),
        );

        process_action_new($this->task, $action_array);

        $assigned_user_id = $GLOBALS['db']->getOne("SELECT assigned_user_id FROM notes WHERE name = 'note756'", true);

        $this->assertEquals($this->task->assigned_user_id, $assigned_user_id, 'assigned_user_id does not match');
    }
}
