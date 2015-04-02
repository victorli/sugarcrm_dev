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

class RevenueLineItemsTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var RevenueLineItem
     */
    private $revenuelineitem;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('RevenueLineItems'));
    }

    public function setUp()
    {
        SugarTestForecastUtilities::setUpForecastConfig(array(
                'forecast_by' => 'RevenueLineItems'
            )
        );
        SugarTestConfigUtilities::setConfig('Opportunities', 'opps_view_by', 'RevenueLineItems');
        parent::setUp();
        $this->revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
    }

    public function tearDown()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestConfigUtilities::setConfig('Opportunities', 'opps_view_by', 'Opportunities');
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestProductTemplatesUtilities::removeAllCreatedProductTemplate();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        parent::tearDown();
    }

    /**
     * This test checks to see that we can save a revenuelineitem where date_closed is set to null
     *
     * @group revenuelineitems
     */
    public function testCreateRevenueLineItemWithoutDateClosed()
    {
        $this->revenuelineitem->date_closed = null;
        $this->revenuelineitem->save();
        $this->assertEmpty($this->revenuelineitem->date_closed);
    }

    /**
     * @group revenuelineitems
     *
     * Test that the account_id in RevenueLineItem instance is properly set for a given Opportunity id.  I am
     * currently creating Opportunities with new Opportunity() because the test helper for Opportunities
     * creates accounts automatically.
     */
    public function testSetAccountForOpportunity()
    {
        //creating Opportunities with BeanFactory because the test helper for Opportunities
        // creates accounts automatically.
        $opp = BeanFactory::newBean("Opportunities");
        $opp->name = "opp1";
        $opp->date_closed = date('Y-m-d');
        $opp->save();
        $opp->load_relationship('accounts');
        SugarTestOpportunityUtilities::setCreatedOpportunity(array($opp->id));
        $account = SugarTestAccountUtilities::createAccount();
        $opp->accounts->add($account);
        $revenuelineitem = new MockRevenueLineItem();
        $this->assertTrue($revenuelineitem->setAccountIdForOpportunity($opp->id));

        //creating Opportunities with BeanFactory because the test helper for Opportunities
        // creates accounts automatically.
        $opp2 = BeanFactory::newBean("Opportunities");
        $opp2->name = "opp2";
        $opp2->date_closed = date('Y-m-d');
        $opp2->save();
        SugarTestOpportunityUtilities::setCreatedOpportunity(array($opp2->id));
        $revenuelineitem2 = new MockRevenueLineItem();
        $this->assertFalse($revenuelineitem2->setAccountIdForOpportunity($opp2->id));
    }



    /**
     * @group revenuelineitems
     */
    public function testRevenueLineItemTemplateSetsRevenueLineItemFields()
    {

        $pt_values = array(
            'mft_part_num' => 'unittest',
            'list_price' => '800',
            'cost_price' => '400',
            'discount_price' => '700',
            'list_usdollar' => '800',
            'cost_usdollar' => '400',
            'discount_usdollar' => '700',
            'tax_class' => 'Taxable',
            'weight' => '100'
        );

        $pt = SugarTestProductTemplatesUtilities::createProductTemplate('', $pt_values);

        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->product_template_id = $pt->id;

        SugarTestReflection::callProtectedMethod($revenuelineitem, 'mapFieldsFromProductTemplate');

        foreach ($pt_values as $field => $value) {
            $this->assertEquals($value, $revenuelineitem->$field);
        }

    }

    /**
     * @group revenuelineitems
     */
    public function testRevenueLineItemTemplateSetsRevenueLineItemFieldsWithCurrencyConversion()
    {
        SugarTestCurrencyUtilities::createCurrency('Yen','¥','YEN',78.87,'currency-yen');
        $pt_values = array(
            'mft_part_num' => 'unittest',
            'list_price' => '800',
            'cost_price' => '400',
            'discount_price' => '700',
            'list_usdollar' => '800',
            'cost_usdollar' => '400',
            'discount_usdollar' => '700',
            'tax_class' => 'Taxable',
            'weight' => '100',
            'currency_id' => '-99'
        );

        $pt = SugarTestProductTemplatesUtilities::createProductTemplate('', $pt_values);

        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->product_template_id = $pt->id;
        $revenuelineitem->currency_id = 'currency-yen';

        SugarTestReflection::callProtectedMethod($revenuelineitem, 'mapFieldsFromProductTemplate');

        $this->assertEquals(SugarCurrency::convertAmount(800, '-99', 'currency-yen'), $revenuelineitem->list_price);
        $this->assertEquals(SugarCurrency::convertAmount(400, '-99', 'currency-yen'), $revenuelineitem->cost_price);
        $this->assertEquals(SugarCurrency::convertAmount(700, '-99', 'currency-yen'), $revenuelineitem->discount_price);

        // remove test currencies
    }

    /**
     * @group revenuelineitems
     */
    public function testBestCaseAutofillEmpty()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->best_case = '';
        $revenuelineitem->save();

        $this->assertEquals($revenuelineitem->likely_case, $revenuelineitem->best_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testBestCaseAutofillNull()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->best_case = null;
        $revenuelineitem->save();

        $this->assertEquals($revenuelineitem->likely_case, $revenuelineitem->best_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testBestCaseAutoRegression()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->best_case = 42;
        $revenuelineitem->save();

        $this->assertEquals(42, $revenuelineitem->best_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testWorstCaseAutofillEmpty()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->worst_case = '';
        $revenuelineitem->save();

        $this->assertEquals($revenuelineitem->likely_case, $revenuelineitem->worst_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testWorstCaseAutofillNull()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->worst_case = null;
        $revenuelineitem->save();

        $this->assertEquals($revenuelineitem->likely_case, $revenuelineitem->worst_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testWorstCaseAutofillRegression()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->likely_case = 10000;
        $revenuelineitem->worst_case = 42;
        $revenuelineitem->save();

        $this->assertEquals(42, $revenuelineitem->worst_case);
    }

    /**
     * @group revenuelineitems
     */
    public function testEmptyQuantityDefaulted()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();

        $revenuelineitem->quantity = "";
        $revenuelineitem->save();
        $this->assertEquals(1, $revenuelineitem->quantity, "Empty string not converted to 1");
    }

    /**
     * @group revenuelineitems
     */
    public function testNullQuantityDefaulted()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();

        $revenuelineitem->quantity = null;
        $revenuelineitem->save();
        $this->assertEquals(1, $revenuelineitem->quantity, "Null not converted to 1");
    }

    /**
     * @group revenuelineitems
     */
    public function testQuantityNotDefaulted()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();

        $revenuelineitem->quantity = 42;
        $revenuelineitem->save();
        $this->assertEquals(42, $revenuelineitem->quantity, "Null not converted to 1");
    }

    
    /**
     * @dataProvider dataProviderMapProbabilityFromSalesStage
     * @group revenuelineitems
     */
    public function testMapProbabilityFromSalesStage($sales_stage, $probability)
    {
        $revenuelineitem = new MockRevenueLineItem();
        $revenuelineitem->sales_stage = $sales_stage;
        // use the Reflection Helper to call the Protected Method
        SugarTestReflection::callProtectedMethod($revenuelineitem, 'mapProbabilityFromSalesStage');

        $this->assertEquals($probability, $revenuelineitem->probability);
    }

    public static function dataProviderMapProbabilityFromSalesStage()
    {
        return array(
            array('Prospecting', '10'),
            array('Qualification', '20'),
            array('Needs Analysis', '25'),
            array('Value Proposition', '30'),
            array('Id. Decision Makers', '40'),
            array('Perception Analysis', '50'),
            array('Proposal/Price Quote', '65'),
            array('Negotiation/Review', '80'),
            array('Closed Won', '100'),
            array('Closed Lost', '0')
        );
    }


    /**
     * @group revenuelineitems
     * @group currency
     * @ticket SFA-745
     */
    public function testRevenueLineItemSaveSetsCurrencyBaseRate()
    {
        $currency = SugarTestCurrencyUtilities::createCurrency('Philippines', '₱', 'PHP', 41.82982, 'currency-php');

        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->currency_id = $currency->id;
        $revenuelineitem->save();

        $this->assertEquals($currency->id, $revenuelineitem->currency_id);
        $this->assertEquals($currency->conversion_rate, $revenuelineitem->base_rate);

    }

    /**
     * @group revenuelineitems
     * @ticket SFA-511
     */
    public function testMapFieldsFromOpportunity()
    {
        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $revenuelineitem->opportunity_id = $opp->id;
        $opp->opportunity_type = 'new';
        $revenuelineitem->save();
        $this->assertEquals('new', $revenuelineitem->product_type);
    }

    /**
     * @group revenuelineitems
     *
     * Test that RLI converted to quote uses product name.
     */
    public function testRevenueLineItemQuoteName()
    {

        $pt_values = array(
            'name' => 'foobar',
            'mft_part_num' => 'unittest',
            'list_price' => '800',
            'cost_price' => '400',
            'discount_price' => '700',
            'list_usdollar' => '800',
            'cost_usdollar' => '400',
            'discount_usdollar' => '700',
            'tax_class' => 'Taxable',
            'weight' => '100'
        );

        $pt = SugarTestProductTemplatesUtilities::createProductTemplate('', $pt_values);

        $revenuelineitem = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $revenuelineitem->product_template_id = $pt->id;

        $product = $revenuelineitem->convertToQuotedLineItem();

        $this->assertEquals($product->name, $pt->name);

    }

}

class MockRevenueLineItem extends RevenueLineItem
{

    public function setAccountIdForOpportunity($oppId)
    {
        return parent::setAccountIdForOpportunity($oppId);
    }
}
