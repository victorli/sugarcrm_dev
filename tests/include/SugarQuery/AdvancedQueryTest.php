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
require_once 'modules/Contacts/Contact.php';
require_once 'tests/include/database/TestBean.php';
require_once 'include/SugarQuery/SugarQuery.php';

class AdvancedQueryTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    private $_db;
    protected $created = array();

    protected $backupGlobals = FALSE;

    protected $contacts = array();
    protected $accounts = array();
    protected $up = true;

    static public function setupBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    static public function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    public function setUp()
    {
        if(empty($this->_db)){
            $this->_db = DBManagerFactory::getInstance();
        }
        $this->up = $this->_db->usePreparedStatements;
    }

    public function tearDown()
    {
        $this->_db->usePreparedStatements = $this->up;
        BeanFactory::setBeanClass('Contacts');

        if ( !empty($this->contacts) ) {
            $bean = BeanFactory::getBean('Contacts');
            $contactList = array();
            foreach ( $this->contacts as $contact ) {
                $contactList[] = $this->_db->quoted($contact->id);
            }

            $this->_db->query("DELETE FROM {$bean->getTableName()} WHERE id IN (" . implode(",", $contactList). ")");
            if ($bean->hasCustomFields()) {
                $this->_db->query(
                    "DELETE FROM {$bean->get_custom_table_name()} WHERE id_c IN (" . implode(",", $contactList) . ")"
                );
            }
        }
        if ( !empty($this->accounts) ) {
            $accountList = array();
            foreach ( $this->accounts as $account ) {
                $accountList[] = $this->_db->quoted($account->id);
            }
            $bean = BeanFactory::getBean('Accounts');
            $this->_db->query("DELETE FROM {$bean->getTableName()} WHERE id IN (" . implode(",", $accountList) . ")");
            if ($bean->hasCustomFields()) {
                $this->_db->query(
                    "DELETE FROM {$bean->get_custom_table_name()} WHERE id_c IN (" . implode(",", $accountList) . ")"
                );
            }
        }

        if ( !empty($this->cases) ) {
            $casesList = array();
            foreach ( $this->cases as $case ) {
                $casesList[] = $this->_db->quoted($case->id);
            }
            $bean = BeanFactory::getBean('Cases');
            $this->_db->query("DELETE FROM {$bean->getTableName()} WHERE id IN (" . implode(",", $casesList) . ")");
            if ($bean->hasCustomFields()) {
                $this->_db->query(
                    "DELETE FROM {$bean->get_custom_table_name()} WHERE id_c IN (" . implode(",", $casesList) . ")"
                );
            }
        }

        if ( !empty($this->notes) ) {
            $notesList = array();
            foreach ( $this->notes as $note) {
                $notesList[] = $this->_db->quoted($note->id);
            }
            $bean = BeanFactory::getBean('Notes');
            $this->_db->query("DELETE FROM {$bean->getTableName()} WHERE id IN (" . implode(",", $notesList) . ")");
            if ($bean->hasCustomFields()) {
                $this->_db->query(
                    "DELETE FROM {$bean->get_custom_table_name()} WHERE id_c IN (" . implode(",", $notesList) . ")"
                );
            }
        }

        if (!empty($GLOBALS['dictionary']['Contact_Mock_Bug62961'])) {
            unset($GLOBALS['dictionary']['Contact_Mock_Bug62961']);
        }

    }

    public function testSelectInWhere()
    {

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();

        $sqWhere = new SugarQuery();
        $sqWhere->select("name");
        $sqWhere->from(BeanFactory::newBean('Accounts'));
        $sqWhere->where()->equals('name','Awesome')->equals('id', $account->id);
        $sqWhereResult = $sqWhere->execute();
        $sqWhereResult = reset($sqWhereResult);
        $this->assertEquals($sqWhereResult['name'], 'Awesome', 'The name Did Not Match it was ' . $sqWhereResult['name']);

        // create a new contact
        $case = BeanFactory::newBean('Cases');
        $case->name = 'Test Case';
        $case->account_id = $account->id;
        $case->save();

        $this->accounts[] = $account;
        $this->cases[] = $case;

        $sq = new SugarQuery();
        $sq->select(array("name"));
        $sq->from(BeanFactory::newBean('Cases'));
        $sq->where()->in('account_id', array($account->id));
        $result = $sq->execute();

        // only 1 record
        $result = reset($result);

        $this->assertEquals($result['name'], 'Test Case', 'The name Did Not Match it was ' . $result['name']);
    }

    public function testSelectUnion()
    {

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        $account1 = $account->id;
        $this->accounts[] = $account;
        // create a new contact
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Not Awesome';
        $account->save();
        $account2 = $account->id;

        $this->accounts[] = $account;

        $sq1 = new SugarQuery();
        $sq1->select(array("id", "name"));
        $sq1->from(BeanFactory::newBean('Accounts'));
        $sq1->where()->equals('name', 'Awesome');

        $sq2 = new SugarQuery();
        $sq2->select(array("id", "name"));
        $sq2->from(BeanFactory::newBean('Accounts'));
        $sq2->where()->equals('name', 'Not Awesome');

        $sqUnion = new SugarQuery();
        $sqUnion->union($sq1);
        $sqUnion->union($sq2);

        $result = $sqUnion->execute();

        $this->assertEquals(2, count($result), "More than 2 rows were returned.");

    }

    public function testSelectNotes() {
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        $account_id = $account->id;
        $this->accounts[] = $account;

        $note = BeanFactory::newBean('Notes');
        $note->name = 'Test note';
        $note->parent_type = 'Accounts';
        $note->parent_id = $account_id;
        $note->save();
        $this->notes[] = $note;

        $sq = new SugarQuery();
        $sq->from($account);
        $sq->where()->equals("id",$account_id, $account);
        $notes = $sq->join('notes')->joinName();
        $sq->select(array(array("accounts.name", "a_name"), array("$notes.name", "n_name")));


        $results = $sq->execute();

        $result = reset($results);

        $this->assertEquals('Test note', $result['n_name'], "The note name was: {$result['n_name']}");

    }

    public function testSelectFavorites() {
        $this->cases = array();
        for ( $i = 0 ; $i < 40 ; $i++ ) {
            $aCase = new aCase();
            $aCase->name = "UNIT TEST ".count($this->cases)." - ".create_guid();
            $aCase->billing_address_postalcode = sprintf("%08d",count($this->cases));
            if ( $i > 25 && $i < 36 ) {
                $aCase->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $aCase->assigned_user_id = '1';
            }
            $aCase->save();
            $this->cases[] = $aCase;
            if ( $i > 33 ) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Cases',$aCase->id);
                $fav->new_with_id = true;
                $fav->module = 'Cases';
                $fav->record_id = $aCase->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
        }

        $sq = new SugarQuery();
        $sq->select(array("id", "name"));
        $sq->from($aCase);

        $sf = new SugarFavorites();
        $sfAlias = $sf->addToSugarQuery($sq);

        $results = $sq->execute();

        $this->assertEquals('6', count($results), "The number of rows returned doesn't match the number of favorites created: " . count($results));

        foreach($results AS $case) {
            $fav = SugarFavorites::isUserFavorite('Cases',$case['id'],$GLOBALS['current_user']->id);
            $this->assertEquals($fav, true, "The record: {$case['id']} was not set as a favorite it is marked:" . var_export($fav, true));
        }

    }

    public function testSelectCount()
    {
        $sqCount = new SugarQuery();
        $sqCount->select()->setCountQuery();
        $sqCount->from(BeanFactory::newBean('Accounts'));
        $this->assertContains('count', $sqCount->compileSql());

        $sqCount = new SugarQuery();
        $sqCount->select()->setCountQuery();
        $sqCount->select(array('name', 'account_type'));
        $sqCount->from(BeanFactory::newBean('Accounts'));
        $sql = $sqCount->compileSql();
        $this->assertContains('count(0)', $sql);
        $this->assertContains('name', $sql);
        $this->assertContains('account_type', $sql);

    }

    public function testSelectCountGroupBy()
    {
        $sqCount = new SugarQuery();
        $sqCount->select()->setCountQuery();
        $sqCount->from(BeanFactory::newBean('Accounts'));
        $this->assertContains('count', $sqCount->compileSql());

        $sqCount = new SugarQuery();
        $sqCount->select()->setCountQuery();
        $sqCount->select(array('name', 'account_type'));
        $sqCount->from(BeanFactory::newBean('Accounts'));
        $sql = $sqCount->compileSql();
        $this->assertContains('count(0)', $sql);
        $this->assertContains('name', $sql);
        $this->assertContains('account_type', $sql);
        $this->assertContains('GROUP BY', $sql);

    }

    public function testBadFields()
    {
        $sq = new SugarQuery();
        $sq->select(array("id", "notARealField"));
        $sq->from(BeanFactory::getBean('Contacts'));
        $sq->where()->equals("noWhere", "nonYaBusiness");
        $sq->orderBy('yesIAmCertainlyAField');
        $sql = $sq->compileSql();

        $this->assertNotContains("yesIAmCertainlyAField", $sql);
        $this->assertNotContains("noWhere", $sql);
        $this->assertNotContains("notARealField", $sql);
    }

    public function testUniqueAliases()
    {
        $sq = new SugarQuery();
        $sq->select(array('*', 'date_modified'));
        $sq->from(BeanFactory::getBean('Contacts'));
        $sq->where()->equals("id","2");
        $sql = $sq->compileSql();
        $count = substr_count($sql, 'date_modified');
        // count the alias as well
        $this->assertEquals(2, $count);

        $sq = new SugarQuery();
        $sq->select(array('*', array('id', 'superAwesomeField')));
        $sq->from(BeanFactory::getBean('Contacts'));
        $sq->where()->equals("id", "2");
        $sql = $sq->compileSql();
        $this->assertcontains('id superAwesomeField', $sql);
    }

    /**
     * @ticket 62961
     */
    public function testCustomFields()
    {
        BeanFactory::setBeanClass('Contacts', 'Contact_Mock_Bug62961');
        $contact = BeanFactory::getBean("Contacts");
        $this->assertArrayHasKey("report_to_bigname", $contact->field_defs);
        $this->assertTrue($contact->hasCustomFields());

        $sq = new SugarQuery();
        $sq->select(array("id", "last_name", "bigname_c", "report_to_bigname"));
        $sq->from($contact);
        $sq->limit(0,1);

        $sql = $sq->compileSql();
        // ensure the query looks good
        $this->assertContains("contacts_cstm.bigname_c", $sql);
        $this->assertContains("_cstm.bigname_c report_to_bigname", $sql);
        $this->assertContains("LEFT JOIN contacts_cstm ON contacts_cstm.id_c = contacts.id", $sql);
        $this->assertRegExp('/LEFT JOIN contacts_cstm jt(\w+)_cstm ON \(jt\1_cstm.id_c = jt\1\.id\)/', $sql);
    }

    /**
     * test conditions on related variables
     */
    public function testRelateConditions()
    {
        $this->_db->usePreparedStatements = false;
        $contact = BeanFactory::getBean("Contacts");
        // regular query
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equals('first_name','Awesome');
        $this->assertRegExp('/WHERE.*contacts\.first_name\s*=\s*\'Awesome\'/',$sq->compileSql());

        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equals('contacts.last_name','Awesome');
        $this->assertRegExp('/WHERE.*contacts\.last_name\s*=\s*\'Awesome\'/',$sq->compileSql());

        // with related in name
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name", "account_name"));
        $sq->from($contact);
        $sq->where()->equals('account_name','Awesome');
        $sql = $sq->compileSql();
        $this->assertRegExp('/WHERE.*jt\w+\.name\s*=\s*\'Awesome\'/',$sql);
        $this->assertNotContains('contacts.account_name', $sql);

        // without related in name
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equals('account_name','Awesome');
        $this->assertRegExp('/WHERE.*jt\w+\.name\s*=\s*\'Awesome\'/',$sq->compileSql());

        // self-link
        $acc = BeanFactory::getBean('Accounts');
        $sq = new SugarQuery();
        $sq->select(array("id", "name"));
        $sq->from($acc);
        $sq->where()->equals('parent_name','Awesome');
        $this->assertRegExp('/WHERE.*jt\w+\.name\s*=\s*\'Awesome\'/',$sq->compileSql());

        // custom field
        BeanFactory::setBeanClass('Contacts', 'Contact_Mock_Bug62961');
        $contact = BeanFactory::getBean("Contacts");
        $GLOBALS['dictionary']['Contact_Mock_Bug62961']['fields'] = $contact->field_defs;
        $this->assertArrayHasKey("report_to_bigname", $contact->field_defs);
        $this->assertTrue($contact->hasCustomFields());

        // direct custom field
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equals('bigname_c','Chuck Norris');
        $this->assertRegExp('/WHERE.*contacts_cstm\.bigname_c\s*=\s*\'Chuck Norris\'/',$sq->compileSql());

        // related custom field
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equals('report_to_bigname','Chuck Norris');
        $this->assertRegExp('/WHERE.*jt\w+_cstm\.bigname_c\s*=\s*\'Chuck Norris\'/',$sq->compileSql());

        // compare fields
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->where()->equalsField('bigname_c','report_to_bigname');
        $this->assertRegExp('/WHERE.*contacts_cstm.bigname_c\s*=\s*jt\w+_cstm.bigname_c/',$sq->compileSql());

        $sq = new SugarQuery();
        $sq->select(array("id", "last_name", 'report_to_bigname'));
        $sq->from($contact);
        $sq->where()->notEqualsField('bigname_c','report_to_bigname');
        $sql = $sq->compileSql();
        $this->assertRegExp('/WHERE.*contacts_cstm.bigname_c\s*!=\s*jt\w+_cstm.bigname_c/',$sql);
        $this->assertContains("SELECT  contacts.id id, contacts.last_name last_name, jt0_reports_to_link_cstm.bigname_c report_to_bigname", $sql);
    }

    /**
     * Test rname exists
     */
    public function testRnameExists()
    {
        $contact = BeanFactory::getBean("Contacts");
        // will throw because name is composite
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name", "account_name"));
        $sq->from($contact);
        $sq->where()->equals('sync_contact',1);
        $sql = $sq->compileSql();
        // the field should not be there now
        $this->assertContains("id IS NOT NULL", $sql);

    }

    /**
     * Test bad conditions
     */
    public function testBadRelateConditions()
    {
        $contact = BeanFactory::getBean("Contacts");
        // will throw because name is composite
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name", "account_name"));
        $sq->from($contact);
        $sq->where()->equals('email_and_name1','Awesome');
        $sql = $sq->compileSql();
        // the field should not be there now
        $this->assertNotContains("email_and_name1 = 'Awesome'", $sql);

    }

    public function testRelatedOrderBy()
    {
        BeanFactory::setBeanClass('Contacts', 'Contact_Mock_Bug62961');
        $contact = BeanFactory::getBean("Contacts");
        $this->assertArrayHasKey("report_to_bigname", $contact->field_defs);
        $this->assertTrue($contact->hasCustomFields());

        // by related field
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->orderBy("account_name");
        $this->assertRegExp('/.*ORDER BY jt\w+.name DESC.*/',$sq->compileSql());

        // by custom field too
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->orderBy("account_name")->orderBy("bigname_c", "ASC");
        $this->assertRegExp('/ORDER BY jt\w+.name DESC,contacts.last_name ASC/',$sq->compileSql());

        // by related custom field
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->orderBy("report_to_bigname");
        $this->assertRegExp('/ORDER BY jt\w+.last_name DESC/',$sq->compileSql());

        // skip bad one
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from($contact);
        $sq->orderBy("portal_password1")->orderBy("account_name", "asc");
        $this->assertRegExp('/ORDER BY jt\w+.name asc/',$sq->compileSql());
    }

    public function testOrderByRaw()
    {
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from(BeanFactory::getBean('Contacts'));
        $sq->orderByRaw("last_name+1", 'DESC');
        $sql = $sq->compileSql();
        $this->assertContains("ORDER BY last_name+1 DESC", $sql);
    }

    public function testGroupByRaw()
    {
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from(BeanFactory::getBean("Contacts"));
        $sq->groupByRaw("last_name is awesome");
        $sql = $sq->compileSql();
        $this->assertContains("GROUP BY last_name is awesome", $sql);
    }

    public function testHavingRaw()
    {
        $sq = new SugarQuery();
        $sq->select(array("id", "last_name"));
        $sq->from(BeanFactory::getBean("Contacts"));
        $sq->havingRaw("last_name > 55");
        $sql = $sq->compileSql();
        $this->assertContains("HAVING last_name > 55", $sql);
    }

    public function testChildJoins()
    {
        $sq = new SugarQuery();
        $sq->select(array("id","last_name"));
        $sq->from(BeanFactory::getBean('Contacts'));
        $accounts = $sq->join('accounts');
        $opportunities = $accounts->join('opportunities');
        $opportunities->join('contacts');
        $sql = $sq->compileSql();
        $this->assertRegExp('/INNER JOIN contacts jt(\w+) ON /', $sql);
        $this->assertRegExp('/INNER JOIN opportunities jt(\w+) ON /', $sql);
    }

    public function testSetJoinOn()
    {
        $sq = new SugarQuery();
        $sq->select(array("id","last_name", "opportunity_role"));
        $sq->from(BeanFactory::getBean('Contacts'));
        $sq->setJoinOn(array('baseBean'=>'contact', 'baseBeanId' => 'test'));
        $sq->where('id', 'test');
        $sql = $sq->compileSql();
        $this->assertcontains("contact_id = 'test'", $sql);
    }

}

