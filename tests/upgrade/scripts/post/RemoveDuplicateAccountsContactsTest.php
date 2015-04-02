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

require_once 'tests/SugarTestHelper.php';
require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'modules/Contacts/upgrade/scripts/post/7_RemoveDuplicateAccountsContacts.php';

/**
 * Test for removing duplicate rows from the accounts_contacts table non-destructively.
 */
class RemoveDuplicateAccountsContactsTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $contact_id = "Dupe_Acct_Cont_Contact_Id";
    protected static $account_id = "Dupe_Acct_Cont_Account_Id";
    protected $db;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->db = $GLOBALS['db'];
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $contact_id = self::$contact_id;
        $account_id = self::$account_id;
        $this->db->query("DELETE FROM accounts_contacts WHERE contact_id = '$contact_id' OR account_id = '$account_id'");
        parent::tearDown();
    }

    /**
     * @param array  $def
     * @param string $layout
     * @param string $file
     * @param array  $expectedLayout
     *
     * @dataProvider provider
     *
     * Functional test
     * @group functional
     */
    public function testRun($startingRows, $expectedRows)
    {
        $contact_id = self::$contact_id;
        $account_id = self::$account_id;
        $db = $this->db;
        if ($db instanceof OracleManager) {
            $this->markTestSkipped();
        }
        $upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        foreach ($startingRows as $row) {
            $query = "INSERT into accounts_contacts
            (id, contact_id, account_id, date_modified, primary_account, deleted)
            VALUES $row";
            $db->query($query);
        }



        $script = new SugarUpgradeRemoveDuplicateAccountsContacts($upgradeDriver);
        $script->from_version = "7.1.5";
        $script->db = $db;

        $script->run();

        $count = $this->db->getOne("SELECT count(*) as c FROM accounts_contacts "
                                 . "WHERE contact_id = '{$contact_id}' OR account_id = '{$account_id}'");


        $results = $db->query(
            "SELECT * FROM accounts_contacts "
            . "WHERE contact_id = '{$contact_id}' OR account_id = '{$account_id}' ORDER BY id"
        );

        $this->assertEquals(count($expectedRows), $count, "Incorrect number of rows returned");

        $i = 0;
        while ($row = $db->fetchRow($results)) {
            $this->assertEquals($expectedRows[$i], $row);
            $i++;
        }
    }

    public function provider()
    {
        $contact_id = self::$contact_id;
        $account_id = self::$account_id;
        return array(
            //Basic use case
            array(
                //Starting rows
                array(
                    "('s1', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                    "('s2', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                ),
                //Expected rows
                array(
                    array(
                        'id' => 's1',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '1',
                        'deleted' => '0',
                    ),
                ),
            ),
            //Deleted use case
            array(
                //Starting rows
                array(
                    "('s1', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                    "('s2', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                    "('s3', '{$contact_id}', '{$account_id}', NULL, 1, 1)",
                ),
                //Expected rows
                array(
                    array(
                        'id' => 's1',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '1',
                        'deleted' => '0',
                    ),
                    array(
                        'id' => 's3',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '1',
                        'deleted' => '1',
                    ),
                ),
            ),
            //Primary Flag use case
            array(
                //Starting rows
                array(
                    "('s1', '{$contact_id}', '{$account_id}', NULL, 0, 0)",
                    "('s2', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                    "('s3', '{$contact_id}', '{$account_id}', NULL, 1, 0)",
                ),
                //Expected rows
                array(
                    array(
                        'id' => 's1',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '0',
                        'deleted' => '0',
                    ),
                    array(
                        'id' => 's2',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '1',
                        'deleted' => '0',
                    ),
                ),
            ),
            //Multiple Contacts per account
            array(
                //Starting rows
                array(
                    "('s1', '{$contact_id}', '{$account_id}', NULL, 0, 0)",
                    "('s2', '{$contact_id}_2', '{$account_id}', NULL, 0, 0)",
                    "('s3', '{$contact_id}', '{$account_id}', NULL, 0, 0)",
                ),
                //Expected rows
                array(
                    array(
                        'id' => 's1',
                        'contact_id' => $contact_id,
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '0',
                        'deleted' => '0',
                    ),
                    array(
                        'id' => 's2',
                        'contact_id' => $contact_id . "_2",
                        'account_id' => $account_id,
                        'date_modified' => null,
                        'primary_account' => '0',
                        'deleted' => '0',
                    ),
                ),
            ),
        );
    }
}
