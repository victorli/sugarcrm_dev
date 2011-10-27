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





require_once('include/utils/activity_utils.php');
require_once('modules/Calendar/CalendarUtils.php');
require_once('modules/Calendar/CalendarActivity.php');


class Calendar {
	
	var $view = 'week'; // current view
	var $dashlet = false; // if is displayed in dashlet	
	var $date_time; // current date
	
	var $show_tasks = true;
	var $show_calls = true;	
	var $day_start_time; // working day start time in format '11:00'
	var $day_end_time; // working day end time in format '11:00'

	var $time_step = 60; // time step of each slot in minutes
		
	var $acts_arr = array(); // Array of activities objects	
	var $ActRecords = array(); // Array of activities data to be displayed	
	var $shared_ids = array(); // ids of users for shared view
	
	var $celcount; // working day count of slots 
	var $cells_per_day; // entire 24h day count of slots 
	var $d_start_minutes; // working day start minutes 
	var $d_end_minutes; // working day end minutes
	
	function __construct($view = "day", $time_arr = array()){
		global $current_user, $timedate;	
		
		$this->view = $view;		

		if(!in_array($this->view,array('day','week','month','year','shared')))
			$this->view = 'week';
		
		$date_arr = array();
		if(!empty($_REQUEST['day']))
			$_REQUEST['day'] = intval($_REQUEST['day']);
		if(!empty($_REQUEST['month']))
			$_REQUEST['month'] = intval($_REQUEST['month']);

		if (!empty($_REQUEST['day']))
			$date_arr['day'] = $_REQUEST['day'];
		if (!empty($_REQUEST['month']))
			$date_arr['month'] = $_REQUEST['month'];
		if (!empty($_REQUEST['week']))
			$date_arr['week'] = $_REQUEST['week'];

		if (!empty($_REQUEST['year'])){
			if ($_REQUEST['year'] > 2037 || $_REQUEST['year'] < 1970){
				print("Sorry, calendar cannot handle the year you requested");
				print("<br>Year must be between 1970 and 2037");
				exit;
			}
			$date_arr['year'] = $_REQUEST['year'];
		}

		if(empty($_REQUEST['day']))
			$_REQUEST['day'] = "";
		if(empty($_REQUEST['week']))
			$_REQUEST['week'] = "";
		if(empty($_REQUEST['month']))
			$_REQUEST['month'] = "";
		if(empty($_REQUEST['year']))
			$_REQUEST['year'] = "";

		// if date is not set in request set current date
		if(empty($date_arr) || !isset($date_arr['year']) || !isset($date_arr['month']) || !isset($date_arr['day']) ){	
			$user_today = $timedate->nowDb();
			preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/',$user_today,$matches);
			$date_arr = array(
			      'year' => $matches[1],
			      'month' => $matches[2],
			      'day' => $matches[3],
			);
		}
		
		$current_date_db = $date_arr['year']."-".str_pad($date_arr['month'],2,"0",STR_PAD_LEFT)."-".str_pad($date_arr['day'],2,"0",STR_PAD_LEFT);
		$this->date_time = SugarDateTime::createFromFormat("Y-m-d",$current_date_db);	
				
		$this->show_tasks = $current_user->getPreference('show_tasks');
		if(is_null($this->show_tasks))
			$this->show_tasks = SugarConfig::getInstance()->get('calendar.show_tasks_by_default',true);		
		$this->show_calls = $current_user->getPreference('show_calls');
		if(is_null($this->show_calls))
			$this->show_calls = SugarConfig::getInstance()->get('calendar.show_calls_by_default',true);
	
		$this->day_start_time = $current_user->getPreference('day_start_time');
		if(is_null($this->day_start_time))
			$this->day_start_time = SugarConfig::getInstance()->get('calendar.default_day_start',"08:00");
		$this->day_end_time = $current_user->getPreference('day_end_time');
		if(is_null($this->day_end_time))
			$this->day_end_time = SugarConfig::getInstance()->get('calendar.default_day_end',"19:00");
			
		if($this->view == "day"){
			$this->time_step = SugarConfig::getInstance()->get('calendar.day_timestep',15);
		}else if($this->view == "week" || $this->view == "shared"){
			$this->time_step = SugarConfig::getInstance()->get('calendar.week_timestep',30);
		}else if($this->view == "month"){
			$this->time_step = SugarConfig::getInstance()->get('calendar.month_timestep',60);
		}else{
			$this->time_step = 60;
		}

		$this->calculate_day_range();		
	}
	
