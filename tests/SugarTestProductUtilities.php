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
 
require_once 'modules/Products/Product.php';

class SugarTestProductUtilities
{
    protected static $_createdProducts = array();

    private function __construct() {}

    public static function createProduct($id = '') 
    {
        $time = mt_rand();
    	$name = 'SugarProduct';
    	$product = new Product();
        $product->currency_id = '-99';
        $product->name = $name . $time;
        $product->tax_class = 'Taxable';
        $product->cost_price = '100.00';
        $product->list_price = '100.00';
        $product->discount_price = '100.00';
        $product->quantity = '100';


        if(!empty($id))
        {
            $product->new_with_id = true;
            $product->id = $id;
        }
        $product->save();
        self::$_createdProducts[] = $product;
        return $product;
    }

    public static function setCreatedProduct($product_ids) {
    	foreach($product_ids as $product_id) {
    		$product = new Product();
    		$product->id = $product_id;
        	self::$_createdProducts[] = $product;
    	} // foreach
    } // fn
    
    public static function removeAllCreatedProducts() 
    {
        $db = DBManagerFactory::getInstance();
        $product_ids = self::getCreatedProductIds();
        $db->query("DELETE FROM products WHERE id IN ('" . implode("', '", $product_ids) . "')");
        $db->query("DELETE FROM products_audit WHERE parent_id IN ('" . implode("', '", $product_ids) . "')");
        $db->query("DELETE FROM forecast_worksheets WHERE parent_type = 'Products' and parent_id IN ('" . implode("', '", $product_ids) . "')");
    }
        
    public static function getCreatedProductIds() 
    {
        $product_ids = array();
        foreach (self::$_createdProducts as $product) {
            $product_ids[] = $product->id;
        }
        return $product_ids;
    }
}
?>
