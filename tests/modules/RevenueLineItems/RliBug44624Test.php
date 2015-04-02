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


class RLIBug44624Test extends Sugar_PHPUnit_Framework_TestCase
{

	private $_product;

	public function setUp() 
	{
    $this->markTestIncomplete("DB Strict Fails, dates are not on the products bean");
		global $current_user;
	    $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->is_admin = 1;
        $current_user->setPreference('dec_sep', '.', 0, 'global');
        $current_user->setPreference('num_grp_sep', ',', 0, 'global');

		require('include/modules.php');
		$GLOBALS['beanList'] = $beanList;
		$GLOBALS['beanFiles'] = $beanFiles;

	    $GLOBALS['module'] = "Products";
		SugarTestProductTypesUtilities::createType(false, '1');
		$this->_product = SugarTestProductUtilitiesWithTypes2::createProduct("1");
        $this->_product->disable_row_level_security = true;
        //Clear out the products_audit table
        $GLOBALS['db']->query("DELETE FROM products_audit WHERE parent_id = '{$this->_product->id}'");
        //$this->useOutputBuffering = false;

	}

	public function tearDown()
	{
		$GLOBALS['db']->query("DELETE FROM products_audit WHERE parent_id = '{$this->_product->id}'");
		SugarTestProductUtilitiesWithTypes2::removeAllCreatedProducts();
		SugarTestProductTypesUtilities::removeAllCreatedtypes();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->_product);
	}

    public function testProductListPriceChanges() {
        $this->_product->list_price = 0;
        $this->_product->save();
        $this->_product->retrieve();

        $this->_product->list_price = 0.00;
        $this->_product->save(); 
        $this->_product->retrieve();

        $this->_product->list_price = "";
        $this->_product->save(); 
        $this->_product->retrieve();

        $id = $this->_product->id;
        $query = "SELECT * from products_audit where parent_id='{$id}'";
        $result = $GLOBALS['db']->query($query);

        $list_of_changes = array();

          while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
              $list_of_changes[] = 'a' . $row['created_by'] . ' = ' . $row["field_name"] . ',' . $row['before_value_string'] . ',' . $row['after_value_string'];

          }

        //echo var_export($list_of_changes, true);
        // list of audited changes should be empty
        $this->assertEmpty($list_of_changes);
    }


    public function testProductCostPriceChanges() {

        $this->_product->cost_price = 1;    // original cost price is 1
        $this->_product->save();
        $this->_product->retrieve();

        $this->_product->cost_price = 1.00;
        $this->_product->save(); 
        $this->_product->retrieve();

        $this->_product->cost_price = "1";
        $this->_product->save(); 
        $this->_product->retrieve();

        $id = $this->_product->id;
        $query = "SELECT * from products_audit where parent_id='$id'";
        $result = $GLOBALS['db']->query($query);

        $list_of_changes = array();

          while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
              $list_of_changes[] = 'b' . $row['created_by'] . ' = ' . $row["field_name"] . ',' . $row['before_value_string'] . ',' . $row['after_value_string'];

          }

        //echo var_export($list_of_changes, true);
        
        // list of audited changes should be empty
        $this->assertEmpty($list_of_changes);
    }

    public function testProductDiscountPriceChanges() {

 

          $this->_product->discount_price = 3.33;    // original cost price is 3.33
          $this->_product->save();
          $this->_product->retrieve();

          $this->_product->discount_price = "3.33";
          $this->_product->save();
          $this->_product->retrieve();

          $id = $this->_product->id;
          $query = "SELECT * from products_audit where parent_id='$id'";
          $result = $GLOBALS['db']->query($query);

          $list_of_changes = array();

          while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
              $list_of_changes[] = 'c' . $row['created_by'] . ' = ' . $row["field_name"] . ',' . $row['before_value_string'] . ',' . $row['after_value_string'];

          }

          //echo var_export($list_of_changes, true);
          //list of audited changes should be empty
          $this->assertEmpty($list_of_changes);


      }


}
 
/**
 * Create a products with type 
 * @author alex
 *
 */
class RLISugarTestProductUtilitiesWithTypes2 extends SugarTestProductUtilities 
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
	public static function createProduct($typeName, $id = '', $prodName="SugarProduct")
	{
		$time = mt_rand();
		$name = $prodName;
		$product = new Product();
		$product->name = $name . $time;
		$product->tax_class = 'Taxable';
		$product->cost_price = 1;
		$product->list_price = 0;
		$product->discount_price = 3.33;
		$product->quantity = '100';
        $product->status = 'Ship';
		$product->type_id = self::getTypeId($typeName);
		if(!empty($id))
		{
			$product->new_with_id = true;
			$product->id = $id;
		}
		$product->save();
        $product->retrieve();
		self::$_createdProducts[] = $product;
		return $product;
	}
}


