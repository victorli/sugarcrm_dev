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
*  @date  2016-6-27
*/
class ProductFeatureValues extends SugarBean{
	var $id;
	var $fid;
	var $is_default;
	var $value;
	
	var $table_name = 'product_feature_values';
	var $module_dir = 'ProductFeatures';
	var $object_name ='ProductFeatureValues';
	
	function ProductFeatureValues(){
		global $dictionary;
		
		require_once 'metadata/product_feature_valuesMetaData.php';
		
		parent::SugarBean();
	}
	
	function get_summary_text(){
		return $this->value;
	}
}