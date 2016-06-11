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
		'tenant_id' => array(
			'name' => 'tenant_id',
			'vname' => 'LBL_TENANT_ID',
			'type' => 'id',
			'group' => 'tenant_name',
			'required' => true,
			'comment' => 'The tenant id of this record creator'
		),
		'tenant_name' => array(
			'name' => 'tenant_name',
			'rname' => 'name',
			'source' => 'non-db',
			'len' => '120',
			'group' => 'tenant_name',
			'vname' => 'LBL_TENANT_NAME',
			'reportable' => false,
			'id_name' => 'tenant_id',
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
			'type' => 'varchar',
			'len' => 100,
			'reportable' => true,
			'comment' => 'Category of the tenant rented(usually the name of a module)'
		),
		'date_from' => array(
			'name' => 'date_from',
			'vname' => 'LBL_DATE_FROM',
			'type' => 'datetime',
			'required' => true,
			'comment' => 'The rent period start date'
		),
		'date_to' => array(
			'name' => 'date_to',
			'vname' => 'LBL_DATE_TO',
			'type' => 'datetime',
			'required' => true,
			'comment' => 'The rent period end date'
		),
		'deleted' => array(
			'name' => 'deleted',
		    'vname' => 'LBL_DELETED',
		    'type' => 'bool',
		    'reportable'=>false,
		    'comment' => 'Record deletion indicator'
		),
		
	),
	'indices' => array(),
);