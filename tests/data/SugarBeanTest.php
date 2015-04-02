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

require_once('data/SugarBean.php');

use SugarTestAccountUtilities as AccountHelper;
use SugarTestUserUtilities as UserHelper;

class SugarBeanTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
	}

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public function tearDown()
    {
        BeanFactory::setBeanClass('Accounts', null);
        SugarTestHelper::tearDown();
    }

	public static function tearDownAfterClass()
	{
	    SugarTestHelper::tearDown();
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

    /**
     * This test makes sure that the object we are looking for is returned from the build_related_list method as
     * something changed someplace that is causing it to return the template that was passed in.
     */
    public function testBuildRelatedListReturnsRecordBeanVsEmptyBean()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $bean = new SugarBean();

        $query = "select id FROM " . $account->table_name . " where id = '" . $account->id . "'";
        $return = array_shift($bean->build_related_list($query, BeanFactory::getBean('Accounts')));

        $this->assertEquals($account->id, $return->id);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }




    /**
     * Test to make sure that when a bean is cloned it removes all loaded relationships so they can be recreated on
     * the cloned copy if they are called.
     *
     * @group 51630
     * @return void
     */
    public function testCloneBeanDoesntKeepRelationship()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $account->load_relationship('contacts');

        // lets make sure the relationship is loaded
        $this->assertTrue(isset($account->contacts));

        $clone_account = clone $account;

        // lets make sure that the relationship is not on the cloned record
        $this->assertFalse(isset($clone_account->contacts));

        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * Test whether a relate field is determined correctly
     *
     * @param array $field_defs
     * @param string $field_name
     * @param bool $is_relate
     * @dataProvider isRelateFieldProvider
     * @covers SugarBean::is_relate_field
     */
    public function testIsRelateField(array $field_defs, $field_name, $is_relate)
    {
        $bean = new BeanIsRelateFieldMock();
        $bean->field_defs = $field_defs;
        $actual = $bean->is_relate_field($field_name);

        if ($is_relate)
        {
            $this->assertTrue($actual);
        }
        else
        {
            $this->assertFalse($actual);
        }
    }

    public static function isRelateFieldProvider()
    {
        return array(
            // test for on a non-existing field
            array(
                array(), 'dummy', false,
            ),
            // test for non-specified field type
            array(
                array(
                    'my_field' => array(),
                ), 'my_field', false,
            ),
            // test on a non-relate field type
            array(
                array(
                    'my_field' => array(
                        'type' => 'varchar',
                    ),
                ), 'my_field', false,
            ),
            // test on a relate field type but link not specified
            array(
                array(
                    'my_field' => array(
                        'type' => 'relate',
                    ),
                ), 'my_field', false,
            ),
            // test when only link is specified
            array(
                array(
                    'my_field' => array(
                        'link' => 'my_link',
                    ),
                ), 'my_field', false,
            ),
            // test on a relate field type
            array(
                array(
                    'my_field' => array(
                        'type' => 'relate',
                        'link' => 'my_link',
                    ),
                ), 'my_field', true,
            ),
        );
    }

    /**
     * test that currency/decimal from db is a string value
     * @dataProvider provideCurrencyFieldStringValues
     * @group sugarbean
     * @group currency
     */
    public function testCurrencyFieldStringValue($type, $actual, $expected)
    {
        $mock = new SugarBean();
        $mock->id = 'SugarBeanMockStringTest';
        $mock->field_defs = array(
            'testDecimal' => array(
                'type' => $type
            ),
        );

        $mock->testDecimal = $actual;
        $mock->fixUpFormatting();
        $this->assertSame($expected, $mock->testDecimal);
    }

    public function provideCurrencyFieldStringValues()
    {
        return array(
            array('decimal', '500.01', '500.01'),
            array('decimal', 500.01, '500.01'),
            array('decimal', '-500.01', '-500.01'),
            array('currency', '500.01', '500.01'),
            array('currency', 500.01, '500.01'),
            array('currency', '-500.01', '-500.01'),
        );
    }

    /**
     * SP-618
     * Verify that calling getCleanCopy on uncommon beans (like SessionManager) and common beans returns a new instance of the bean and not a null
     * @group sugarbean
     */
    public function testGetCopyNotNull()
    {
        $mock = new SessionManager();
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, "New instance of SessionManager SugarBean should not be null");
        $this->assertEquals($mock->module_name, $newInstance->module_name);

        $mock = new SugarBean();
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, "New instance of SugarBean should not be null");

        $mock = BeanFactory::getBean('Accounts');
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, "New instance of Accounts SugarBean should not be null");
        $this->assertEquals('Accounts', $newInstance->module_name);
    }

    /**
     * @group sugarbean
     */
    public function testGetNotificationRecipientsReturnsEmptyArray()
    {
        $mock = new SugarBean();
        unset($mock->assigned_user_id);

        $ret = $mock->get_notification_recipients();

        $this->assertEmpty($ret);
    }

    public function testGetNotificationRecipientsReturnsNonEmptyArray()
    {
        $mock = new SugarBean();
        $mock->assigned_user_id = '1';

        $ret = $mock->get_notification_recipients();

        $this->assertEquals('1',$ret[0]->id);
    }
    /**
     * Check that the decryption is not called until the actual value is used
     * @return void
     */
    public function testDecryptCallsNumber()
    {
        $oSugarBean = new BeanMockTestObjectName();

        $oSugarBean->field_defs = array(
            'test_field' => array(
                'name' => 'test_field',
                'type' => 'encrypt',
            ),
        );
        $encrypted_value = 'encrypted_value';
        $oSugarBean->test_field = ''; //initialization to avoid "Indirect modification of overloaded property..." error
        $oSugarBean->test_field =& $encrypted_value; //use link to avoid calling __get method in assertEquals
        $oSugarBean->field_name_map['test_field']['type'] = 'encrypt';
        $oSugarBean->check_date_relationships_load(); //$oSugarBean->test_field shouldn't be changed
        $this->assertEquals('encrypted_value', $encrypted_value);
        $decrypted_value = $oSugarBean->test_field; //$oSugarBean->test_field should be changed
        $this->assertNotEquals($encrypted_value, $decrypted_value);
    }

    /**
     * Check if SugarBean::checkUserAccess returns true for a valid case.
     * @covers SugarBean::checkUserAccess
     */
    public function testCheckUserAccess()
    {
        $user = UserHelper::createAnonymousUser(true, 1);
        $account = AccountHelper::createAccount();

        $this->assertTrue($account->checkUserAccess($user));
    }

    /**
     * @param array $parent_data   Parent bean data
     * @param array $child_data    Child bean data
     * @param array $fn_field_defs Function field definition
     * @param mixed $expected      Expected value
     *
     * @dataProvider functionFieldProvider
     */
    public function testProcessFunctionFields(array $parent_data, array $child_data, array $fn_field_defs, $expected)
    {
        $parent = new BeanFunctionFieldsMock();
        $child = new SugarBean();
        $child->field_defs['fn_field'] = $fn_field_defs;
        $child->fn_field = null;

        foreach ($parent_data as $key => $value) {
            $parent->$key = $value;
        }

        foreach ($child_data as $key => $value) {
            $child->$key = $value;
        }

        $child->field_defs = $fn_field_defs;

        $parent->processFunctionFields($child, array('fn_field' => $fn_field_defs));

        $this->assertEquals($expected, $child->fn_field);
    }

    public static function functionFieldProvider()
    {
        $parent_data = array('foo' => 'bar');
        $child_data = array('baz' => 'quux');

        return array(
            // source is parent bean, function is global function
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('foo'),
                    'function_name' => 'strlen',
                ),
                3,
            ),
            // source is child bean, function is static function of a class
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('baz'),
                    'function_params_source' => 'this',
                    'function_class' => 'BeanFunctionFieldsMock',
                    'function_name' => 'toUpper',
                ),
                'QUUX',
            ),
            // function declaration is in external file
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('foo'),
                    'function_name' => 'SugarBeanTest_external_function',
                    'function_require' => dirname(__FILE__) . '/SugarBeanTest/external_function.php',
                ),
                'bar',
            ),
            // argument is $this
            array(
                $parent_data,
                array(),
                array(
                    'function_params' => array('$this'),
                    'function_name' => 'get_class',
                ),
                'BeanFunctionFieldsMock',
            ),
            // param source is wrong
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('foo'),
                    'function_params_source' => 'unknown',
                    'function_name' => 'strlen',
                ),
                null,
            ),
            // function doesn't exist
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('foo'),
                    'function_name' => 'SugarBeanTest_unknown',
                ),
                null,
            ),
            // source field is not set
            array(
                $parent_data,
                $child_data,
                array(
                    'function_params' => array('bar'),
                    'function_name' => 'strlen',
                ),
                null,
            ),
        );
    }

    /**
     * Check if SugarBean::checkUserAccess returns false without team access.
     * @covers SugarBean::checkUserAccess
     */
    public function testCheckUserAccessWithoutTeamAccess()
    {
        $user = UserHelper::createAnonymousUser();
        $account = AccountHelper::createAccount();

        $this->assertFalse($account->checkUserAccess($user));
    }

    /**
     * Check if SugarBean::checkUserAccess returns false without ACL access.
     * @covers SugarBean::checkUserAccess
     */
    public function testCheckUserAccessWithoutACLAccess()
    {
        $user = UserHelper::createAnonymousUser();
        BeanFactory::setBeanClass('Accounts', 'NoAccessAccount');

        $account = BeanFactory::getBean('Accounts');
        $account->id = 'foo';

        $this->assertFalse($account->checkUserAccess($user));
    }

    /**
     * This test will make sure that when you enter an operation that the one that actually entered the operation
     * actually is the one to leave it.
     */
    public function testEnterLeaveOperationMultipleTimes()
    {
        $ret1 = SugarBean::enterOperation('unit_test');
        $this->assertTrue($ret1);

        $ret2 = SugarBean::enterOperation('unit_test');
        $this->assertFalse($ret2);

        $this->assertFalse(SugarBean::leaveOperation('unit_test', $ret2));

        $this->assertTrue(SugarBean::leaveOperation('unit_test', $ret1));

        SugarBean::resetOperations();
    }

    /**
     *
     * Test logging for distinct mismatch/compensation and the
     * proper return of offending record ids.
     *
     * @covers SugarBean::logDistinctMismatch
     * @dataProvider providerTestLogDistinctMismatch
     * @group unit
     *
     * @param array $sqlRows
     * @param array $beans
     * @param string $level
     * @param array $expected
     */
    public function testLogDistinctMismatch(array $sqlRows, array $beans, $level, array $expected)
    {
        LoggerManager::getLogger()->setLevel($level);

        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();

        $methodArgs = array($sqlRows, $beans);
        $this->assertEquals(
            $expected,
            SugarTestReflection::callProtectedMethod($bean, 'logDistinctMismatch', $methodArgs),
            "Wrong offending record ids returned"
        );
    }

    public function providerTestLogDistinctMismatch()
    {
        return array(

            // matching sqlRows vs beanSet
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a2', 'name' => 'record2'),
                    2 => array('id' => 'a3', 'name' => 'record3'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                ),
                'debug',
                array(),
            ),

            // duplicate sqlRows
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a1', 'name' => 'record1'),
                    2 => array('id' => 'a2', 'name' => 'record2'),
                    3 => array('id' => 'a3', 'name' => 'record3'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                ),
                'debug',
                array('a1' => 2),
            ),

            // duplicate sqlRows, no detailed logging (not enabled by default)
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a1', 'name' => 'record1'),
                    2 => array('id' => 'a2', 'name' => 'record2'),
                    3 => array('id' => 'a3', 'name' => 'record3'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                ),
                'fatal',
                array(),
            ),
        );
    }

    /**
     *
     * Test fetchFromQuery with distinct compensation.
     *
     * @covers SugarBean::fetchFromQuery
     * @covers SugarBean::computeDistinctCompensation
     * @dataProvider providerTestFetchFromQueryWithDistinctCompensation
     * @group unit
     */
    public function testFetchFromQueryWithDistinctCompensation($sqlRows, $expected, $compensation)
    {
        // prepare SugarQuery
        $query = $this->getMockBuilder('SugarQuery')
            ->setMethods(array('execute'))
            ->getMock();

        $query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($sqlRows));

        // sut
        $bean = $this->getMockBuilder('SugarBean')
            ->setMethods(array('call_custom_logic', 'logDistinctMismatch'))
            ->getMock();
        $bean->field_defs = array('id' => 'id', 'name' => 'name');

        if ($compensation) {
            $bean->expects($this->once())
                ->method('logDistinctMismatch');
        }

        // execute fetch
        $options = array('compensateDistinct' => true);
        $results = $bean->fetchFromQuery($query, array(), $options);

        // tests
        $this->assertArrayHasKey(
            '_distinctCompensation',
            $results,
            'No distinct compensation returned'
        );

        $this->assertEquals(
            $compensation,
            $results['_distinctCompensation'],
            'Incorrect compensation result'
        );

        unset($results['_distinctCompensation']);

        foreach ($results as $key => $bean) {
            $this->assertEquals(
                $expected[$key],
                $bean->toArray(),
                'Incorrect bean result set'
            );
        }
    }

    public function providerTestFetchFromQueryWithDistinctCompensation()
    {
        return array(

            // matching sqlRows vs beanSet
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a2', 'name' => 'record2'),
                    2 => array('id' => 'a3', 'name' => 'record3'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                ),
                0,
            ),

            // one duplicate sqlRows
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a1', 'name' => 'record1'),
                    2 => array('id' => 'a2', 'name' => 'record2'),
                    3 => array('id' => 'a3', 'name' => 'record3'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                ),
                1,
            ),

            // multiple duplicate sqlRows with different records
            array(
                array(
                    0 => array('id' => 'a1', 'name' => 'record1'),
                    1 => array('id' => 'a1', 'name' => 'record1'),
                    2 => array('id' => 'a2', 'name' => 'record2'),
                    3 => array('id' => 'a3', 'name' => 'record3'),
                    4 => array('id' => 'a3', 'name' => 'record3'),
                    5 => array('id' => 'a3', 'name' => 'record3'),
                    6 => array('id' => 'a4', 'name' => 'record4'),
                    7 => array('id' => 'a5', 'name' => 'record5'),
                    8 => array('id' => 'a5', 'name' => 'record5'),
                    9 => array('id' => 'a1', 'name' => 'record1'),
                ),
                array(
                    'a1' => array('id' => 'a1', 'name' => 'record1'),
                    'a2' => array('id' => 'a2', 'name' => 'record2'),
                    'a3' => array('id' => 'a3', 'name' => 'record3'),
                    'a4' => array('id' => 'a4', 'name' => 'record4'),
                    'a5' => array('id' => 'a5', 'name' => 'record5'),
                ),
                5,
            ),
        );
    }

    /**
     * Tests SugarBean::create_new_list_query
     * This test is to make sure ret_array['secondary_select'] should not contain fields with relationship_fields defined
     */
    public function testCreateNewListQuery()
    {
        $bean = BeanFactory::getBean("Contacts");
        $filter = array(
            "account_id",
            "opportunity_role_fields",
            "opportunity_role_id",
            "opportunity_role"
        );
        $params = array(
            "distinct" => false,
            "joined_tables" => array(0 => "opportunities_contacts"),
            "include_custom_fields" => true,
            "collection_list" => null
        );
        $query = $bean->create_new_list_query("", "", $filter, $params, 0, "", true);

        $this->assertNotContains("opportunity_role_fields", $query["secondary_select"], "secondary_select should not contain fields with relationship_fields defined (e.g. opportunity_role_fields).");
        $this->assertContains("opportunity_role_id", $query["secondary_select"], "secondary_select should contain the fields that's defined in relationship_fields (e.g. opportunity_role_id).");

        $bean = BeanFactory::getBean("Contacts");
        $filter = array(
            "account_name",
            "account_id"
        );
        $params = array(
            "join_type" => "LEFT JOIN",
            "join_table_alias" => "accounts",
            "join_table_link_alias" => "jtl0"
        );
        $query = $bean->create_new_list_query("", "", $filter, $params, 0, "", true);

        $this->assertEquals(1, substr_count($query["secondary_select"], " account_id"), "secondary_select should not contain duplicate alias names.");

        $bean = BeanFactory::getBean('Calls');
        $query = $bean->create_new_list_query('', '', array('contact_name', 'contact_id'), array(), 0, '', true);

        $this->assertContains("contact_id", $query["secondary_select"], "secondary_select should contain rel_key field (e.g. contact_id).");
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
		parent::__construct();
	}
}

class BeanIsRelateFieldMock extends SugarBean
{
    public function is_relate_field($field_name_name)
    {
        return parent::is_relate_field($field_name_name);
    }
}

class BeanFunctionFieldsMock extends SugarBean
{
    public function processFunctionFields(SugarBean $bean, array $fields)
    {
        parent::processFunctionFields($bean, $fields);
    }

    public static function toUpper($arg)
    {
        return strtoupper($arg);
    }
}

class NoAccessAccount extends Account
{
    public function ACLAccess()
    {
        return false;
    }
}
