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

require_once 'modules/Quotes/clients/base/api/QuoteConvertApi.php';

class QuoteConvertApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Opportunity
     */
    protected $opp;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var Link2
     */
    protected $opp_link2;

    /**
     * @var Link2
     */
    protected $quote_link2;

    public function setUp()
    {
        SugarTestHelper::setUp('app_list_strings');

        $this->opp = $this->getMockBuilder('Opportunity')
            ->setMethods(array('save', 'retrieve', 'load_relationship', 'ACLAccess'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->opp_link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('add', 'get'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = $this->getMockBuilder('Quote')
            ->setMethods(array('save', 'retrieve', 'load_relationship', 'get_linked_beans'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote_link2 = $this->getMockBuilder('Link2')
            ->setMethods(array('add', 'get', 'getBeans'))
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function tearDown()
    {
        unset($this->opp, $this->quote, $this->quote_link2, $this->opp_link2);
        SugarTestHelper::tearDown();
    }

    public function testRegisterApiRest()
    {
        $convertApi = $this->getConvertApi('UnitTest');

        $endpoints = $convertApi->registerApiRest();

        $this->assertEquals(1, count($endpoints));
        $this->assertEquals(3, count($endpoints['convert']['path']));
        $this->assertEquals('POST', $endpoints['convert']['reqType']);
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testConvertQuoteThrowsExceptionWhenNoSaveAccessToOpportunity()
    {
        $convert_api = $this->getConvertApi('UnitTest', array('requireArgs', 'loadBean'));

        $convert_api->expects($this->any())
            ->method('loadBean')
            ->will(
                $this->onConsecutiveCalls(
                    $this->quote,
                    $this->opp
                )
            );

        $this->opp->expects($this->atLeastOnce())
            ->method('ACLAccess')
            ->with('save')
            ->will($this->returnValue(false));

        $mockServiceBase = new RestService();

        $args = array(
            'targetModule' => 'Opportunity',
            'module' => 'Quotes',
            'record' => 'test_record'
        );

        $convert_api->convertQuote($mockServiceBase, $args);
    }

    /**
     * @expectedException SugarApiExceptionEditConflict
     */
    public function testConvertQuoteThrowsExceptionWhenQuoteHasOpportunity()
    {
        $convert_api = $this->getConvertApi('UnitTest', array('requireArgs', 'loadBean'));

        $convert_api->expects($this->any())
            ->method('loadBean')
            ->will(
                $this->onConsecutiveCalls(
                    $this->quote,
                    $this->opp
                )
            );

        $this->opp->expects($this->atLeastOnce())
            ->method('ACLAccess')
            ->with('save')
            ->will($this->returnValue(true));

        $this->quote->opportunities = $this->quote_link2;
        $this->quote->expects($this->once())
            ->method('load_relationship')
            ->with('opportunities');

        $this->quote_link2->expects($this->once())
            ->method('getBeans')
            ->will($this->returnValue(array('one record')));

        $mockServiceBase = new RestService();

        $args = array(
            'targetModule' => 'Opportunity',
            'module' => 'Quotes',
            'record' => 'test_record'
        );

        $convert_api->convertQuote($mockServiceBase, $args);
    }

    public function testConvertQuote()
    {
        $convert_api = $this->getConvertApi(
            'UnitTest',
            array(
                'requireArgs',
                'loadBean',
                'formatBean',
                'mapQuoteToOpportunity',
                'convertQuoteLineItemsToRevenueLineItems',
                'linkQuoteContactsToOpportunity',
                'linkQuoteContractsToOpportunity'
            )
        );

        $convert_api->expects($this->any())
            ->method('loadBean')
            ->will(
                $this->onConsecutiveCalls(
                    $this->quote,
                    $this->opp
                )
            );

        $convert_api->expects($this->once())
            ->method('mapQuoteToOpportunity');

        $convert_api->expects($this->once())
            ->method('convertQuoteLineItemsToRevenueLineItems');

        $convert_api->expects($this->once())
            ->method('linkQuoteContactsToOpportunity');

        $convert_api->expects($this->once())
            ->method('linkQuoteContractsToOpportunity');

        $convert_api->expects($this->exactly(2))
            ->method('formatBean')
            ->will(
                $this->onConsecutiveCalls(
                    array('id' => 'opp_id', '_module' => 'Opportunity'),
                    array('id' => 'quote_id', '_module' => 'Quote')
                )
            );


        $this->opp->expects($this->atLeastOnce())
            ->method('ACLAccess')
            ->with('save')
            ->will($this->returnValue(true));

        // we should call save once
        $this->opp->expects($this->once())
            ->method('save');

        $this->quote->opportunities = $this->quote_link2;
        $this->quote->expects($this->once())
            ->method('load_relationship')
            ->with('opportunities');

        $this->quote_link2->expects($this->once())
            ->method('getBeans')
            ->will($this->returnValue(array()));

        $mockServiceBase = new RestService();

        $args = array(
            'targetModule' => 'Opportunity',
            'module' => 'Quotes',
            'record' => 'test_record'
        );

        $return = $convert_api->convertQuote($mockServiceBase, $args);

        $this->assertEquals('Opportunity', $return['record']['_module']);
        $this->assertEquals('Quote', $return['related_record']['_module']);
    }

    public function testMapQuoteToOpportunity()
    {
        $values = array(
            'id' => create_guid(),
            'name' => 'UnitTest',
            'assigned_user_id' => 'TestAssignedUserId',
            'assigned_user_name' => 'TestAssignedUserName',
            'total' => '100.00',
            'currency_id' => 'CurrencyId',
            'base_rate' => '1.0',
            'billing_account_id' => 'BillingAccountId',
            'date_quote_expected_closed' => '2004-01-01'
        );

        foreach ($values as $key => $value) {
            $this->quote->$key = $value;
        }

        $quoteToOppKeyMap = array(
            'date_quote_expected_closed' => 'date_closed',
            'billing_account_id' => 'account_id',
            'id' => 'quote_id',
            'total' => 'amount',
        );

        $this->opp->expects($this->once())
            ->method('save');

        $convert_api = $this->getConvertApi('Opportunities');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'mapQuoteToOpportunity',
            array($this->quote, $this->opp)
        );

        foreach ($values as $key => $value) {
            if (isset($quoteToOppKeyMap[$key])) {
                $key = $quoteToOppKeyMap[$key];
            }

            $this->assertEquals($this->opp->$key, $value);
        }
    }

    public function testMapQuoteToOpportunityDoesNotSetOppAmountWhenNotForecastingByOpps()
    {
        $values = array(
            'id' => create_guid(),
            'name' => 'UnitTest',
            'assigned_user_id' => 'TestAssignedUserId',
            'assigned_user_name' => 'TestAssignedUserName',
            'total' => '100.00',
            'currency_id' => 'CurrencyId',
            'base_rate' => '1.0',
            'billing_account_id' => 'BillingAccountId',
            'date_quote_expected_closed' => '2004-01-01'
        );

        foreach ($values as $key => $value) {
            $this->quote->$key = $value;
        }

        $this->opp->expects($this->once())
            ->method('save');

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'mapQuoteToOpportunity',
            array($this->quote, $this->opp)
        );

        $this->assertEmpty($this->opp->amount);
    }

    public function testConvertQuoteLineItemsToRevenueLineItems()
    {
        $this->opp->revenuelineitems = $this->opp_link2;

        $this->opp_link2->expects($this->exactly(3))
            ->method('add');

        $rliMock = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(array('save'))
            ->getMock();

        $mockRliClassName = get_class($rliMock);

        $products = array();

        for ($i = 0; $i < 3; $i++) {
            $productMock = $this->getMockBuilder('Product')
                ->setMethods(array('convertToRevenueLineItem'))
                ->getMock();

            $productMock->expects($this->once())
                ->method('convertToRevenueLineItem')
                ->will($this->returnValue(new $mockRliClassName()));

            $products[] = $productMock;
        }

        $this->quote->expects($this->once())
            ->method('get_linked_beans')
            ->will(
                $this->returnValue($products)
            );

        $convert_api = $this->getConvertApi('RevenueLineItems');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'convertQuoteLineItemsToRevenueLineItems',
            array($this->quote, $this->opp)
        );
    }

    public function testConvertQuoteLineItemsToRevenueLineItemsReturnsFalseWhenForecastNotByRLI()
    {
        $convert_api = $this->getConvertApi('UnitTest');

        $return = SugarTestReflection::callProtectedMethod(
            $convert_api,
            'convertQuoteLineItemsToRevenueLineItems',
            array($this->quote, $this->opp)
        );

        $this->assertFalse($return);
    }

    public function testLinkQuoteContractsToOpportunity()
    {

        $this->opp->contracts = $this->opp_link2;

        $this->opp_link2->expects($this->exactly(2))
            ->method('add');

        $this->quote->contracts = $this->quote_link2;

        $this->quote_link2->expects($this->once())
            ->method('get')
            ->will($this->returnValue(array('id_1', 'id-2')));

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'linkQuoteContractsToOpportunity',
            array($this->quote, $this->opp)
        );
    }

    public function testLinkQuoteContractsToOpportunityDoesNotCallAddWhenNoContracts()
    {

        $this->opp->contracts = $this->opp_link2;

        $this->opp_link2->expects($this->never())
            ->method('add');

        $this->quote->contracts = $this->quote_link2;

        $this->quote_link2->expects($this->once())
            ->method('get')
            ->will($this->returnValue(array()));

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'linkQuoteContractsToOpportunity',
            array($this->quote, $this->opp)
        );
    }

    public function testLinkQuoteContactsToOpportunity()
    {

        $this->opp->contacts = $this->opp_link2;

        $this->opp->expects($this->once())
            ->method('load_relationship')
            ->with('contacts');

        $this->opp_link2->expects($this->exactly(2))
            ->method('add');

        $this->quote->shipping_contacts = $this->quote_link2;
        $this->quote->billing_contacts = $this->quote_link2;

        $this->quote_link2->expects($this->exactly(2))
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    array('id_1'),
                    array('id_2')
                )
            );

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'linkQuoteContactsToOpportunity',
            array($this->quote, $this->opp)
        );
    }

    public function testLinkQuoteContactsToOpportunityOnlyAddsOneWhenIdsAreTheSame()
    {

        $this->opp->contacts = $this->opp_link2;

        $this->opp->expects($this->once())
            ->method('load_relationship')
            ->with('contacts');

        $this->opp_link2->expects($this->once())
            ->method('add');

        $this->quote->shipping_contacts = $this->quote_link2;
        $this->quote->billing_contacts = $this->quote_link2;

        $this->quote_link2->expects($this->exactly(2))
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    array('id_1'),
                    array('id_1')
                )
            );

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'linkQuoteContactsToOpportunity',
            array($this->quote, $this->opp)
        );
    }

    public function testLinkQuoteContactsToOpportunityDoesNotAddWhenNoContacts()
    {

        $this->opp->contacts = $this->opp_link2;

        $this->opp_link2->expects($this->never())
            ->method('add');

        $this->quote->shipping_contacts = $this->quote_link2;
        $this->quote->billing_contacts = $this->quote_link2;

        $this->quote_link2->expects($this->exactly(2))
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    array(),
                    array()
                )
            );

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'linkQuoteContactsToOpportunity',
            array($this->quote, $this->opp)
        );
    }

    protected function getConvertApi($forecast_by, array $mock_methods = array())
    {

        if (!in_array('getForecastConfig', $mock_methods)) {
            $mock_methods[] = 'getForecastConfig';
        }

        $convert_api = $this->getMockBuilder('QuoteConvertApi')
            ->setMethods($mock_methods)
            ->disableOriginalConstructor()
            ->getMock();

        $convert_api->expects($this->any())
            ->method('getForecastConfig')
            ->will($this->returnValue(array('forecast_by' => $forecast_by)));

        return $convert_api;
    }
}
