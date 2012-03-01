<?php

/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2012 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


require_once('data/SugarBean.php');

class SugarBeanTest extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public static function tearDownAfterClass()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}

    public function testGetObjectName(){
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->getObjectName(), 'my_table', "SugarBean->getObjectName() is not returning the table name when object_name is empty.");
    }

    public function testGetAuditTableName(){
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->get_audit_table_name(), 'my_table_audit', "SugarBean->get_audit_table_name() is not returning the correct audit table name.");
    }
    
    /**
     * @ticket 47261
     */
    public function testGetCustomTableName()
    {
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->get_custom_table_name(), 'my_table_cstm', "SugarBean->get_custom_table_name() is not returning the correct custom table name.");
    }

    public function testRetrieveQuoting()
    {
        $bean = new BeanMockTestObjectName();
        $bean->db = new MockMysqlDb();
        $bean->retrieve("bad'idstring");
        $this->assertNotContains("bad'id", $bean->db->lastQuery);
        $this->assertContains("bad", $bean->db->lastQuery);
        $this->assertContains("idstring", $bean->db->lastQuery);
    }

    public function testRetrieveStringQuoting()
    {
        $bean = new BeanMockTestObjectName();
        $bean->db = new MockMysqlDb();
        $bean->retrieve_by_string_fields(array("test1" => "bad'string", "evil'key" => "data", 'tricky-(select * from config)' => 'test'));
        $this->assertNotContains("bad'string", $bean->db->lastQuery);
        $this->assertNotContains("evil'key", $bean->db->lastQuery);
        $this->assertNotContains("select * from config", $bean->db->lastQuery);
    }

}

// Using Mssql here because mysql needs real connection for quoting
require_once 'include/database/MssqlManager.php';
class MockMysqlDb extends MssqlManager
{
    public $database = true;
    public $lastQuery;

    public function connect(array $configOptions = null, $dieOnError = false)
    {
        return true;
    }

    public function query($sql, $dieOnError = false, $msg = '', $suppress = false, $keepResult = false)
    {
        $this->lastQuery = $sql;
        return true;
    }

    public function fetchByAssoc($result, $encode = true)
    {
        return false;
    }
}

class BeanMockTestObjectName extends SugarBean
{
    var $table_name = "my_table";

    function BeanMockTestObjectName() {
		parent::SugarBean();
	}
}