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
*/
class TenantPeriod extends Basic{
	
	var $id;

	var $category;
	var $date_from;
	var $date_to;
	
	var $table_name = "tenant_periods";
	var $object_name = "TenantPeriod";
	var $module_dir = 'TenantPeriods';
	var $importable = true;
	
	var $new_schema = true;
	
	function TenantPeriod(){
		parent::SugarBean();
	}
	
	function bean_implements($interface){
		switch ($interface){
			case 'ACL' : return true;
			default : return false;
		}
	}
	
	function get_summary_text(){
		return $this->category;
	}
	
	function save($check_notify = false){
		parent::save($check_notify);
	}
	
	
}
