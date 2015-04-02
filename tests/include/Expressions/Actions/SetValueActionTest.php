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

require_once("include/Expressions/Dependency.php");
require_once("include/Expressions/Trigger.php");
require_once("include/Expressions/Expression/Parser/Parser.php");
require_once("include/Expressions/Actions/ActionFactory.php");

class SetValueActionTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
	{
        parent::setUp();
        SugarTestHelper::setUp("current_user");
        $GLOBALS['current_user']->setPreference('datef', "Y-m-d");
        //Set the time format preference to include seconds since the test uses '2001-01-10 11:45:00' which contains seconds
        $GLOBALS['current_user']->setPreference('timef', "H:i:s");
	}

	public function testSetValues()
	{
	    $task = new Task();

        //Test Date value
        $task->date_due = '2001-01-10 11:45:00';
        $target = "date_start";
        $expr = 'addDays($date_due, -7)';
        $action = ActionFactory::getNewAction("SetValue", array("target" => $target,"value" => $expr));
        $action->fire($task);

        $this->assertEquals($task->$target, TimeDate::getInstance()->fromDb('2001-01-10 11:45:00')->get('- 7 days')->asDb());

        //Test string value
        $target = "name";
        $expr = 'concat("Hello", " ", "World")';
        $action = ActionFactory::getNewAction("SetValue", array("target" => $target,"value" => $expr));
        $action->fire($task);
        $this->assertEquals($task->$target, "Hello World");

        //Test numeric value
        $target = "name";
        $expr = 'ceiling(pi)';
        $action = ActionFactory::getNewAction("SetValue", array("target" => $target,"value" => $expr));
        $action->fire($task);
        $this->assertEquals($task->$target, 4);

	}
}