	/**
	 * Loads activities data to array
	 */		
	function load_activities(){
		$field_list = CalendarUtils::get_fields();
		
		foreach($this->acts_arr as $user_id => $acts){	
			foreach($acts as $act){										
					$newAct = array();
					$newAct['module_name'] = $act->sugar_bean->module_dir;
					$newAct['type'] = strtolower($act->sugar_bean->object_name);				
					$newAct['user_id'] = $user_id;
					$newAct['assigned_user_id'] = $act->sugar_bean->assigned_user_id;
					$newAct['id'] = $act->sugar_bean->id;	
					$newAct['name'] = $act->sugar_bean->name;
					$newAct['status'] = $act->sugar_bean->status;
					
					if(isset($act->sugar_bean->duration_hours)){
						$newAct['duration_hours'] = $act->sugar_bean->duration_hours;
						$newAct['duration_minutes'] = $act->sugar_bean->duration_minutes;
					}				
					 			
					$newAct['detailview'] = 0;
					$newAct['editview'] = 0;
					
					if($act->sugar_bean->ACLAccess('DetailView'))
						$newAct['detailview'] = 1;						
					if($act->sugar_bean->ACLAccess('Save'))
						$newAct['editview'] = 1;					
						
					if(empty($act->sugar_bean->id)){
						$newAct['detailview'] = 0;
						$newAct['editview'] = 0;
					}					
					
					if($newAct['detailview'] == 1){
						if(isset($field_list[$newAct['module_name']])){
							foreach($field_list[$newAct['module_name']] as $field){
								if(!isset($newAct[$field])){
									$newAct[$field] = $act->sugar_bean->$field;									
									if($act->sugar_bean->field_defs[$field]['type'] == 'text'){									
										$t = $newAct[$field];	
										if(strlen($t) > 300){
											$t = substr($t, 0, 300);
											$t .= "...";
										}			
										$t = str_replace("\r\n","<br>",$t);
										$t = str_replace("\r","<br>",$t);
										$t = str_replace("\n","<br>",$t);
										$newAct[$field] = $t;
									}										
								}
							}					
						}				
					}
					
					$date_field = "date_start";								
					if($newAct['type'] == 'task')
						$date_field = "date_due";
																	
				
					$timestamp = SugarDateTime::createFromFormat($GLOBALS['timedate']->get_date_time_format(),$act->sugar_bean->$date_field,new DateTimeZone('UTC'))->format('U');				
								
					$newAct['timestamp'] = $timestamp;
					$newAct['time_start'] = $GLOBALS['timedate']->fromTimestamp($newAct['timestamp'])->format($GLOBALS['timedate']->get_time_format());

					if(!isset($newAct['duration_hours']) || empty($newAct['duration_hours']))
						$newAct['duration_hours'] = 0;
					if(!isset($newAct['duration_minutes']) || empty($newAct['duration_minutes']))
						$newAct['duration_minutes'] = 0;				
			
					$this->ActRecords[] = $newAct;
			}
		}
	}
	
	/**
	 * Get javascript objects of activities to be displayed on calendar
	 * @return string
	 */
	function get_activities_js(){	
				$field_list = CalendarUtils::get_fields();
				$a_str = "";				
				$ft = true;
				foreach($this->ActRecords as $act){
					if(!$ft)
						$a_str .= ",";						
					$a_str .= "{";		
					$a_str .= '
						"type" : "'.$act["type"].'", 
						"module_name" : "'.$act["module_name"].'",  
						"record" : "'.$act["id"].'",
						"user_id" : "'.$act["user_id"].'",
						"timestamp" : "'.$act["timestamp"].'",
						"time_start" : "'.$act["time_start"].'",
						"record_name": "'.$act["name"].'",'.
					'';
					foreach($field_list[$act['module_name']] as $field){
						if(!isset($act[$field]))
							$act[$field] = "";
						$a_str .= '	"'. $field . '" : "'.$act[$field].'",
					'; 
					}
					$a_str .=	'
						"detailview" : "'.$act["detailview"].'",
						"editview" : "'.$act["editview"].'"
					';
					$a_str .= "}";
					$ft = false;				
				}				
				return $a_str;
	}	
	
