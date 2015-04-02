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

/**
 * @ticket 33049
 */
class FreeTDSManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_db;
    
    public function setUp()
    {
        $this->_db = DBManagerFactory::getInstance();
        if(get_class($this->_db) != 'FreeTDSManager') {
           $this->markTestSkipped("Skipping test if not mssql configuration");
    }
    }
    
    public function testAppendnAddsNCorrectly()
    {
        $sql = $this->_db->appendN('SELECT name FROM accounts where name = ' . $this->_db->quoted('Test'));
        $this->assertEquals(
            'SELECT name FROM accounts where name = N' . $this->_db->quoted('Test'),
            $sql,
            'Assert N was added.'
        );
        
        $sql = $this->_db->appendN('SELECT name FROM accounts where name = ' . $this->_db->quoted('O\'Rielly'));
        $this->assertEquals(
            'SELECT name FROM accounts where name = N' . $this->_db->quoted('O\'Rielly'),
            $sql,
            'Assert N was added.'
        );
    }
}
