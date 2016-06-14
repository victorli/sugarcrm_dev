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

$dictionary['TenantPeriod'] = array(
	'table'	=>	'tenant_periods',
	'comment'	=>	'Determine the rent period of modules of each tenant',
	'fields'	=> array(
		'category' => array(
			'name' => 'category',
			'vname' => 'LBL_CATEGORY',
			'type' => 'enum',
			'options' => 'tenant_category_dom',
			'len' => 100,
			'reportable' => true,
			'required' => true,
			'comment' => 'Category of the tenant rented(usually the name of a module)'
		),
		'date_from' => array(
			'name' => 'date_from',
			'vname' => 'LBL_DATE_FROM',
			'type' => 'date',
			'required' => true,
			'enable_range_search' => true,
			'options' => 'date_range_search_dom',
			'comment' => 'The rent period start date'
		),
		'date_to' => array(
			'name' => 'date_to',
			'vname' => 'LBL_DATE_TO',
			'type' => 'date',
			'required' => true,
			'validation' => array('type' => 'isAfter','compareto'=>'date_from','blank'=>false),
			'enable_range_search' => true,
			'options' => 'date_range_search_dom',
			'comment' => 'The rent period end date'
		),
		
	),
	'indices' => array(
		array('name'=>'idx_tp_tenant_id','type'=>'index','fields'=>array('tenant_user_id')),
		array('name'=>'idx_tp_category','type'=>'index','fields'=>array('category'))
	),
);
 VardefManager::createVardef('TenantPeriods','TenantPeriod', array('default', 'assignable',''));
