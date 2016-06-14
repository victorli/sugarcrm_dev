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
*  @date  2016-6-13
*/

$viewdefs['TenantPeriods']['DetailView'] = array(
	'templateMeta' => array(
		'form' => array(
			'buttons'=>array(
				'EDIT',
				'DELETE',
				/*array(
					'sugar_html' => array(
						'type' => 'submit',
						'value' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
						'id' => 'close_and_create_new_button',
						'class' => 'button',
						'onclick' => 'this.form.action.value=\'Save\'; this.form.return_module.value=\'TenantPeriods\''
					)
				)*/),
			),
		'maxColumns' => '2',
		'widths' => array(
			array('label'=>10,'field'=>30),
			array('label'=>'10','field'=>'30'),
			),
		'includes' => array(
			//array('file' => 'modules/TenantPeriods/TP.js'),
			),
	),
	'panels' => array(
		'LBL_TENANT_PERIOD_INFORMATION' => array(
			array(
				array('name' => 'name','label' => 'LBL_SUBJECT'),
				array('name' => 'category','label' => 'LBL_CATEGORY')
			),
			array(
				'date_from',
				'date_to'
			),
		),
		'LBL_PANEL_ASSIGNMENT' => array(
			array(
				array('name'=>'assigned_user_name','label'=>'LBL_ASSIGNED_TO'),
			),
			array(
				array('name'=>'date_entered','customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}','label'=>'LBL_DATE_ENTERED'),
				array('name'=>'date_modified','customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}','label'=>'LBL_DATE_MODIFIED'),
			),
		),
	),
);
