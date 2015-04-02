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

require_once 'include/database/DBManagerFactory.php';

class Bug34547Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $_has_mysqli_disabled;
    private $_db;
    private $_db_manager;

    public function setUp()
    {
        if ( !function_exists('mysql_connect') || !function_exists('mysqli_connect'))
            $this->markTestSkipped('Test requires mysql and mysqli extensions enabled.');

        if (version_compare(phpversion(), '5.5.0', '>=')) {
            $this->markTestSkipped('The mysql extension is deprecated since php 5.5.');
        }

        $this->_db = DBManagerFactory::getInstance();
        if(get_class($this->_db) != 'MysqlManager' && get_class($this->_db) != 'MysqliManager') {
            $this->markTestSkipped("Skipping test if not mysql or mysqli configuration");
        }

        unset($GLOBALS['dbinstances']);
        $this->_db_manager = $GLOBALS['sugar_config']['dbconfig']['db_manager'];
        unset($GLOBALS['sugar_config']['dbconfig']['db_manager']);
        $this->_has_mysqli_disabled = (!empty($GLOBALS['sugar_config']['mysqli_disabled']) && $GLOBALS['sugar_config']['mysqli_disabled'] === TRUE);
        if(!$this->_has_mysqli_disabled) {
            $GLOBALS['sugar_config']['mysqli_disabled'] = TRUE;
        }
        DBManagerFactory::disconnectAll();
    }

    public function tearDown()
    {
        if(!$this->_has_mysqli_disabled) {
           unset($GLOBALS['sugar_config']['mysqli_disabled']);
        }
        $GLOBALS['sugar_config']['dbconfig']['db_manager'] = $this->_db_manager;
        unset($GLOBALS['dbinstances']);
        DBManagerFactory::disconnectAll();
    }

    public function testMysqliDisabledInGetInstance()
    {
        $this->_db = DBManagerFactory::getInstance();
        $this->assertEquals('MysqlManager', get_class($this->_db), "Assert that MysqliManager is not disabled");
    }

}