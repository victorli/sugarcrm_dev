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

class One2OneBeanRelationshipTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function testbuildJoinSugarQuery()
    {
        $relDef = array(
            'name' => 'products_revenuelineitems',
            'lhs_module' => 'Products',
            'lhs_table' => 'products',
            'lhs_key' => 'revenuelineitem_id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'id',
            'relationship_type' => 'one-to-one',
        );
        $rel = new One2OneBeanRelationship($relDef);

        /* @var $product Product */
        $product = $this->getMock('Product', array('save'));
        $product->id = 'unit_test_id';

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('getSide', 'getRelatedModuleName', 'getFocus'))
            ->disableOriginalConstructor()
            ->getMock();
        $link2->expects($this->any())
            ->method('getSide')
            ->will($this->returnValue(REL_RHS));
        $link2->expects($this->never())
            ->method('getFocus');
        $sq = new SugarQuery();
        $sq->select('id');
        $sq->from(BeanFactory::getBean('RevenueLineItems'));

        /** @var Link2 $link2 */
        $ret = $rel->buildJoinSugarQuery($link2, $sq, array('ignoreRole' => true));

        /** @var SugarQuery_Builder_Join $ret */
        $this->assertEquals('revenue_line_items', $ret->on['and']->conditions[0]->field->table);
        $this->assertEquals('id', $ret->on['and']->conditions[0]->field->field);
        $this->assertEquals('products.revenuelineitem_id', $ret->on['and']->conditions[0]->values);
    }

    /**
     * @covers One2OneBeanRelationship::getType
     */
    public function testGetType()
    {
        $relationship = $this->getMock('One2OneBeanRelationship', null, array(), '', false);

        $this->assertEquals(REL_TYPE_ONE, $relationship->getType(REL_LHS));
        $this->assertEquals(REL_TYPE_ONE, $relationship->getType(REL_RHS));
    }
}
