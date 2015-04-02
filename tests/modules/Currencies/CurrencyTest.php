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
require_once('modules/Currencies/Currency.php');

class CurrencyTest extends Sugar_PHPUnit_Framework_TestCase {

    var $previousCurrentUser;
    var $currencyYen;
    var $currencyId = 'abc123'; // test currency_id

    /**
     * pre test setup
     */
    public function setUp() 
    {
        global $current_user;
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $this->currencyYen = SugarTestCurrencyUtilities::createCurrency('Yen', '¥', 'YEN', 78.87, $this->currencyId);
        $current_user->setPreference('number_grouping_seperator', ',', 0, 'global');
        $current_user->setPreference('decimal_seperator', '.', 0, 'global');
        $current_user->save();

        
        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
        get_number_seperators(true);
    }

    /**
     * post test teardown
     */
    public function tearDown() 
    {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        global $current_user;
        $current_user = $this->previousCurrentUser;
        $this->currencyYen = null;
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestHelper::tearDown();
        get_number_seperators(true);
    }

    /**
     * test retrieval of base currency
     *
     * @group currency
     */
    public function testCurrencyRetrieveBase()
    {
        $currency = BeanFactory::getBean('Currencies','-99');
        $this->assertInstanceOf('Currency',$currency);
        $this->assertEquals(1.0,$currency->conversion_rate);
    }

    /**
     * test retrieval of base currency with null id
     *
     * @group currency
     */
    public function testConvertToDollar()
    {
        $this->assertEquals(1.267909,$this->currencyYen->convertToDollar(100.00));
    }

    /**
     * test retrieval of base currency with null id
     *
     * @group currency
     */
    public function testConvertFromDollar()
    {
        $this->assertEquals(7887,$this->currencyYen->convertFromDollar(100.00));
    }

    /**
     * test retrieval of base currency name
     *
     * @group currency
     */
    public function testGetBaseCurrencyName()
    {
        $this->assertEquals($GLOBALS['sugar_config']['default_currency_name'],
                            $this->currencyYen->getDefaultCurrencyName());
    }

    /**
     * test retrieval of base currency symbol
     *
     * @group currency
     */
    public function testGetBaseCurrencySymbol()
    {
        $this->assertEquals($GLOBALS['sugar_config']['default_currency_symbol'],
                            $this->currencyYen->getDefaultCurrencySymbol());
    }

    /**
     * test retrieval of base currency ISO code
     *
     * @group currency
     */
    public function testGetBaseCurrencyISO()
    {
        $this->assertEquals('USD',$this->currencyYen->getDefaultISO4217());
    }

    /**
     * test retrieval of currency by symbol
     *
     * @dataProvider retrieveIdBySymbolProvider
     * @param string $expectedId
     * @param string $symbol
     * @group currency
     */
    public function testRetrieveIdBySymbol($expectedId,$symbol)
    {
        $this->assertEquals($expectedId,$this->currencyYen->retrieveIDBySymbol($symbol));
    }

    /**
     * testRetrieveIdBySymbol data provider
     *
     * @group currency
     */
    public function retrieveIdBySymbolProvider()
    {
        return array(
            array($this->currencyId,'¥'),
            array('-99',$GLOBALS['sugar_config']['default_currency_symbol']),
        );
    }

    /**
     * test retrieval of currency by ISO
     *
     * @dataProvider retrieveIdByIsoProvider
     * @param string $expectedId
     * @param string $ISO
     * @group currency
     */
    public function testRetrieveIdByIso($expectedId,$ISO)
    {
        $this->assertEquals($expectedId,$this->currencyYen->retrieveIDByISO($ISO));
    }

    /**
     * testRetrieveIdBySymbol data provider
     *
     * @group currency
     */
    public function retrieveIdByIsoProvider()
    {
        return array(
            array($this->currencyId,'YEN'),
            array('-99','USD'),
        );
    }

    /**
     * test retrieval of currency by symbol
     *
     * @dataProvider retrieveIdByNameProvider
     * @param string $expectedId
     * @param string $name
     * @group currency
     */
    public function testRetrieveIdByName($expectedId,$name)
    {
        $this->assertEquals($expectedId,$this->currencyYen->retrieveIDByName($name));
    }

    /**
     * testRetrieveIdBySymbol data provider
     *
     * @group currency
     */
    public function retrieveIdByNameProvider()
    {
        return array(
            array($this->currencyId,'Yen'),
            array('-99',$GLOBALS['sugar_config']['default_currency_name']),
        );
    }

    /**
     * test unformatting currency
     *
     * @group currency
     */

    public function testUnformatNumber()
    {
        global $current_user;
        $testValue = "$100,000.50";
        
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(100000.50, $unformattedValue, "Assert that $100,000.50 becomes 100000.50. Unformatted value is: ".$unformattedValue);
        
        //Switch the num_grp_sep and dec_sep values
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('dec_sep', ',');
        $current_user->save();

        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
        get_number_seperators(true);
        
        $testValue = "$100.000,50";
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(100000.50, $unformattedValue, "Assert that $100.000,50 becomes 100000.50. Unformatted value is: ".$unformattedValue);

        $testValue = "0.9";
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(9, $unformattedValue, "Assert that 0.9 becomes 9. Unformatted value is: ".$unformattedValue);

        $testValue = "-0.9";
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(-9, $unformattedValue, "Assert that -0.9 becomes -9. Unformatted value is: ".$unformattedValue);

        $testValue = "-3.000";
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(-3000, $unformattedValue, "Assert that -3.000 becomes -3000. Unformatted value is: ".$unformattedValue);

        $testValue = "3.000";
        $unformattedValue = unformat_number($testValue);
        $this->assertEquals(3000, $unformattedValue, "Assert that 3.000 becomes 3000. Unformatted value is: ".$unformattedValue);
    }

    /**
     * test formatting currency
     *
     * @group currency
     */

    public function testFormatNumber()
    {
        global $current_user;
        $testValue = "100000.50";
        
        $formattedValue = format_number($testValue);
        $this->assertEquals("100,000.50", $formattedValue, "Assert that 100000.50 becomes 100,000.50. Formatted value is: ".$formattedValue);
        
        //Switch the num_grp_sep and dec_sep values
        $current_user->setPreference('num_grp_sep', '.');
        $current_user->setPreference('dec_sep', ',');
        $current_user->save();

        //Force reset on dec_sep and num_grp_sep because the dec_sep and num_grp_sep values are stored as static variables
        get_number_seperators(true);       
        
        $testValue = "100000.50";
        $formattedValue = format_number($testValue);
        $this->assertEquals("100.000,50", $formattedValue, "Assert that 100000.50 becomes 100.000,50. Formatted value is: ".$formattedValue);
    }    
    
} 

?>
