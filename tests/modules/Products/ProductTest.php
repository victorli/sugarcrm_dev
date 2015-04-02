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

class ProductTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @param String $amount
     * @param String $quantity
     * @param String $discount
     * @param String $discount_select
     * @param String $likely_expected
     * @throws SugarMath_Exception
     * @dataProvider productDataProvider
     */
    public function testConvertProductToRLI($amount, $quantity, $discount, $discount_select, $likely_expected)
    {
        /* @var $product Product */
        $product = $this->getMock('Product', array('save'));

        $product->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $discount_amount = $discount;
        if ($discount_select === 1) {
            $discount_amount = SugarMath::init()->exp('(?*?)*(?/100)', array($amount, $quantity, $discount))->result();
        }

        $product->name = 'Hello World';
        $product->total_amount = SugarMath::init()->exp('((?*?)-?)', array($amount, $quantity, $discount_amount))->result();
        $product->discount_price = $amount;
        $product->quantity = $quantity;
        $product->discount_amount = $discount;
        $product->discount_select = $discount_select;
        $product->fetched_row = array();

        foreach ($product->getFieldDefinitions() as $field) {
            $product->fetched_row[$field['name']] = $product->$field['name'];
        }

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');

        $rli = $product->convertToRevenueLineItem();

        $this->assertEquals($product->revenuelineitem_id, $rli->id);
        $this->assertEquals($product->name, $rli->name);
        $this->assertEquals(
            $likely_expected,
            $rli->likely_case,
            'Likely Case Is Wrong'
        );
        // lets make sure that the discount_amount is correct
        $this->assertEquals(
            $discount_amount,
            $rli->discount_amount,
            'Discount Amount Is Wrong'
        );
    }

    /**
     * productDataProvider
     */
    public function productDataProvider()
    {
        // $amount, $quantity, $discount, $discount_select, $likely_expected
        return array(
            array('100.00', '1', '0', null, '100.00'),
            array('1000.00', '10', '0', null, '10000.00'),
            array('100.00', '10', '1', null, '999.00'),
            array('100.00', '1', '0', 1, '100.00'),
            array('100.00', '1', '10', 1, '90.00'),
            array('100.00', '2', '20', 1, '160.00'),
            array('0.13', '1000', '10', 1, '117.00'),
            array('0.25', '89765', '21456.00', null, '985.25')
        );
    }

    /**
     *
     * @dataProvider dataProviderUpdateCurrencyBaseRate
     * @param string $stage
     * @param boolean $expected
     */
    public function testUpdateCurrencyBaseRate($stage, $expected)
    {
        $product = $this->getMock('Product', array('save', 'load_relationship'));
        $product->expects($this->once())
            ->method('load_relationship')
            ->with('product_bundles')
            ->willReturn(true);

        $bundle = $this->getMock('ProductBundle', array('save', 'load_relationship'));

        $bundle->expects($this->once())
            ->method('load_relationship')
            ->with('quotes')
            ->willReturn(true);

        /* @var $quote Quote */
        $quote = $this->getMock('Quote', array('save'));

        $quote->quote_stage = $stage;

        $quote_link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $quote_link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                array(
                    $quote
                )
            );

        /* @var $product Product */
        $bundle->quotes = $quote_link2;

        $bundle_link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $bundle_link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                array(
                    $bundle
                )
            );

        /* @var $product Product */
        $product->product_bundles = $bundle_link2;

        $this->assertEquals($expected, $product->updateCurrencyBaseRate());
    }

    public function dataProviderUpdateCurrencyBaseRate()
    {
        return array(
            array('Draft', true),
            array('Negotiation', true),
            array('Delivered', true),
            array('On Hold', true),
            array('Confirmed', true),
            array('Closed Accepted', false),
            array('Closed Lost', false),
            array('Closed Dead', false)
        );
    }

    public function testUpdateCurrencyBaseRateWithNotQuoteReturnTrue()
    {
        $product = $this->getMock('Product', array('save', 'load_relationship'));
        $product->expects($this->once())
            ->method('load_relationship')
            ->with('product_bundles')
            ->willReturn(true);

        $link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getBeans'))
            ->getMock();

        $link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                array()
            );

        /* @var $product Product */
        $product->product_bundles = $link2;

        $this->assertTrue($product->updateCurrencyBaseRate());
    }
}