	/**
	 * initialize ids of shared users
	 */	
	function init_shared(){
		global $current_user;
		$user_ids = $current_user->getPreference('shared_ids');
		if(!empty($user_ids) && count($user_ids) != 0 && !isset($_REQUEST['shared_ids'])) {
			$this->shared_ids = $user_ids;
		}else if(isset($_REQUEST['shared_ids']) && count($_REQUEST['shared_ids']) > 0){
			$this->shared_ids = $_REQUEST['shared_ids'];
			$current_user->setPreference('shared_ids', $_REQUEST['shared_ids']);
		}else{
			$this->shared_ids = array($current_user->id);				
		}
	}
	
	/**
	 * calculatess count of timeslots per visible day, calculates day start and day end in minutes 
	 */	
	function calculate_day_range(){	
		
		list($hour_start,$minute_start) =  explode(":",$this->day_start_time);		
		list($hour_end,$minute_end) =  explode(":",$this->day_end_time);		

		$this->d_start_minutes = $hour_start * 60 + $minute_start;
		$this->d_end_minutes = $hour_end * 60 + $minute_end;		

		$this->celcount = 0;
		for($i = $hour_start; $i < $hour_end; $i++){
				for($j = 0; $j < 60; $j += $this->time_step){
					if($i*60+$j >= $hour_end*60 + $minute_end)
						break;
					$this->celcount++;
				}
		}
		$this->cells_per_day = 24 * (60 / $this->time_step);
	}
	
	/**
	 * loads array of objects
	 * @param User $user user object
	 * @param string $type
	 */	
	function add_activities($user,$type='sugar'){
		global $timedate;
		$start_date_time = $this->date_time;
		if($this->view == 'week' || $this->view == 'shared'){		
			$start_date_time = CalendarUtils::get_first_day_of_week($this->date_time);
			$end_date_time = $start_date_time->get("+7 days");
		}else if($this->view == 'month'){
			$start_date_time = $this->date_time->get_day_by_index_this_month(0);	
			$end_date_time = $start_date_time->get("+".$start_date_time->format('t')." days");
			$start_date_time = CalendarUtils::get_first_day_of_week($start_date_time);
			$end_date_time = CalendarUtils::get_first_day_of_week($end_date_time)->get("+7 days");
		}else{
			$end_date_time = $this->date_time->get("+1 day");
		}		

		$acts_arr = array();
	    	if($type == 'vfb'){
				$acts_arr = CalendarActivity::get_freebusy_activities($user, $start_date_time, $end_date_time);
	    	}else{
				$acts_arr = CalendarActivity::get_activities($user->id, $this->show_tasks, $start_date_time, $end_date_time, $this->view,$this->show_calls);
	    	}
	    	
	    	$this->acts_arr[$user->id] = $acts_arr;	 
	}

	/**
	 * Get date string of next or previous calendar grid
	 * @param string $direction next or previous
	 * @return string
	 */
	function get_neighbor_date_str($direction){
		if($direction == "previous")
			$sign = "-";
		else 
			$sign = "+";
			
		if($this->view == 'month'){
			$day = $this->date_time->get($sign."1 month")->get_day_begin(1);
		}else if($this->view == 'week' || $this->view == 'shared'){
			$day = CalendarUtils::get_first_day_of_week($this->date_time);
			$day = $day->get($sign."7 days");
		}else if($this->view == 'day'){
			$day = $this->date_time->get($sign."1 day")->get_day_begin();
		}else if($this->view == 'year'){
            		$day = $this->date_time->get($sign."1 year")->get_day_begin();
		}else{
			return "get_neighbor_date_str: notdefined for this view";
		}
		return $day->get_date_str();
	}

}

?>
