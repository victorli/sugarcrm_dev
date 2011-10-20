<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2011 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


if(!ACLController::checkAccess('Calendar', 'list', true)){
	ACLController::displayNoAccess(true);
}

require_once('modules/Calendar/Calendar.php');
require_once('modules/Calendar/CalendarDisplay.php');
require_once("modules/Calendar/CalendarGrid.php");

global $cal_strings, $app_strings, $app_list_strings, $current_language, $timedate, $sugarConfig;
$cal_strings = return_module_language($current_language, 'Calendar');

if(empty($_REQUEST['view'])){
	$_REQUEST['view'] = SugarConfig::getInstance()->get('calendar.default_view','week');
}

$args = array();
$args['view'] = $_REQUEST['view'];
$args['cal'] = new Calendar($args['view']);


if($_REQUEST['view'] == 'day' || $_REQUEST['view'] == 'week' || $_REQUEST['view'] == 'month'){
	$args['cal']->add_activities($GLOBALS['current_user']);	
}else if($_REQUEST['view'] == 'shared'){
	$args['cal']->init_shared();	
	global $shared_user;				
	$shared_user = new User();	
	foreach($args['cal']->shared_ids as $member){
		$shared_user->retrieve($member);
		$args['cal']->add_activities($shared_user);
	}
}

if(in_array($args['cal']->view, array("day","week","month","shared"))){
	$args['cal']->load_activities();
}

$ed = new CalendarDisplay($args);
$ed->display_title();

if(in_array($args['cal']->view, array("day","week","month","shared","year"))){
	if($args['cal']->view == "shared")
		$ed->display_shared_html();
	$ed->display_calendar_header();
	$ed->display();
	$ed->display_calendar_footer();
}	

?>
