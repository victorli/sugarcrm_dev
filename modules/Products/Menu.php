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
*  @date  2016-6-13
*/

global $mod_strings,$app_strings;

if(ACLController::checkAccess('Products','edit',true)) $module_menu[] = array("index.php?module=Products&action=EditView&return_module=Products&return_action=DetailView",$mod_strings['LNK_NEW_PRODUCT'],"New Product");
if(ACLController::checkAccess('Products', 'list', true))$module_menu[]=Array("index.php?module=Products&action=index&return_module=Products&return_action=DetailView", $mod_strings['LNK_PRODUCT_LIST'],"Products");
