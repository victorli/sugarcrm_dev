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

require_once('include/ListView/ListViewData.php');


class RLIBug43714Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var ListViewData instance
     */
    private $_lvd;

    /**
     * @var Product instance
     */
    private $_product;

    public function setUp()
    {
        $this->markTestIncomplete("These tests fail in DB Strict mode on Stack94");
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
        $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
        $GLOBALS['current_user']->setPreference('timef', "h.iA");

        $this->_lvd = new ListViewData();
        $this->_product = new Product();
        $this->_product->disable_row_level_security = true;

        $GLOBALS['db']->query("DELETE FROM products_audit WHERE parent_id = '{$this->_product->id}'");

        $_REQUEST = array(
            "return_action" => "",
            "return_module" => "",
            "massupdate" => false,
            "delete" => false,
            "merge" => false,
            "current_query_by_page" => "YToxNTp7czo0OiJsdnNvIjtzOjM6ImFzYyI7czoyNjoiUHJvZHVjdHMyX1BST0RVQ1RfT1JERVJfQlkiO3M6OToidHlwZV9uYW1lIjtzOjI0OiJQcm9kdWN0czJfUFJPRFVDVF9vZmZzZXQiO3M6MToiMCI7czoxMDoibWFzc3VwZGF0ZSI7czo1OiJmYWxzZSI7czo2OiJkZWxldGUiO3M6NToiZmFsc2UiO3M6NToibWVyZ2UiO3M6NToiZmFsc2UiO3M6NjoibW9kdWxlIjtzOjg6IlByb2R1Y3RzIjtzOjY6ImFjdGlvbiI7czo1OiJpbmRleCI7czoxODoic2VsZWN0X2VudGlyZV9saXN0IjtzOjE6IjAiO3M6MTE6InNlbGVjdENvdW50IjthOjI6e2k6MDtzOjE6IjAiO2k6MTtzOjE6IjAiO31zOjIzOiJ0ZWFtX25hbWVfbmV3X29uX3VwZGF0ZSI7czo1OiJmYWxzZSI7czoxOToidGVhbV9uYW1lX2FsbG93X25ldyI7czo0OiJ0cnVlIjtzOjI2OiJ0ZWFtX25hbWVfYWxsb3dlZF90b19jaGVjayI7czo1OiJmYWxzZSI7czoxNDoidGVhbV9uYW1lX21hc3MiO3M6MTU6InRlYW1fbmFtZV90YWJsZSI7czoxNDoidGVhbV9uYW1lX3R5cGUiO3M6NzoicmVwbGFjZSI7fQ==",
            "module" => "Products",
            "action" => "index",
            "lvso" => "asc",
            "Products2_PRODUCT_ORDER_BY" => "type_name",
            "uid" => "",
            "select_entire_list" => 0,
            "Products2_PRODUCT_offset" => 0,
            "show_plus" => "",
            "selectCount" => array
            (
                "0" => 0,
                "1" => 0,
            ),
            "contact_name" => "",
            "contact_id" => "",
            "date_purchased" => "",
            "discount_select" => "",
            "status" => "",
            "tax_class" => "",
            "date_support_expires" => "",
            "date_support_starts" => "",
            "book_value_date" => "",
            "account_name" => "",
            "account_id" => "",
            "update_fields_team_name_collection" => "",
            "team_name_new_on_update" => false,
            "team_name_allow_update" => "",
            "team_name_allow_new" => true,
            "team_name_allowed_to_check" => false,
            "team_name_mass" => "team_name_table",
            "team_name_collection_0" => "",
            "id_team_name_collection_0" => "",
            "team_name_type" => "replace",
            "searchFormTab" => "advanced_search",
            "query" => true,
            "manufacturer_id_advanced_multiselect" => true,
            "category_id_advanced_multiselect" => true,
            "type_id_advanced_multiselect" => true,
            "support_term_advanced_multiselect" => true,
            "favorites_only_advanced" => 0,
            "showSSDIV" => "yes",
            "displayColumns" => "NAME|ACCOUNT_NAME|STATUS|QUANTITY|DISCOUNT_USDOLLAR|LIST_USDOLLAR|DATE_PURCHASED|DATE_SUPPORT_EXPIRES|DATE_ENTERED|TYPE_NAME",
            "hideTabs" => "CATEGORY_NAME|CONTACT_NAME|QUOTE_NAME|SERIAL_NUMBER|TEAM_NAME",
            "orderBy" => "NAME",
            "sortOrder" => "ASC",
            "button" => "Search",
        );

        $GLOBALS['module'] = "Products";
        SugarTestProductTypesUtilities::createType(false, '1');
        SugarTestProductTypesUtilities::createType(false, '2');
        SugarTestProductTypesUtilities::createType(false, '3');

        //creating test product
        SugarTestProductUtilitiesWithTypes::createProduct("1");
        SugarTestProductUtilitiesWithTypes::createProduct("2");
        SugarTestProductUtilitiesWithTypes::createProduct("3");
    }

    public function tearDown()
    {
        $GLOBALS['db']->query("DELETE FROM products_audit WHERE parent_id = '{$this->_product->id}'");
        SugarTestProductUtilitiesWithTypes::removeAllCreatedProducts();
        SugarTestProductTypesUtilities::removeAllCreatedtypes();

        unset($GLOBALS['app_list_strings']);
    }

    public function testListViewDataCreated()
    {
        $this->assertTrue(class_exists("ListViewData"));
        $this->assertInstanceOf("ListViewData", $this->_lvd);
        $this->assertInstanceOf("Product", $this->_product);
    }

    public function testListViewDataCorrectOrder()
    {

        $this->markTestIncomplete('SFA Team: getListViewData is being called improperly, with the second call leading to an error because of a require_once.');
        $filterFields = array(
            "name" => 1,
            "account_name" => 1,
            "account_id" => 1,
            "status" => 1,
            "quantity" => 1,
            "discount_usdollar" => 1,
            "currency_id" => 1,
            "list_usdollar" => 1,
            "date_purchased" => 1,
            "date_support_expires" => 1,
            "date_entered" => 1,
            "type_name" => 1,
            "favorites_only" => 1,
        );

        $params = array(
            "massupdate" => 1,
            "orderBy" => "NAME",
            "overrideOrder" => 1,
            "sortOrder" => "ASC",
        );

        $data = $this->_lvd->getListViewData($this->_product, '', 0, -1, $filterFields, $params);
        $this->assertNotEmpty($data['data']);

        $this->_checkIfProductsOrderedCorrect($data['data'],strtolower($_REQUEST["lvso"]));

        $_REQUEST["lvso"] = "desc";
        $data = $this->_lvd->getListViewData($this->_product, '', 0, -1, $filterFields, $params);
        $this->assertNotEmpty($data['data']);
        $this->_checkIfProductsOrderedCorrect($data['data'],strtolower($_REQUEST["lvso"]));
    }

    /**
     *
     * Whether products were ordered correct
     * @param array $products products data'
     * @param string $order 'asc' | 'desc'
     */
    private function _checkIfProductsOrderedCorrect($products, $order)
    {
        $orderedCorrect = true;
        $previousType = -1;
        if ($order == "asc")
        {
            foreach($products as $item)
            {
                if ($previousType >= $item['TYPE_NAME'])
                {
                    $orderedCorrect = false;
                }
            }
        }
        elseif ($order == "desc") {
            foreach($products as $item)
            {
                if ($previousType >= $item['TYPE_NAME'])
                {
                    $orderedCorrect = false;
                }
            }
        }
        $this->assertTrue($orderedCorrect);
    }
}

/**
 * Create a products with type
 * @author alex
 *
 */
class RLISugarTestProductUtilitiesWithTypes extends SugarTestProductUtilities
{

    /**
     * Get type id by type name
     * @param string $typeName type name
     */
    public static function getTypeId($typeName)
    {
        static $typesList;

        if (!$typesList)
        {
            $productType = new ProductType();
            $typesList = $productType->get_product_types();
        }

        return array_search($typeName, $typesList);
    }

    /**
     *
     * Create a product
     * @param string $typeName type of created product will be
     * @param int $id id of created product
     */
    public static function createProduct($typeName, $id = '')
    {
        $time = mt_rand();
        $name = 'SugarProduct';
        $product = new Product();
        $product->name = $name . $time;
        $product->tax_class = 'Taxable';
        $product->cost_price = '100.00';
        $product->list_price = '100.00';
        $product->discount_price = '100.00';
        $product->quantity = '100';
        $product->type_id = self::getTypeId($typeName);
        if(!empty($id))
        {
            $product->new_with_id = true;
            $product->id = $id;
        }
        $product->save();
        self::$_createdProducts[] = $product;
        return $product;
    }
}
