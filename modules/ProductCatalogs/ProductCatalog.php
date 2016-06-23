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
*  @date  2016-6-21
*/
class ProductCatalog extends SugarBean{
	var $id;
	var $name;
	
	var $visible;
	var $parent_id;
	var $description;
	
	var $cover_image;
	var $thumbnail;
	
	var $table_name = "product_catalogs";
	var $object_name = "ProductCatalog";
	var $module_dir = "ProductCatalogs";
	
	var $new_schema = true;
	
	function ProductCatalog(){
		parent::SugarBean();
	}
	
	function bean_implements($interface){
		switch ($interface){
			case 'ACL': return true;
			default: return false;
		}
	}
	
	function getParentsDropdown($select_id=0){
		global $current_user,$mod_strings;
		
		$sql = "select * from $this->table_name where deleted=0 and visible=1 ";
		
		if(is_sys_admin($current_user)){
			$where .= " AND 1=1";
		}elseif(is_tenant($current_user)){
			$where .= " AND tenant_id='".$current_user->id."'";
		}else{
			$where .= " AND tenant_id='".$current_user->tenant_id."'";
		}
		$orderby = " order by id asc";
		
		$GLOBALS['log']->debug($sql.$where.$orderby);
		
		$r = $this->db->query($sql . $where . $orderby);
		$parents_select = "<select id='parent_id' name='parent_id'>";
		$parents_select .= "<option value='0'>".$mod_strings['LBL_ROOT']."</option>";
			while ($d = $this->db->fetchByAssoc($r)){
				$selected = "";
				if($d['id'] == $select_id)
					$selected = "selected";
				$parents_select .= "<option value='".$d['id']."' $selected>".$d['name']."</option>";
			}
		$parents_select .= "</select>";
		
		return $parents_select;
	}
	
	function get_summary_text(){
		return $this->name;
	}
	
	function save($check_notify = false){
		
		//process cover image
		if(isset($_FILES['cover_image']) && !empty($_FILES['cover_image']['name'])){
			$r = $this->save_photo('cover_image');
			if($r['result']){
				$this->cover_image = $r['message'];
			}else{
				sugar_die($r['message']);
			}
			//try to delete old cover image
			if(!empty($this->id) && isset($_REQUEST['old_cover_image']) && !empty($_REQUEST['old_cover_image'])){
				unlink($_REQUEST['old_cover_image']);
			}
		}
		//process thumbnail
		if(isset($_FILES['thumbnail']) && !empty($_FILES['thumbnail']['name'])){
			$r = $this->save_photo('thumbnail');
			if($r['result'])
				$this->thumbnail = $r['message'];
			else
				sugar_die($r['message']);
				
			if(!empty($this->id) && isset($_REQUEST['old_thumbnail']) && !empty($_REQUEST['old_thumbnail'])){
				unlink($_REQUEST['old_thumbnail']);
			}
		}
		
		parent::save($check_notify);
	}
}
