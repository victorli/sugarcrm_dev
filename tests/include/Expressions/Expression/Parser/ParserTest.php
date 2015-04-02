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

class ParserTest extends Sugar_PHPUnit_Framework_TestCase
{

	/**
     * @group bug39037
     */
	public function testEvaluate()
	{
        try {
        	$dep = new Dependency("test");
        	$dep->addAction(
        	   ActionFactory::getNewAction('SetValue', array('target' => 'name', 'value' => 'notAFunction(1,1)'))
        	);
        	$focus = new Account();
        	$dep->fire($focus);
        	$dep->setTrigger(new Trigger('falz'));
        	$dep->fire($focus);
        	$dep->setTrigger(new Trigger('isAlpha($notAField)'));
            $dep->fire($focus);
            //fake assert to show the test passed
            $this->assertTrue(true);
        } catch (Exception $e){
        	$this->assertTrue(false, "Parser threw exception: {$e->getMessage()}");
        }
    }

    public function testSingleArgument()
    {
        $expr = 'enum("test")';
        $result = Parser::evaluate($expr)->evaluate();
        $this->assertEquals(array("test"), $result);

        $expr = 'concat("test")';
        $result = Parser::evaluate($expr)->evaluate();
        $this->assertEquals("test", $result);
    }
}
