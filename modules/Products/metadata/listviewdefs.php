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
*  @date  2016-6-21
*/
$listViewDefs['ProductCatalogs'] = array(
	'KEY' => array(
		'width' => 10,
		'label' => 'LBL_KEY',
		'default' => true
	),	
	'NAME' => array(
		'width' => 15,
		'label' => 'LBL_SUBJECT',
		'link' => true,
		'default' => true
	),
	'BARCODE' => array(
		'width' => 15,
		'label' => 'LBL_BARCODE',
		'default' => true,
	),
	'ACTIVE' => array(
		'width' => 20,
		'label' => 'LBL_ACTIVE',
		'default' => true
	),
	'DATE_ENTERED' => array(
		'width'=>10,
		'label' => 'LBL_LIST_DATE_ENTERED',
		'default' => true
	),
	'CREATED_BY_NAME' => array(
		'width' => '10',
		'label' => 'LBL_LIST_CREATED_BY_NAME',
		'default' => true,
	),
);
