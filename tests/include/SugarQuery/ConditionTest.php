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

class ConditionTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    private static $db;
    protected static $products = array();
    protected static $prodIds = array();

    protected $created = array();

    protected $backupGlobals = false;

    /**
     * @var Product
     */
    protected $product_bean;

    static public function setupBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        if (empty(self::$db)) {
            self::$db = DBManagerFactory::getInstance();
        }


        // "Delete" all the products that may currently exist
        $sql = "SELECT id FROM products WHERE deleted = 0";
        $res = self::$db->query($sql);
        while ($row = self::$db->fetchRow($res)) {
            self::$prodIds[] = $row['id'];
        }

        if (self::$prodIds) {
            $sql = "UPDATE products SET deleted = 1 WHERE id IN ('" . implode("','", self::$prodIds) . "')";
            self::$db->query($sql);
        }

        for ($x = 100; $x <= 300; $x++) {
            // create a new product
            $product = SugarTestProductUtilities::createProduct();
            $product->name = "SugarQuery Unit Test {$x}";
            $product->quantity = $x;

            $product->save();
            self::$products[] = $product;
        }

        unset($opportunity);
    }

    static public function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        if (!empty(self::$products)) {
            $oppList = array();
            foreach (self::$products as $opp) {
                $oppList[] = $opp->id;
            }

            self::$db->query("DELETE FROM products WHERE id IN ('" . implode("','", $oppList) . "')");

            if (self::$db->tableExists('products_cstm')) {
                self::$db->query("DELETE FROM products_cstm WHERE id_c IN ('" . implode("','", $oppList) . "')");
            }
        }

        if (self::$prodIds) {
            $sql = "UPDATE products SET deleted = 0 WHERE id IN ('" . implode("','", self::$prodIds) . "')";
            self::$db->query($sql);
        }
    }

    public function setUp()
    {
        $this->product_bean = BeanFactory::newBean('Products');
    }

    public function testEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from($this->product_bean);
        $sq->where()->equals('quantity', 200, $this->product_bean);

        $result = $sq->execute();
        $this->assertEquals(count($result), 1, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $this->assertEquals(200, $opp['quantity'], "The amount does not equal to 200 it was: {$opp['quantity']}");
        }
    }

    public function testContains()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from($this->product_bean);
        $sq->where()->contains('name', 'Query Unit Test 10', $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 10, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $test_string = strstr($opp['name'], '10');
            $this->assertTrue(!empty($test_string), "The name did not contain 10 it was: {$opp['name']}");
        }
    }

    public function testStartsWith()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from($this->product_bean);
        $sq->where()->starts('name', 'SugarQuery Unit Test 10', $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 10, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $test_string = stristr($opp['name'], 'SugarQuery Unit Test 10');
            $this->assertTrue(
                !empty($test_string),
                "The name did not start with SugarQuery Unit Test 10 it was: {$opp['name']}"
            );
        }
    }

    public function testLessThan()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->lt('quantity', 200, $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 100, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $this->assertLessThan(200, $opp['quantity'], "The amount was not less than 2000 it was: {$opp['quantity']}");
        }
    }

    public function testLessThanEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->lte('quantity', 200, $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 101, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $this->assertLessThanOrEqual(
                200,
                $opp['quantity'],
                "The amount was not less than 2000 it was: {$opp['quantity']}"
            );
        }
    }

    public function testGreaterThan()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->gt('quantity', 200, $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 100, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $this->assertGreaterThan(200, $opp['quantity'], "The amount was not less than 2000 it was: {$opp['quantity']}");
        }
    }

    public function testGreaterThanEquals()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->gte('quantity', 200, $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 101, "Wrong row count, actually received: " . count($result) . " back.");

        foreach ($result AS $opp) {
            $this->assertGreaterThanOrEqual(200, $opp['quantity'], "Wrong amount value detected.");
        }
    }

    public function testDateRange()
    {
        $sq = new SugarQuery();

        $sq->select(array('name', 'date_modified'));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->dateRange('date_entered', 'last_7_days', $this->product_bean);

        $result = $sq->execute();

        $this->assertGreaterThanOrEqual(
            1,
            count($result),
            'Wrong row count, actually received: ' . count($result) . ' back.'
        );

        foreach ($result AS $opp) {
            $this->assertGreaterThanOrEqual(
                gmdate("Y-m-d H:i:s", gmmktime(0, 0, 0, gmdate('m'), gmdate('d') - 7, gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
            $this->assertLessThanOrEqual(
                gmdate("Y-m-d H:i:s", gmmktime(23, 59, 59, gmdate('m'), gmdate('d'), gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
        }
    }

    public function testDateBetween()
    {
        $sq = new SugarQuery();

        $sq->select(array('name', 'date_modified'));
        $sq->from(BeanFactory::newBean('Products'));
        $params = array(gmdate('Y-m-d', gmmktime(0, 0, 0, gmdate('m'), gmdate('d') - 1, gmdate('Y'))), gmdate('Y-m-d'));
        $sq->where()->dateBetween('date_entered', $params, $this->product_bean);

        $result = $sq->execute();

        $this->assertGreaterThanOrEqual(
            1,
            count($result),
            'Wrong row count, actually received: ' . count($result) . ' back.'
        );

        foreach ($result AS $opp) {
            $this->assertGreaterThanOrEqual(
                gmdate("Y-m-d H:i:s", gmmktime(0, 0, 0, gmdate('m'), gmdate('d') - 1, gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
            $this->assertLessThanOrEqual(
                gmdate("Y-m-d H:i:s", gmmktime(23, 59, 59, gmdate('m'), gmdate('d'), gmdate('Y'))),
                $opp['date_modified'],
                'Wrong date detected.'
            );
        }
    }

    public function testIn()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->in('quantity', array(100, 101, 102, 103, 104, 105), $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 6, "Wrong row count, actually received: " . count($result) . " back.");


        //With a null value
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->in('quantity', array('', 100, 101, 102, 103, 104, 105), $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 6, "Wrong row count, actually received: " . count($result) . " back.");


        //With only a null value
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->in('quantity', array(''), $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 0, "Wrong row count, actually received: " . count($result) . " back.");
    }

    public function testNotIn()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->notIn('quantity', array(100, 101, 102, 103, 104, 105));

        $result = $sq->execute();

        $this->assertEquals(195, count($result), "Wrong row count, actually received: " . count($result) . " back.");


        //With a null value
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->notIn('quantity', array('', 100, 101, 102, 103, 104, 105));

        $result = $sq->execute();

        $this->assertEquals(195, count($result), "Wrong row count, actually received: " . count($result) . " back.");


        //With only a null value
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->notIn('quantity', array(''));

        $result = $sq->execute();

        $this->assertEquals(201, count($result), "Wrong row count, actually received: " . count($result) . " back.");
    }

    public function testBetween()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->between('quantity', 110, 120, $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 11, "Wrong row count, actually received: " . count($result) . " back.");
    }

    public function testNotNull()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->notNull('quantity', $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 201, "Wrong row count, actually received: " . count($result) . " back.");

    }

    public function testNull()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "quantity"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->isNull('quantity', $this->product_bean);

        $result = $sq->execute();

        $this->assertEquals(count($result), 0, "Wrong row count, actually received: " . count($result) . " back.");

    }

    public function testRaw()
    {
        $sq = new SugarQuery();

        $sq->select(array("name", "amount"));
        $sq->from(BeanFactory::newBean('Products'));
        $sq->where()->addRaw("name = 'SugarQuery Unit Test 131'");

        $result = $sq->execute();

        $this->assertEquals(count($result), 1, "Wrong row count, actually received: " . count($result) . " back.");

        $result = reset($result);

        $this->assertEquals(
            $result['name'],
            "SugarQuery Unit Test 131",
            "Wrong record returned, received: " . $result['name']
        );

    }

    public function testOrderByLimit()
    {
        $sq = new SugarQuery();
        $sq->select("name", "quantity");
        $sq->from(BeanFactory::newBean('Products'));
        $sq->orderBy("quantity", "ASC");
        $sq->limit(2);

        $result = $sq->execute();

        $this->assertEquals(count($result), 2, "Wrong row count, actually received: " . count($result) . " back.");

        $low = $result[0]['quantity'];
        $high = $result[1]['quantity'];

        $this->assertGreaterThan($low, $high, "{$high} is not greater than {$low}");

        $sq = new SugarQuery();
        $sq->select("name", "quantity");
        $sq->from(BeanFactory::newBean('Products'));
        $sq->orderBy("quantity", "ASC");
        $sq->limit(2);
        $sq->offset(1);

        $result = $sq->execute();

        $this->assertEquals(count($result), 2, "Wrong row count, actually received: " . count($result) . " back.");

        $low = $result[0]['quantity'];
        $high = $result[1]['quantity'];

        $this->assertGreaterThan($low, $high, "{$high} is not greater than {$low}");


    }

}
