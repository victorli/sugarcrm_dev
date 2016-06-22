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
*  @date  2016-6-22
*/
class ProductCatalogsViewEdit extends ViewEdit{
	function display(){
		if(empty($this->bean->id))
			$isNew = true;
		else 
			$isNew = false;
			
		$this->ss->assign('PARENTS_DROPDOWN',$this->bean->getParentDropdown($this->bean->parent_id));
		$this->ev->process();
		echo $this->ev->display($this->showTitle);
	}
}
