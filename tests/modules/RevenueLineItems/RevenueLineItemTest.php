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


class RevenueLineItemTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group revenuelineitems
     * @covers RevenueLineItem::convertToQuotedLineItem()
     */
    public function testConvertToQuotedLineItemWithDiscountPriceSet()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMock('RevenueLineItem', array('save'));
        $rli->likely_case = '100.00';
        $rli->discount_price = '200.00';
        $rli->sales_stage = 'Test';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals($rli->discount_price, $product->discount_price);
        $this->assertEquals($rli->id, $product->revenuelineitem_id, 'RLI to QLI Link is not Set');
        $this->assertEquals('Test', $product->sales_stage, "Product does not match RevenueLineItem");
    }

    /**
     * @group revenuelineitems
     * @covers RevenueLineItem::convertToQuotedLineItem()
     */
    public function testConvertToQuotedLineItemWithoutDiscountPriceSet()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMock('RevenueLineItem', array('save'));
        $rli->likely_case = '100.00';
        $rli->discount_price = '';
        $rli->sales_stage = 'Test';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals($rli->likely_case, $product->discount_price);
        $this->assertEquals($rli->id, $product->revenuelineitem_id, 'RLI to QLI Link is not Set');
        $this->assertEquals('Test', $product->sales_stage, "Product does not match RevenueLineItem");
    }

    public function testConvertToQuoteLineItemsSetsCorrectDiscountAmount()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMock('RevenueLineItem', array('save'));
        $rli->discount_amount = '25.00';
        $rli->quantity = '50';
        $rli->discount_price = '1.00';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals('25.00', $product->discount_amount);
    }

    public function testConvertToQuoteLineItemsSetCorrectDiscountAmountWhenPercent()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMock('RevenueLineItem', array('save'));
        $rli->discount_amount = '25.00';
        $rli->quantity = '50';
        $rli->discount_price = '1.00';
        $rli->discount_select = 1;
        $rli->deal_calc = 0.25; // (discount_amount/100)*discount_price
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals('25.00', $product->discount_amount);
    }

    /**
     * @dataProvider dataProviderSetDiscountPrice
     * @param string $likely
     * @param string $quantity
     * @param string $discount_price
     * @param string $expected_discount
     */
    public function testSetDiscountPrice($likely, $quantity, $discount_price, $expected_discount)
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMock('RevenueLineItem', array('save'));
        $rli->likely_case = $likely;
        $rli->quantity = $quantity;
        $rli->discount_price = $discount_price;

        SugarTestReflection::callProtectedMethod($rli, 'setDiscountPrice');

        $this->assertEquals($expected_discount, $rli->discount_price);
    }

    public function dataProviderSetDiscountPrice()
    {
        // values are likely, quantity, discount_price, expected_discount_price
        return array(
            array('100.00', '1', '', '100.00'),
            array('100.00', '1', '0.00', '0.00'),
            array('100.00', '1', '150', '150.00'),
        );
    }
}


