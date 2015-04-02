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

require_once('modules/Trackers/store/TrackerSessionsDatabaseStore.php');
require_once('modules/Trackers/TrackerManager.php');
require_once('include/MVC/SugarApplication.php');

/**
 *
 * Test session tracking using SugarApplication
 *
 */
class Bug50220Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $revert = array();

    public function setup()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));

        $trackerManager = TrackerManager::getInstance();
        $this->revert['disabledMonitors'] = $trackerManager->getDisabledMonitors();
        $this->revert['isPaused'] = $trackerManager->isPaused();

        $trackerManager->isPaused = false;
        $trackerManager->setDisabledMonitors(array());
    }

    public function tearDown()
    {
        global $current_user;

        $trackerManager = TrackerManager::getInstance();
        $trackerManager->setDisabledMonitors($this->revert['disabledMonitors']);
        $trackerManager->isPaused = $this->revert['isPaused'];

        $GLOBALS['db']->query("DELETE FROM tracker_sessions WHERE user_id = '{$current_user->id}'");

        SugarTestHelper::tearDown();
    }

    /**
     * testSugarApplication
     *
     * This function tests the SugarApplication file and particularly how the tracker_session table is written to
     */
    public function testSugarApplication()
    {
        global $current_user;

        SugarApplication::trackSession();

        $roundTrips = $GLOBALS['db']->getOne("SELECT round_trips FROM tracker_sessions WHERE user_id = '{$current_user->id}'");
        $this->assertEquals(1, $roundTrips, 'Failed to write to tracker_sessions from SugarApplication');
    }
}
