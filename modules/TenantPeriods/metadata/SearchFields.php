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
*  @date  2016-6-17
*/
$searchFields['TenantPeriods'] = array(
	'name' => array('query_type'=>'default'),
	'tenant_user_name' => array('query_type'=>'default','db_field'=>array('first_name','last_name'),'force_unifiedsearch'=>true),
	'category' => array('query_type' => 'default'),

	'range_date_from' => array('query_type'=>'default','enable_range_search'=>true,'is_date_field'=>true),
	'start_range_date_from' => array('query_type'=>'default','enable_range_search'=>true,'is_date_field'=>true),
	'end_range_date_from' => array('query_type'=>'default','enable_range_search'=>true,'is_date_field'=>true),
	'range_date_to' => array('query_type'=>'default','enable_range_search'=>true,'is_date_field'=>true),
	'start_range_date_to' => array('query_type'=>'default','enable_range_search'=>true,'is_date_field'=>true),
	'end_range_date_to' => array('query_type'=>'default','enable_range_search'=>true,'is_date_field'=>true),
);