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
require_once('modules/Trackers/Tracker.php');
require_once('modules/Trackers/TrackerPerf.php');
require_once('modules/Trackers/TrackerQuery.php');
require_once('modules/Trackers/TrackerSession.php');

require_once 'include/api/SugarApi.php';
require_once 'include/api/RestService.php';
require_once 'clients/base/api/CurrentUserApi.php';

class TrackersTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $service;

    private $files = array(
        "tracker_perfvardefs.php",
        "tracker_queriesvardefs.php",
        "tracker_sessionsvardefs.php",
        "Trackervardefs.php",
    );

    public function setUp()
    {
        $GLOBALS['reload_vardefs'] = true;
        SugarCache::$isCacheReset = false;

        SugarTestHelper::setUp("app_list_strings");

        $this->currentUserApi = new CurrentUserApi();
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        foreach ($this->files as $file) {
            $filepath = sugar_cached("modules/Trackers/" . $file);
            @unlink($filepath);
        }
    }

    public function testCacheRewriteAPI()
    {
        $timestamp = array();

        // Store cached file timestamps
        foreach ($this->files as $file) {
            $filepath = sugar_cached("modules/Trackers/" . $file);
            touch($filepath, 123);
            $this->assertFileExists($filepath, "Cache file '$file' does not exist.");
            $timestamp[$file] = filemtime($filepath);
        }

        // Call to /me
        $this->currentUserApi->retrieveCurrentUser($this->service, array());

        // Check if cache is re-created
        foreach ($this->files as $file) {
            $filepath = sugar_cached("modules/Trackers/" . $file);
            $this->assertFileExists($filepath, "Cache file '$file' does not exist after REST API call.");
            // file does not have correct timestamp
            $this->assertEquals(
                $timestamp[$file],
                filemtime($filepath),
                "File '$file' is re-created on API call."
            );
        }
    }

    public function testCacheRewriteClassInstantiation()
    {
        $timestamp = array();

        // Store cached file timestamps
        foreach ($this->files as $file) {
            $filepath = sugar_cached("modules/Trackers/" . $file);
            touch($filepath, 123);
            $this->assertFileExists($filepath, "Cache file '$file' does not exist.");
            $timestamp[$file] = filemtime($filepath);
        }

        // Instantiate classes
        unset($GLOBALS['dictionary']);
        new Tracker();
        new TrackerPerf();
        new TrackerQuery();
        new TrackerSession();

        // Check if cache is re-created
        foreach ($this->files as $file) {
            $filepath = sugar_cached("modules/Trackers/" . $file);
            $this->assertFileExists($filepath, "Cache file '$file' does not exist after second instantiation.");
            // file does not have correct timestamp
            $this->assertEquals(
                $timestamp[$file],
                filemtime($filepath),
                "File '$file' is re-created on second instantiation."
            );
        }
    }
}
