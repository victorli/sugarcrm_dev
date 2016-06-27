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
$$dictionary['ProductFeatureValues'] = array(
	'table' => 'product_feature_values',
	'fields' => array(
		'id' => array(
			'name'		=> 'id',
			'vname'		=> 'LBL_ID',
			'type'		=> 'id',
			'required'	=> true,
		),
		'fid' => array(
			'name' => 'fid',
			'vname' => 'LBL_FID',
			'type' => 'id',
			'required' => true,
		),
		'value' => array(
			'name' => 'value',
			'vname' => 'LBL_VALUE',
			'type' => 'text',
			'dbType' => 'varchar',
			'len' => '128',
			'required' => true,
		),
		'is_default' => array(
			'name' => 'is_default',
			'vname' => 'LBL_IS_DEFAULT',
			'type' => 'bool',
			'default' => false,
		),
		'tenant_id' => array(
			'name' => 'tenant_id',
			'vname' => 'LBL_TENANT_ID',
			'dbType' => 'id',
			'reportable' => true,
			'massupdate' => false,
		),
		'date_entered' => array (
			'name' => 'date_entered',
			'vname' => 'LBL_DATE_ENTERED',
			'type' => 'datetime',
			'required'=>true,
		),
		'date_modified' => array (
			'name' => 'date_modified',
			'vname' => 'LBL_DATE_MODIFIED',
			'type' => 'datetime',
			'required'=>true,
		),
		'deleted' => array (
			'name' => 'deleted',
			'vname' => 'LBL_DELETED',
			'type' => 'bool',
			'required' => false,
			'reportable'=>false,
		),
	),
	'indices' => array(
		array(
			'name' => 'pfvpk',
			'type' =>'primary',
			'fields' => array('id')
		),
		array(
			'name' => 'idx_pfv_fid',
			'type' => 'index',
			'fields' => array('fid')
		)
	),
);