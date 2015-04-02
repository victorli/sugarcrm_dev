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


class ForecastManagerWorksheetTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var Forecast
     */
    protected static $forecast;

    /**
     * @var Timeperiod
     */
    protected static $timeperiod;

    /**
     * @var User
     */
    protected static $manager;

    /**
     * @var Quota
     */
    protected static $topLevelManager_quota;

    /**
     * @var User
     */
    protected static $user;

    /**
     * @var User
     */
    protected static $topLevelManager;

    /**
     * @var Quota
     */
    protected static $user_quota;

    /**
     * @var Quota
     */
    protected static $manager_quota;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        SugarTestForecastUtilities::setUpForecastConfig();

        self::$timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

        self::$topLevelManager = SugarTestUserUtilities::createAnonymousUser();

        self::$topLevelManager_quota = SugarTestQuotaUtilities::createQuota(1000);
        self::$topLevelManager_quota->user_id = self::$topLevelManager->id;
        self::$topLevelManager_quota->quota_type = 'Direct';
        self::$topLevelManager_quota->timeperiod_id = self::$timeperiod->id;
        self::$topLevelManager_quota->save();

        $rollup_quota_manager = SugarTestQuotaUtilities::createQuota(1000);
        $rollup_quota_manager->user_id = self::$topLevelManager->id;
        $rollup_quota_manager->quota_type = 'Rollup';
        $rollup_quota_manager->timeperiod_id = self::$timeperiod->id;
        $rollup_quota_manager->save();

        self::$manager = SugarTestUserUtilities::createAnonymousUser(false);
        self::$manager->reports_to_id = self::$topLevelManager->id;
        self::$manager->save();

        self::$manager_quota = SugarTestQuotaUtilities::createQuota(1000);
        self::$manager_quota->user_id = self::$manager->id;
        self::$manager_quota->quota_type = 'Direct';
        self::$manager_quota->timeperiod_id = self::$timeperiod->id;
        self::$manager_quota->save();

        $rollup_quota = SugarTestQuotaUtilities::createQuota(2000);
        $rollup_quota->user_id = self::$manager->id;
        $rollup_quota->quota_type = 'Rollup';
        $rollup_quota->timeperiod_id = self::$timeperiod->id;
        $rollup_quota->save();


        self::$user = SugarTestUserUtilities::createAnonymousUser(false);
        self::$user->reports_to_id = self::$manager->id;
        self::$user->save();

        self::$user_quota = SugarTestQuotaUtilities::createQuota(600);
        self::$user_quota->user_id = self::$user->id;
        self::$user_quota->quota_type = 'Direct';
        self::$user_quota->timeperiod_id = self::$timeperiod->id;
        self::$user_quota->save();

        $rollup_quota_user = SugarTestQuotaUtilities::createQuota(600);
        $rollup_quota_user->user_id = self::$user->id;
        $rollup_quota_user->quota_type = 'Rollup';
        $rollup_quota_user->timeperiod_id = self::$timeperiod->id;
        $rollup_quota_user->save();

        self::$forecast = SugarTestForecastUtilities::createForecast(self::$timeperiod, self::$user);

        $GLOBALS['current_user'] = self::$manager;
    }

    public static function tearDownAfterClass()
    {
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM forecast_manager_worksheets WHERE user_id = '" . self::$user->id . "'");

        SugarTestForecastUtilities::tearDownForecastConfig();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();

        SugarTestHelper::tearDown();
    }

    /**
     * @group forecasts
     */
    public function testSaveManagerDraft()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $ret = $worksheet->reporteeForecastRollUp(self::$user, self::$forecast->toArray());

        // make sure that true was returned
        $this->assertTrue($ret);

        $ret = $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => self::$manager->id,
                'user_id' => self::$user->id,
                'draft' => 1,
                'deleted' => 0
            )
        );

        $this->assertNotNull($ret, 'User Draft Forecast Manager Worksheet Not Found');
        $this->assertEquals(self::$user->id, $worksheet->user_id);
        $this->assertEquals(self::$manager->id, $worksheet->assigned_user_id);
        $this->assertEquals(1, $worksheet->draft);

        return $worksheet;
    }

    /**
     * @depends testSaveManagerDraft
     * @group forecasts
     */
    public function testSaveManagerDraftHasCurrencyIdAndBaseRate($worksheet)
    {
        $this->assertNotEmpty($worksheet->currency_id);
        $this->assertEquals('-99', $worksheet->currency_id);
        $this->assertNotEmpty($worksheet->base_rate);
        $this->assertEquals(1, $worksheet->base_rate);
    }

    /**
     * @depends testSaveManagerDraft
     * @group forecasts
     */
    public function testSaveManagerDraftDoesNotCreateCommittedVersion()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $ret = $worksheet->reporteeForecastRollUp(self::$user, self::$forecast->toArray());

        // make sure that true was returned
        $this->assertTrue($ret);

        $ret = $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => self::$manager->id,
                'user_id' => self::$user->id,
                'draft' => 0,
                'deleted' => 0
            )
        );

        $this->assertNull($ret);
    }

    /**
     * @depends testSaveManagerDraft
     * @dataProvider caseFieldsDataProvider
     * @group forecasts
     */
    public function testAdjustedCaseValuesEqualStandardCaseValues($field, $adjusted_field)
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => self::$manager->id,
                'user_id' => self::$user->id,
                'draft' => 1,
                'deleted' => 0
            )
        );

        $this->assertEquals($worksheet->$field, $worksheet->$adjusted_field, 0, 2);
    }

    public static function caseFieldsDataProvider()
    {
        return array(
            array('likely_case', 'likely_case_adjusted'),
            array('best_case', 'best_case_adjusted'),
            array('worst_case', 'worst_case_adjusted'),
        );
    }

    /**
     * @depends testSaveManagerDraft
     * @group forecasts
     */
    public function testQuotaWasPulledFromQuotasTable()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => self::$manager->id,
                'user_id' => self::$user->id,
                'draft' => 1,
                'deleted' => 0
            )
        );

        $this->assertEquals(self::$user_quota->amount, $worksheet->quota, '', 2);
    }

    /**
     * @depends testSaveManagerDraft
     * @group forecasts
     * @return ForecastManagerWorksheet
     */
    public function testCommitManagerHasCommittedUserRow()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->commitManagerForecast(self::$manager, self::$timeperiod->id);


        $ret = $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => self::$manager->id,
                'user_id' => self::$user->id,
                'draft' => 0,
                'deleted' => 0
            )
        );

        $this->assertNotNull($ret, 'User Committed Forecast Manager Worksheet Not Found');
        $this->assertEquals(self::$user->id, $worksheet->user_id);
        $this->assertEquals(self::$manager->id, $worksheet->assigned_user_id);
        $this->assertEquals(0, $worksheet->draft);

        return $worksheet;
    }

    /**
     * @depends testCommitManagerHasCommittedUserRow
     * @group forecasts
     */
    public function testCommitRecalculatesManagerDirectQuota(ForecastManagerWorksheet $worksheet)
    {
        // get the direct quota for the manager
        /* @var $quota Quota */
        $quota = BeanFactory::getBean('Quotas');
        $quota->retrieve_by_string_fields(
            array(
                'timeperiod_id' => self::$timeperiod->id,
                'user_id' => self::$manager->id,
                'committed' => 1,
                'quota_type' => 'Direct',
                'deleted' => 0
            )
        );

        $this->assertNotEmpty($quota->amount);
        $this->assertEquals('1400.00', $quota->amount, null, 2);
    }


    /**
     * @depends testCommitManagerHasCommittedUserRow
     * @group forecasts
     */
    public function testUserCommitsUpdatesMangerDraftAndUpdatesCommittedVersion(ForecastManagerWorksheet $mgr_worksheet)
    {
        sleep(2); // we need to wait 2 seconds to get the off set that we need.
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $forecast = self::$forecast->toArray();
        $forecast['best_case'] += 100;
        $ret = $worksheet->reporteeForecastRollUp(self::$user, $forecast);

        // make sure that true was returned
        $this->assertTrue($ret);

        $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => self::$manager->id,
                'user_id' => self::$user->id,
                'draft' => 0,
                'deleted' => 0
            )
        );

        // just make sure that the best case on the committed version still equals the original value
        $this->assertEquals($forecast['best_case'], $worksheet->best_case);

        // make sure that the date_modified didn't get updated since a rep commited and not a manager
        // see ticket SFA-787
        $this->assertEquals($mgr_worksheet->date_modified, $worksheet->date_modified);
    }

    /**
     * @depends testCommitManagerHasCommittedUserRow
     * @group forecasts
     */
    public function testManagerShowHistoryLogIsTrue()
    {
        // load up the draft record for the manager
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => $GLOBALS['current_user']->id,
                'user_id' => self::$user->id,
                'draft' => 1,
                'deleted' => 0
            )
        );

        $this->assertEquals(1, $worksheet->show_history_log);
    }

    /**
     * @depends testManagerShowHistoryLogIsTrue
     * @group forecasts
     */
    public function testShowHistoryLogIsZeroWhenAdjustedColumnIsChanged()
    {
        // commit the manager
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->commitManagerForecast(self::$manager, self::$timeperiod->id);

        // change an adjust column on the draft record
        // load up the draft record for the manager
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => self::$manager->id,
                'user_id' => self::$user->id,
                'draft' => 1,
                'deleted' => 0
            )
        );

        $worksheet->likely_case_adjusted = SugarMath::init($worksheet->likely_case_adjusted)->add(100)->result();
        $worksheet->save();

        // get the draft record again
        // load up the draft record for the manager
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $worksheet->retrieve_by_string_fields(
            array(
                'assigned_user_id' => self::$manager->id,
                'user_id' => self::$user->id,
                'draft' => 1,
                'deleted' => 0
            )
        );
        // make sure that we are not showing the history log
        $this->assertEquals(0, $worksheet->show_history_log);
    }

    /**
     * @group forecasts
     */
    public function testCommitManagerForecastReturnsFalseWhenUserNotAManager()
    {
        /* @var $worksheet ForecastManagerWorksheet */
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        $return = $worksheet->commitManagerForecast(self::$user, self::$timeperiod->id);

        $this->assertFalse($return);
    }

    /**
     * @group forecasts
     */
    public function testManagerQuotaReCalcWorks()
    {
        // from the data created when the class was started, the manager had a rollup quota of 2000, direct 1000, 
        // and the user had a quota of 600, so, it should return 1400 as that is the difference
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');

        $new_mgr_quota = SugarTestReflection::callProtectedMethod(
            $worksheet,
            'recalcUserQuota',
            array(
                self::$manager->id,
                self::$timeperiod->id
            )
        );

        $this->assertEquals(1400, $new_mgr_quota, '', 2);
    }
    
    /**
     * @group forecasts
     */
    public function testManagerQuotaNoRecalc()
    {
        // from the data created when the class was started, the manager had a quota of 1000
        // and the user had a quota of 600. We are going to set the manager direct to 4000, so
        // that the total is 4600 (2600 over the Rollup of 2000).  It should NOT recalc at that point.
        $worksheet = BeanFactory::getBean('ForecastManagerWorksheets');
        self::$manager_quota->amount = 4000;
        self::$manager_quota->save();
        $new_mgr_quota = SugarTestReflection::callProtectedMethod(
            $worksheet,
            'recalcUserQuota',
            array(
                self::$manager->id,
                self::$timeperiod->id
            )
        );

        $this->assertEquals(4000, $new_mgr_quota, '', 2);
    }
}
