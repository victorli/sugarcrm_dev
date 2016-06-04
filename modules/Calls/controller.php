<?php
/*
* 2014 eCartx
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
class CallsController extends SugarController{
	function CallsController(){
		parent::SugarController();
	}
	
	function action_detailview(){
		if(is_admin($GLOBALS['current_user']) && !is_tenant($GLOBALS['current_user'])
		 || is_createdByUser(new Call(), $_REQUEST['record'], $GLOBALS['current_user']->id)){
		 	$this->view = 'detail';
		 }else{
		 	sugar_die("Unauthorized access to employees.");
		 }
		 
		 return true;
	}
}