class Contact_Mock_Bug62961 extends Contact
{
    public function __construct()
    {
        parent::__construct();
        $this->field_defs['bigname_c'] =
            array (
                'calculated' => 'true',
                'formula' => 'strToUpper($last_name)',
                'enforced' => 'true',
                'dependency' => '',
                'required' => false,
                'source' => 'custom_fields',
                'name' => 'bigname_c',
                'vname' => 'LBL_BIGNAME',
                'type' => 'varchar',
                'massupdate' => '0',
                'default' => NULL,
                'no_default' => false,
                'importable' => 'false',
                'duplicate_merge' => 'disabled',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'len' => '255',
                'size' => '20',
                'custom_module' => 'Contacts',
                'sort_on' => 'last_name',
            );
        $this->field_defs['report_to_bigname'] =
            array(
                'name' => 'report_to_bigname',
                'rname' => 'bigname_c',
                'id_name' => 'reports_to_id',
                'vname' => 'LBL_REPORTS_TO',
                'type' => 'relate',
                'link' => 'reports_to_link',
                'table' => 'contacts',
                'isnull' => 'true',
                'module' => 'Contacts',
                'dbType' => 'varchar',
                'len' => 'id',
                'reportable' => false,
                'source' => 'non-db',
            );

    }

    public function hasCustomFields()
    {
        return true;
    }
}
