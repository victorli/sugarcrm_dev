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

class DBManagerFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_oldSugarConfig;

    public function setUp()
    {
        $this->_oldSugarConfig = $GLOBALS['sugar_config'];
    }

    public function tearDown()
    {
        $GLOBALS['sugar_config'] = $this->_oldSugarConfig;
    }

    public function testGetInstance()
    {
        $db = DBManagerFactory::getInstance();

        $this->assertTrue($db instanceOf DBManager,"Should return a DBManger object");
    }

    public function testGetInstanceCheckMysqlDriverChoosen()
    {
        if ( $GLOBALS['db']->dbType != 'mysql' )
            $this->markTestSkipped('Only applies to MySql');

        $db = DBManagerFactory::getInstance();

        if ( function_exists('mysqli_connect') )
            $this->assertTrue($db instanceOf MysqliManager,"Should return a MysqliManager object");
        else
            $this->assertTrue($db instanceOf MysqlManager,"Should return a MysqlManager object");
    }

    /**
     * @ticket 27781
     */
    public function testGetInstanceMssqlDefaultSelection()
    {
        if ( $GLOBALS['db']->dbType != 'mssql' )
            $this->markTestSkipped('Only applies to SQL Server');

        $GLOBALS['sugar_config']['db_mssql_force_driver'] = '';

        $db = DBManagerFactory::getInstance();

        if ( function_exists('sqlsrv_connect') )
            $this->assertTrue($db instanceOf SqlsrvManager,"Should return a SqlsrvManager object");
        elseif ( DBManagerFactory::isFreeTDS() )
            $this->assertTrue($db instanceOf FreeTDSManager,"Should return a FreeTDSManager object");
        else
            $this->assertTrue($db instanceOf MssqlManager,"Should return a MssqlManager object");
    }

    /**
     * @ticket 27781
     */
    public function testGetInstanceMssqlForceFreetdsSelection()
    {
        if ( $GLOBALS['db']->dbType != 'mssql' || !DBManagerFactory::isFreeTDS() )
            $this->markTestSkipped('Only applies to SQL Server FreeTDS');

        $GLOBALS['sugar_config']['db_mssql_force_driver'] = 'freetds';

        $db = DBManagerFactory::getInstance();

        $this->assertTrue($db instanceOf FreeTDSManager,"Should return a FreeTDSManager object");
    }

    /**
     * @ticket 27781
     */
    public function testGetInstanceMssqlForceMssqlSelection()
    {
        if ( $GLOBALS['db']->dbType != 'mssql' || !function_exists('mssql_connect') )
            $this->markTestSkipped('Only applies to SQL Server with the Native PHP mssql Driver');

        $GLOBALS['sugar_config']['db_mssql_force_driver'] = 'mssql';

        $db = DBManagerFactory::getInstance();

        if ( DBManagerFactory::isFreeTDS() )
            $this->assertTrue($db instanceOf MssqlManager,"Should return a MssqlManager object");
        elseif ( function_exists('mssql_connect') )
        $this->assertTrue($db instanceOf MssqlManager,"Should return a MssqlManager object");
        else
            $this->assertTrue($db instanceOf SqlsrvManager,"Should return a SqlsrvManager object");
    }

    /**
     * @ticket 27781
     */
    public function testGetInstanceMssqlForceSqlsrvSelection()
    {
        if ( $GLOBALS['db']->dbType != 'mssql' || !function_exists('sqlsrv_connect') )
            $this->markTestSkipped('Only applies to SQL Server');

        $GLOBALS['sugar_config']['db_mssql_force_driver'] = 'sqlsrv';

        $db = DBManagerFactory::getInstance();

        if ( DBManagerFactory::isFreeTDS() && !function_exists('sqlsrv_connect') )
            $this->assertTrue($db instanceOf FreeTDSManager,"Should return a FreeTDSManager object");
        elseif ( function_exists('mssql_connect') && !function_exists('sqlsrv_connect') )
            $this->assertTrue($db instanceOf MssqlManager,"Should return a MssqlManager object");
        else
        $this->assertTrue($db instanceOf SqlsrvManager,"Should return a SqlsrvManager object");
    }
}
