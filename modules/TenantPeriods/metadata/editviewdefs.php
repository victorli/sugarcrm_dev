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
*  @date  2016-6-11
*/
$viewdefs['TenantPeriods']['EditView'] = array(
	'templateMeta' => array(
		'maxColumns' => '2',
		'widths' => array(
			array('label' => '10','field'=>'30'),
			array('label' => '10','field'=>'30')
		),
		'form' => array(
			'headerTpl' => 'modules/TenantPeriods/tpls/EditViewHeader.tpl',
			'footerTpl' => 'modules/TenantPeriods/tpls/EditViewFooter.tpl'
		),
	),
	'panels' => array(
		'LBL_TENANTPERIOD_INFORMATION' => array(
			array(
				array('name'=>'tenant_name','label'=>'LBL_TENANT_NAME'),
				array('name'=>'category','label'=>'LBL_CATEGORY')
			),
			array(
				array('name'=>'date_from','label'=>'LBL_DATE_FROM'),
				array('name'=>'date_to','label'=>'LBL_DATE_TO')
			),
		),
	),
);

