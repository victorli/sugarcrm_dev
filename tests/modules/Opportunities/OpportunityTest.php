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

class OpportunityTest extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars', '$', 'MOD', 2.0);

        SugarTestForecastUtilities::setUpForecastConfig(array(
                'sales_stage_won' => array('Closed Won'),
                'sales_stage_lost' => array('Closed Lost'),
            ));
    }

    public function tearDown()
    {
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
    }

    public function dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty()
    {
        return array(array('best_case'), array('worst_case'));
    }

    /**
     * @dataProvider dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty
     * @group opportunities
     */
    public function testCaseFieldEqualsAmountWhenCaseFieldEmpty($case)
    {
        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->revenuelineitems->add($rli);
        $rli->$case = '';
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity->save();
        $this->assertEquals($opportunity->$case, $opportunity->amount);
    }


    /**
     * @dataProvider dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty
     * @group opportunities
     */
    public function testCaseFieldEqualsZeroWhenCaseFieldSetToZero($case)
    {
        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->revenuelineitems->add($rli);
        $opportunity->sales_stage = "Prospecting";
        $rli->$case = $rli->likely_case = 0;
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity->$case = 0;
        $opportunity->save();
        $this->assertEquals(0, $opportunity->$case);
    }

    /**
     * Test that the base_rate field is populated with rate of currency_id
     * @group forecasts
     * @group opportunities
     */
    public function testCurrencyRate()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = "Test Opportunity Delete Me";
        $opportunity->amount = "5000.00";
        $opportunity->date_closed = TimeDate::getInstance()->getNow()->modify("+10 days")->asDbDate();
        $opportunity->best_case = "1000.00";
        $opportunity->worst_case = "600.00";
        $opportunity->save();
        $this->assertEquals(
            sprintf('%.6f', $opportunity->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }

    /**
     * Test that base currency exchange rates from EUR are working properly.
     * @group forecasts
     * @group opportunities
     */
    public function testBaseCurrencyAmounts()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = "Test Opportunity Delete Me";
        $opportunity->amount = "5000.00";
        $opportunity->date_closed = TimeDate::getInstance()->getNow()->modify("+10 days")->asDbDate();
        $opportunity->best_case = "1000.00";
        $opportunity->worst_case = "600.00";
        $opportunity->save();

        $this->assertEquals(
            sprintf('%.6f', $opportunity->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }

    

    /**
     * @dataProvider dataProviderMapProbabilityFromSalesStage
     * @group opportunities
     */
    public function testMapProbabilityFromSalesStage($sales_stage, $probability)
    {
        /* @var $oppMock Opportunity */
        $oppMock = $this->getMock('Opportunity', array('save'));
        $oppMock->sales_stage = $sales_stage;
        // use the Reflection Helper to call the Protected Method
        SugarTestReflection::callProtectedMethod($oppMock, 'mapProbabilityFromSalesStage');

        $this->assertEquals($probability, $oppMock->probability);
    }

    /**
     * Test that related RLI's Account is always updated when we change it in Opportunity.
     * @group opportunities
     */
    public function testRelatedRLIUpdatesAccountChange()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $account_1 = SugarTestAccountUtilities::createAccount();
        $opportunity->account_id = $account_1->id;
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opportunity->revenuelineitems->add($rli);

        $opportunity->save();
        $this->assertEquals($account_1->id, $rli->account_id, '1st save');

        //let's change Opportunity's Account and see what happens with RLI's related Account
        $opportunity->retrieve($opportunity->id);
        $account_2 = SugarTestAccountUtilities::createAccount();
        $opportunity->account_id = $account_2->id;
        $opportunity->save();
        $this->assertEquals($account_2->id, $rli->account_id, '2nd save');
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
}

class MockOpportunityBean extends Opportunity
{
    
}
