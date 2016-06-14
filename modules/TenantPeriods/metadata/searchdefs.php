<?php
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
*  @date  2016-6-14
*/
$searchdefs['TenantPeriods'] = array(
	'layout' => array(
		'basic_search' => array(
			'name' => array('name'=>'name','default'=>true,'width'=>'10%')
		),
		'advanced_search' => array(
			'name'=>array('name'=>'name','default'=>true,'width'=>'10%'),
			'tenant_user_name' => array('name'=>'tenant_user_name','label'=>'LBL_TENANT_USER_NAME','type'=>'name','default'=>true,'width'=>'10%'),
			'category' => array('name'=>'category','default'=>true,'width'=>'15%'),
			'date_from' => array('name'=>'date_from','default'=>false,'width'=>'10%'),
			'date_to' => array('name'=>'date_to','default'=>false,'width'=>'10%'),
		),
	),
	'templateMeta' => array(
		'maxColumns' => '3',
		'maxColumnsBasic' => '4',
		'widths' => array(
			'label' => '10',
			'field' => '30'
		),
	),
);
