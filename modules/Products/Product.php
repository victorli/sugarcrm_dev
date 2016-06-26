<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
* 2016 eCartx
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@ecartx.com so we can send you a copy immediately.
*
*  @author BLX90 <zs.li@blx90.com>
*  @copyright 2014 BLX90
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  @date  2016-6-21
*/
class Product extends SugarBean{
	var $id;
	var $name;
	var $key; //the auto increment id
	
	var $barcode; //global unique barcode
	var $active;
	var $summary; //short description
	var $description;
	var $tags; //more tags seperated by comma
	
	var $table_name = "products";
	var $object_name = "Product";
	var $module_dir = "Products";
	
	var $new_schema = true;
	
	function Product(){
		parent::SugarBean();
	}
	
	function bean_implements($interface){
		switch ($interface){
			case 'ACL': return true;
			default: return false;
		}
	}
	
	function get_summary_text(){
		return $this->name;
	}
	
	function save($check_notify = false){
		
		parent::save($check_notify);
	}
}
