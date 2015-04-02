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

require_once('modules/RevenueLineItems/RevenueLineItemHooks.php');
class RevenueLineItemHooksTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var RevenueLineItem
     */
    protected $rli;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');

        $this->rli = $this->getMock('RevenueLineItem', array('save'));
    }

    public function tearDown()
    {
        $this->rli = null;
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @covers RevenueLineItemHooks::afterRelationshipDelete
     * @dataProvider dataAfterRelationshipDelete
     */
    public function testAfterRelationshipDelete($event, $link, $result)
    {
        $hook = new RevenueLineItemHooks();
        $ret = $hook->afterRelationshipDelete($this->rli, $event, $link);
        $this->assertEquals($result, $ret);
        
    }

    public function dataAfterRelationshipDelete()
    {
        return array(
            array('after_relationship_delete', array('link' => 'account_link'), true),
            array('after_relationship_delete', array('link' => 'foo'), false),
            array('foo', array('link' => 'account_link'), false),
            array('foo', array('link' => 'foo'), false ),
        );
    }
}
