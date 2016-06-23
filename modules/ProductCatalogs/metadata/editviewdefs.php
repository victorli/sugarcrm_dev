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
$viewdefs['ProductCatalogs']['EditView'] = array(
	'templateMeta' => array(
		'maxColumns' => '2',
		'widths' => array(
			array('label' => '10','field'=>'30'),
			array('label' => '10','field'=>'30')
		),
		'form' => array(
			'enctype' => 'multipart/form-data'
		),
	),
	'panels' => array(
		'LBL_PRODUCT_CATALOG_INFORMATION' => array(
			array(
				array('name' => 'name','label' => 'LBL_SUBJECT'),	
			),
			array(
			  array('name' => 'parent_id','label' => 'LBL_PARENT_NAME','customCode' => '{$PARENTS_DROPDOWN}'),
			  array('name' => 'visible','label' => 'LBL_VISIBLE'),
			),
			array(
				array('name'=>'cover_image','label'=>'LBL_COVER_IMAGE','type'=>'photo','displayParams'=>array('id'=>'id')),
				array('name'=>'thumbnail','label'=>'LBL_THUMBNAIL','type'=>'photo','displayParams'=>array('id'=>'id')),
			),
			array(
				array('name'=>'description','label'=>'LBL_DESCRIPTION'),
			),
		),
	),
);
