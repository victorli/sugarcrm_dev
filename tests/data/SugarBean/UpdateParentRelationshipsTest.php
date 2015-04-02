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

require_once 'data/Relationships/One2MBeanRelationship.php';

class UpdateParentRelationshipsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**#@+
     * @var Account
     */
    private static $account1;
    private static $account2;
    /**#@-*/

    /**
     * @var Call
     */
    private static $call;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');

        self::$account1 = SugarTestAccountUtilities::createAccount();
        self::$account2 = SugarTestAccountUtilities::createAccount();
        self::$call = SugarTestCallUtilities::createCall();

        // link call to account
        self::$call->load_relationship('accounts');
        self::$call->accounts->add(self::$account1);
    }

    public static function tearDownAfterClass()
    {
        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testUpdateParentRelationships()
    {
        /** @var Call $call */
        $call = BeanFactory::getBean('Calls', self::$call->id, array(
            'use_cache' => false,
        ));

        $call->load_relationship('accounts');
        $def = SugarRelationshipFactory::getInstance()->getRelationshipDef('account_calls');

        $relationship = $this->getMockBuilder('One2MBeanRelationship')
            ->setConstructorArgs(array($def))
            ->setMethods(array('callAfterAdd', 'callAfterDelete'))
            ->getMock();

        $linked = $unlinked = array();
        $this->collectInvocations($relationship, 'callAfterAdd', $linked);
        $this->collectInvocations($relationship, 'callAfterDelete', $unlinked);

        SugarTestReflection::setProtectedValue($call->accounts, 'relationship', $relationship);

        // link call to another account
        $call->parent_id = self::$account2->id;
        $call->save();

        // make sure unlink from old account is tracked from both sides
        $this->assertContains(array(
            self::$call->id,
            self::$account1->id,
            'accounts',
        ), $unlinked);

        $this->assertContains(array(
            self::$account1->id,
            self::$call->id,
            'calls',
        ), $unlinked);

        // make sure link to new account is tracked from both sides
        $this->assertContains(array(
            self::$call->id,
            self::$account2->id,
            'accounts',
        ), $linked);

        $this->assertContains(array(
            self::$account2->id,
            self::$call->id,
            'calls',
        ), $linked);
    }

    private function collectInvocations(PHPUnit_Framework_MockObject_MockObject $mock, $method, &$result)
    {
        $mock->expects($this->any())
            ->method($method)
            ->will($this->returnCallback(function (SugarBean $focus, SugarBean $related, $link) use (&$result) {
                $result[] = array($focus->id, $related->id, $link);
            }));
    }
}
