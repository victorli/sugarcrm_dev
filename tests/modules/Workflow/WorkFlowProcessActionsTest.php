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
 * Copyright (C) 2004-2015 SugarCRM Inc. All rights reserved.
 */
require_once('include/workflow/action_utils.php');
require_once('modules/WorkFlow/WorkFlow.php');

class WorkFlowProcessActionsTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $quote;
    private $_wf_array;
    private $_workflow_id;

    public function setUp()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $team = SugarTestTeamUtilities::createAnonymousTeam();

        $GLOBALS['current_user'] = $user;

        // Create a workflow firing on New Quotes
        $workflow = new WorkFlow();
        $workflow->name = 'WF1261';
        $workflow->base_module = 'Quotes';
        $workflow->status = 1;
        $workflow->type = 'Normal';
        $workflow->fire_order = 'alerts_actions';
        $workflow->parent_id = null;
        $workflow->record_type = 'All';
        $workflow->save();
        $workflow->check_logic_hook_file();
        $workflow->write_workflow();
        $this->_workflow_id = $workflow->id;

        $this->quote = SugarTestQuoteUtilities::createQuote();

        $account = SugarTestAccountUtilities::createAccount();
        $this->quote->account_id = $account->id;
        $this->quote->shipping_account_id = $account->id;
        $this->quote->billing_account_id = $account->id;

        $this->quote->assigned_user_id = $user->id;
        $this->quote->save();

        $this->quote->load_relationship('teams');
        $this->quote->teams->setSaved(false);
        $this->quote->teams->add(array($team->id, $user->team_id));

        $this->_wf_array = array (
            'action_type' => 'new',
            'action_module' => 'Tasks',
            'rel_module' => '',
            'rel_module_type' => 'all',
            'basic' => array (
                'name' => 'Created from workflow',
                'status' => 'Not Started',
                'priority' => 'Medium',
            ),
            'basic_ext' => array (
            ),
            'advanced' => array (
                'team_id' => array (
                    'value' => 'team_set_id',
                    'ext1' => '',
                    'ext2' => '',
                    'ext3' => '',
                    'adv_type' => 'exist_team',
                ),
            ),
        );
    }
    public function tearDown()
    {
        rmdir_recursive('custom/modules/Quotes/workflow');
        rmdir_recursive('custom/modules/Quotes/logic_hooks.php');
        $GLOBALS['db']->query("DELETE FROM workflow WHERE id = '$this->_workflow_id'");
        $GLOBALS['db']->query("DELETE FROM workflow_schedules WHERE workflow_id = '$this->_workflow_id'");

        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }
    /**
     * Testing workflow for correct teams in Quotes for new module
     * @group 1261
     */
    public function testWorkflowsForQuotesModule()
    {
        $quote_teams = $this->quote->teams->get();
        process_workflow_actions($this->quote, $this->_wf_array);

        $this->quote->load_relationship('tasks');
        $quote_task_id = $this->quote->tasks->get();

        $task = SugarTestTaskUtilities::createTask();
        $task->retrieve(array_shift($quote_task_id));
        $task->load_relationship('teams');
        $task_teams = $task->teams->get();

        $this->assertEquals(count(array_diff($quote_teams, $task_teams)), 0, 'Team sets are different');
    }
}
?>
