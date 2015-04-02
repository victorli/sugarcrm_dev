<?php

require_once ("modules/SugarFavorites/SugarFavorites.php");

/**
 * @ticket 64650
 */
class Bug64650Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var Account */
    private $account;

    /** @var SugarFavorites */
    private $favorite;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->account = SugarTestAccountUtilities::createAccount();

        $this->favorite = new SugarFavorites();
        $this->favorite->new_with_id = true;
        $this->favorite->id = SugarFavorites::generateGUID('Accounts', $this->account->id);
        $this->favorite->module = 'Accounts';
        $this->favorite->record_id = $this->account->id;
        $this->favorite->save();
    }

    public function tearDown()
    {
        $db = $this->favorite->db;
        $db->query(
            'DELETE FROM sugarfavorites WHERE id = '
            . $db->quoted($this->favorite->id)
        );

        SugarTestAccountUtilities::removeAllCreatedAccounts();

        SugarTestHelper::tearDown();
    }

    public function testFavorite()
    {
        $bean = BeanFactory::getBean('Accounts');

        $where = $bean->table_name . ".id = '{$this->account->id}'";
        $query = $bean->create_new_list_query('id', $where, array(), array(
            'favorites' => 1,
        ));

        $db = $bean->db;
        $dbResult = $db->query($query);
        $row = $db->fetchRow($dbResult);

        $row = $bean->convertRow($row);
        $bean->populateFromRow($row);

        $this->assertTrue($bean->my_favorite);
    }
}
