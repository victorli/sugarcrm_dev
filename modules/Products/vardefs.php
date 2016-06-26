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

$dictionary['Product'] = array(
	'table'	=>	'products',
	'comment'	=>	'products store',
	'fields'	=> array(
		'id' => array(
			'name' => 'id',
			'vname' => 'LBL_ID',
			'type' => 'id',
			'required' => true,
			'reportable' => true,
			'comment' => 'Unique identifier'
		),
		'name' => array(
			'name' => 'name',
			'vname' => 'LBL_SUBJECT',
			'type' =>'name',
			'dbType' => 'varchar',
			'len' => 255,
			'unified_search' => true,
			'full_text_search' => array('boost'=>3),
			'importable' => 'required',
			'required' => true
		),
		'pkey' => array(
			'name' => 'pkey',
			'vname' => 'LBL_PKEY',
			'type' => 'int',
			'required' => true,
			'len' => '11',
			'auto_increment' => true,
			'importable' => 'required',
			'massupdate' => false,
			'comment' => 'The unique internal ID of each product'
		),
		'barcode' => array(
			'name' => 'barcode',
			'vname' => 'LBL_BARCODE',
			'type' => 'text',
			'requried' => false,
			'dbType' => 'varchar',
			'len' => '36',
			'importable' => 'required',
			'massupdate' => false,
			'comment' => 'The unique global bar code of each product'
		),
		'active' => array(
			'name' => 'active',
			'vname' => 'LBL_ACTIVE',
			'type' => 'bool',
			'reportable' => true,
			'massupdate' => true,
			'default' => true,
		),
		'summary' => array(
			'name' => 'summary',
			'vname' => 'LBL_SUMMARY',
			'required' => false,
			'massupdate' => false,
			'type' => 'text',
			'dbType' => 'varchar',
			'len' => 255,
		),
		'description' => array(
			'name' => 'description',
			'vname' => 'LBL_DESCRIPTION',
			'required' => false,
			'massupdate' => false,
			'type' => 'text',
			'dbType' => 'varchar',
			'len' => 500,
		),
		'tags' => array(
			'name' => 'tags',
			'vname' => 'LBL_TAGS',
			'required' => false,
			'massupdate' => true,
			'type' => 'text',
			'dbType' => 'varchar',
			'len' => 255,
		),
		'tenant_id' => array(
			'name' => 'tenant_id',
			'vname' => 'LBL_TENANT_ID',
			'dbType' => 'id',
			'reportable' => true,
			'massupdate' => false,
		),
		'date_entered' => array(
			'name' => 'date_entered',
			'vname' => 'LBL_DATE_ENTERED',
			'type'  => 'datetime',
			'group' => 'created_by_name',
			'enable_range_search' => true,
			'options' => 'date_range_search_dom',
		),
		'date_modified' => array(
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'group' => 'tenant_by_name',
			'enable_range_search' => true,
			'options' => 'date_range_search_dom',
		),
		'modified_user_id' => array(
			'name' => 'modified_user_id',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_MODIFIED',
			'type' => 'assigned_user_name',
			'table' => 'users',
			'isnull' => 'false',
			'group' => 'modified_by_name',
			'dbType' => 'id',
			'reportable' => true,
			'massupdate' => false,
		),
		'modified_by_name' => array(
			'name' => 'modified_by_name',
			'vname' =>'LBL_MODIFIED_NAME',
			'type' =>'relate',
			'reportable' => false,
			'source' => 'non-db',
			'rname' => 'user_name',
			'table' =>'users',
			'id_name' =>'modified_user_id',
			'module' => 'Users',
			'link' => 'modified_user_link',
			'duplicate_merge' => 'disabled',
			'massupdate' => false,
		),
		'created_by' => array(
			'name' => 'created_by',
			'rname' => 'user_name',
			'id_name' => 'modified_user_id',
			'vname' => 'LBL_CREATED',
			'type' => 'assigned_user_name',
			'table' => 'users',
			'isnull' => false,
			'dbType' => 'id',
			'group' => 'created_by_name',
			'massupdate' => false,
		),
		'created_by_name' => array(
			'name' => 'created_by_name',
			'rname' => 'user_name',
			'id_name' => 'created_by',
			'vname' => 'LBL_CREATED',
			'type' => 'relate',
			'source' => 'non-db',
			'table' => 'users',
			'link' =>'created_by_link',
			'module' => 'Users',
			'duplicate_merge' => 'disabled',
			'importable' => false,
			'massupdate' => false,
		),
		'deleted' => array(
			'name' => 'deleted',
			'vname' => 'LBL_DELETED',
			'type' => 'bool',
			'default' => '0',
			'importable' => true,
			'massupdate' => true,
		),
		'modified_user_link'=>array(
			'name' => 'modified_user_link',
			'type' => 'link',
			'relationship' => 'p_modified_user',
			'vname' => 'LBL_MODIFIED_USER',
			'link_type' => 'one',
			'module' => 'Users',
			'bean_name' => 'User',
			'source' => 'non-db',
		),
		'created_by_link'=>array(
			'name' => 'created_by_link',
			'type' => 'link',
			'relationship' => 'p_created_by',
			'vname' => 'LBL_CREATED_USER',
			'link_type' => 'one',
			'module' => 'Users',
			'bean_name' => 'User',
			'source' => 'non-db'
		),
		
	),
	'indices' => array(
		array('name'=>'idx_tp_id','type'=>'index','fields'=>array('id')),
		array('name'=>'idx_tp_pkey','type'=>'index','fields'=>array('pkey')),
		array('name'=>'idx_tp_barcode','type'=>'index','fields'=>array('barcode')),
		array('name'=>'idx_tp_tenant_id','type'=>'index','fields'=>array('tenant_id')),
	),
	'relationships' => array(
		'p_created_by' => array(
			'lhs_module' => 'Users','lhs_table'=>'users','lhs_key'=>'id',
			'rhs_module' => 'Products','rhs_table' => 'products','rhs_key'=>'created_by',
			'relationship_type' => 'one-to-many',
		),
		'p_modified_user' => array(
			'lhs_module' => 'Users','lhs_table'=>'users','lhs_key'=>'id',
			'rhs_module' => 'Products','rhs_table'=>'products','rhs_key'=>'modified_user_id',
			'relationship_type'=>'one-to-many',
		),
	),
);
