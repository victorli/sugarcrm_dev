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


require_once("modules/ForecastManagerWorksheets/clients/base/api/ForecastManagerWorksheetsFilterApi.php");

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 */
class ForecastManagerWorksheetsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private static $reportee;

    /**
     * @var array
     */
    private static $reportee2;

    /**
     * @var array
     */
    protected static $manager;

    /**
     * @var array
     */
    protected static $manager2;

    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var array
     */
    protected static $managerData;

    /**
     * @var array
     */
    protected static $managerData2;

    /**
     * @var array
     */
    protected static $repData;

    /**
     * @var Administration
     */
    protected static $admin;

    /**
     * @var ForecastManagerWorksheetsFilterApi
     */
    protected $filterApi;

    /**
     * @var ForecastsWorksheetApi
     */
    protected $putApi;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        self::$manager = SugarTestForecastUtilities::createForecastUser(
            array(
                'opportunities' => array(
                    'total' => 5,
                    'include_in_forecast' => 5
                ),
            )
        );

        //set up another manager, and assign him to the first manager manually so his data is generated
        //correctly.
        self::$manager2 = SugarTestForecastUtilities::createForecastUser(
            array(
                'opportunities' => array(
                    'total' => 5,
                    'include_in_forecast' => 5
                ),
            )
        );

        self::$manager2["user"]->reports_to_id = self::$manager['user']->id;
        self::$manager2["user"]->save();

        self::$reportee = SugarTestForecastUtilities::createForecastUser(
            array(
                'user' => array(
                    'reports_to' => self::$manager['user']->id
                ),
                'opportunities' => array(
                    'total' => 5,
                    'include_in_forecast' => 5
                )
            )
        );
        self::$reportee2 = SugarTestForecastUtilities::createForecastUser(
            array(
                'user' => array(
                    'reports_to' => self::$manager2['user']->id
                ),
                'opportunities' => array(
                    'total' => 5,
                    'include_in_forecast' => 5
                )
            )
        );

        self::$timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        self::$managerData = array(
            "amount" => self::$manager['opportunities_total'],
            "quota" => self::$manager['quota']->amount,
            "quota_id" => self::$manager['quota']->id,
            "best_case" => self::$manager['forecast']->best_case,
            "likely_case" => self::$manager['forecast']->likely_case,
            "worst_case" => self::$manager['forecast']->worst_case,
            "best_adjusted" => self::$manager['worksheet']->best_case,
            "likely_adjusted" => self::$manager['worksheet']->likely_case,
            "worst_adjusted" => self::$manager['worksheet']->worst_case,
            "commit_stage" => self::$manager['worksheet']->commit_stage,
            "forecast_id" => self::$manager['forecast']->id,
            "worksheet_id" => self::$manager['worksheet']->id,
            "show_opps" => true,
            "id" => self::$manager['user']->id,
            "name" => 'Opportunities (' . self::$manager['user']->first_name . ' ' . self::$manager['user']->last_name . ')',
            "user_id" => self::$manager['user']->id,

        );

        self::$managerData2 = array(
            "amount" => self::$manager2['opportunities_total'],
            "quota" => self::$manager2['quota']->amount,
            "quota_id" => self::$manager2['quota']->id,
            "best_case" => self::$manager2['forecast']->best_case,
            "likely_case" => self::$manager2['forecast']->likely_case,
            "worst_case" => self::$manager2['forecast']->worst_case,
            "best_adjusted" => self::$manager2['worksheet']->best_case,
            "likely_adjusted" => self::$manager2['worksheet']->likely_case,
            "worst_adjusted" => self::$manager2['worksheet']->worst_case,
            "commit_stage" => self::$manager2['worksheet']->commit_stage,
            "forecast_id" => self::$manager2['forecast']->id,
            "worksheet_id" => self::$manager2['worksheet']->id,
            "show_opps" => true,
            "id" => self::$manager2['user']->id,
            "name" => 'Opportunities (' . self::$manager2['user']->first_name . ' ' . self::$manager2['user']->last_name . ')',
            "user_id" => self::$manager2['user']->id,

        );

        self::$repData = array(
            "amount" => self::$reportee['opportunities_total'],
            "quota" => self::$reportee['quota']->amount,
            "quota_id" => self::$reportee['quota']->id,
            "best_case" => self::$reportee['forecast']->best_case,
            "likely_case" => self::$reportee['forecast']->likely_case,
            "worst_case" => self::$reportee['forecast']->worst_case,
            "best_adjusted" => self::$reportee['worksheet']->best_case,
            "likely_adjusted" => self::$reportee['worksheet']->likely_case,
            "worst_adjusted" => self::$reportee['worksheet']->worst_case,
            "commit_stage" => self::$reportee['worksheet']->commit_stage,
            "forecast_id" => self::$reportee['forecast']->id,
            "worksheet_id" => self::$reportee['worksheet']->id,
            "show_opps" => true,
            "id" => self::$reportee['user']->id,
            "name" => self::$reportee['user']->first_name . ' ' . self::$reportee['user']->last_name,
            "user_id" => self::$reportee['user']->id,

        );


        SugarTestForecastUtilities::setUpForecastConfig(array(
                'show_worksheet_worst' => 1
            ));
    }

    public function setUp()
    {
        //Create an anonymous user for login purposes/
        $this->_user = self::$manager['user'];
        $this->_oldUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->_user;

        $this->filterApi = new ForecastManagerWorksheetsFilterApi();
        //$this->putApi = new ForecastsWorksheetApi();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        parent::tearDown();
    }

    public function tearDown()
    {
        $this->filterApi = null;
        $GLOBALS["current_user"] = null;
        // override since we want to do this after the class is done
    }

    /**
     * This test asserts that we get back data.
     *
     * @group forecastapi
     * @group forecasts
     */
    public function testPassedInUserIsManager()
    {
        $GLOBALS["current_user"] = self::$manager["user"];

        $response = $this->filterApi->forecastManagerWorksheetsGet(
            SugarTestRestUtilities::getRestServiceMock(self::$manager['user']),
            array(
                'user_id' => self::$manager['user']->id,
                'timeperiod_id' => self::$timeperiod->id,
                'module' => 'ForecastManagerWorksheets'
            )
        );

        $this->assertNotEmpty($response["records"], "Rest reply is empty. User Is Not A Manager.");
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     * @group forecastapi
     * @group forecasts
     */
    public function testPassedInUserIsNotManagerReturnsEmpty()
    {
        $GLOBALS["current_user"] = self::$reportee["user"];

        $this->filterApi->forecastManagerWorksheetsGet(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee['user']),
            array(
                'user_id' => self::$reportee['user']->id,
                'timeperiod_id' => self::$timeperiod->id,
                'module' => 'ForecastManagerWorksheets'
            )
        );
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     * @group forecastapi
     * @group forecasts
     */
    public function testCurrentUserIsNotManagerReturnsEmpty()
    {
        $GLOBALS['current_user'] = self::$reportee['user'];

        $this->filterApi->forecastManagerWorksheetsGet(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee['user']),
            array(
                'timeperiod_id' => self::$timeperiod->id,
                'module' => 'ForecastManagerWorksheets'
            )
        );
    }
}

class ForecastManagerWorksheetApiServiceMock extends RestService
{
    public function execute()
    {
    }

    protected function handleException(Exception $exception)
    {
    }
}
