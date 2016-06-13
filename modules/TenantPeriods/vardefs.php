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
		'id' => array(
			'name' => 'id',
			'vname' => 'LBL_ID',
			'required' => true,
			'type' => 'id',
			'reportable' => false,
			'comment' => 'Unique identifier'
		),
		'name' => array(
			'name' => 'name',
			'vname' => 'LBL_NAME',
			'type' => 'name',
			'dbType' => 'varchar',
			'len' => 50,
			'unified_search' => true,
			'full_text_search' => array('boost'=>3),
			'importable' => 'required',
			'required' => true,
		),
		'tenant_user_id' => array(
			'name' => 'tenant_user_id',
			'vname' => 'LBL_TENANT_USER_ID',
			'type' => 'id',
			'group' => 'tenant_user_name',
			'required' => true,
			'comment' => 'The tenant id of this record creator'
		),
		'tenant_user_name' => array(
			'name' => 'tenant_user_name',
			'rname' => 'user_name',
			'source' => 'non-db',
			'len' => '120',
			'group' => 'tenant_user_name',
			'vname' => 'LBL_TENANT_USER_NAME',
			'reportable' => false,
			'id_name' => 'tenant_user_id',
			'join_name' => 'users',
			'type' => 'relate',
			'module' => 'Users',
			'link' => 'users',
			'table' => 'users',
		),
		'date_entered' => array(
			'name' => 'date_entered',
			'vname' => 'LBL_DATE_ENTERED',
			'type' => 'datetime',
			'required' => true,
			'comment' => 'Date record created'
		),
		'date_modified' => array(
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'required' => true,
			'comment' => 'Date record last modified'
		),
		'modified_user_id' => array(
			'name' => 'modified_user_id',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_MODIFIED',
			'type' => 'assigned_user_name',
			'table' => 'modified_user_id_users',
			'isnull' => 'false',
			'dbType' => 'id',
			'required' => false,
			'len' => 36,
			'reportable' => true,
			'comment' => 'User who last modified record'
		),
		'created_by' => array(
			'name' => 'created_by',
			'rname' => 'user_name',
			'id_name' => 'created_by',
			'vname' => 'LBL_CREATED',
			'type' => 'assigned_user_name',
			'table' => 'created_by_users',
			'isnull' => 'false',
			'dbType' => 'id',
			'len' => 36,
			'comment' => 'User id who created record'
		),
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
		'deleted' => array(
			'name' => 'deleted',
		    'vname' => 'LBL_DELETED',
		    'type' => 'bool',
		    'reportable'=>false,
		    'comment' => 'Record deletion indicator'
		),
		'users'=>	array(
			'name' => 'users',
			'type' => 'link',
			'relationship' => 'user_tenantperiods',
			'source'=>'non-db',
			'side'=>'right',
			'vname'=>'LBL_USERS',
		),
		
	),
	'indices' => array(
		array('name'=>'idx_tp_tenant_id','type'=>'index','fields'=>array('tenant_user_id')),
		array('name'=>'idx_tp_category','type'=>'index','fields'=>array('category'))
	),
);
 //VardefManager::createVardef('TenantPeriods','TenantPeriod', array('default', 'assignable',''));
