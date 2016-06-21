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
class TenantPeriod extends SugarBean{
	
	var $id;
	var $name;
	
	var $category;
	var $date_from;
	var $date_to;
	
	var $tenant_user_id;
	var $tenant_user_name;
	
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
		return $this->name;
	}
	
	function save($check_notify = false){
		parent::save($check_notify);
	}

	public static function checkAccess($category,$action=null){
		global $current_user;
		

		if(is_sys_admin($current_user)) return true;
	
		$tenant_id = null;
		if(is_tenant($current_user))
			$tenant_id = $current_user->id;
		else
			$tenant_id = $current_user->tenant_id;

		if($tenant_id == 1){ //inner employee 
			//TODO leave blank for further using
			return true;
		}
		
		$sql = "select DATEDIFF(MAX(date_to),DATE(NOW())) from tenant_periods where tenant_user_id='".$tenant_id."' AND category='".$category."' AND deleted=0";
		$r = DBManagerFactory::getInstance()->fetchOne($sql);

		if($r == false)
			return true;//no record to limit for this category

		if($r[0] >= 0)
			return true;

		return false;
	}
	
	
}
