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




class CalendarDisplay {	

	// colors of items on calendar
	var $activity_colors = array(
		'Meetings' => array(
			'border' => '#1C5FBD',
			'body' => '#D2E5FC',
		),
		'Calls' => array(
			'border' => '#DE4040',
			'body' => '#FCDCDC',
		),
		'Tasks' => array(
			'border' => '#015900',
			'body' => '#B1F5AE',
		),
	);

	function __construct(&$args){
		$this->args = &$args;
	}	
	
	/**
	 * main display function of Calendar
	 */
	function display(){
	
		global $timedate;
	
		$args = &$this->args;	
		$ss = new Sugar_Smarty();
	
		$ss->assign('APP',$GLOBALS['app_strings']);
		$ss->assign('APPLIST',$GLOBALS['app_list_strings']);
		$ss->assign('MOD',$GLOBALS['cal_strings']);
	
		$ss->assign('pview',$args['cal']->view);
		$ss->assign('t_step',$args['cal']->time_step);
		$ss->assign('current_user_id',$GLOBALS['current_user']->id);
		$ss->assign('current_user_name',$GLOBALS['current_user']->name);
		$ss->assign('time_format',$GLOBALS['timedate']->get_user_time_format());
		$ss->assign('items_draggable',SugarConfig::getInstance()->get('calendar.items_draggable',true));
		$ss->assign('item_text',SugarConfig::getInstance()->get('calendar.item_text','name'));
		$ss->assign('mouseover_expand',SugarConfig::getInstance()->get('calendar.mouseover_expand',true));
		$ss->assign('cells_per_day',$args['cal']->cells_per_day);
		$ss->assign('celcount',$args['cal']->celcount);
		$ss->assign('img_edit_inline',SugarThemeRegistry::current()->getImageURL('edit_inline.gif',false));
		$ss->assign('img_view_inline',SugarThemeRegistry::current()->getImageURL('view_inline.gif',false));
		$ss->assign('img_close',SugarThemeRegistry::current()->getImageURL('close.gif',false));		
		$ss->assign('dashlet',$args['cal']->dashlet);
			
		
		if(count($args['cal']->shared_ids)){
			$ss->assign('shared_ids',$args['cal']->shared_ids);
			$ss->assign('shared_users_count',count($args['cal']->shared_ids));
		}				
		$ss->assign('activity_colors',$this->activity_colors);	
		$d_param = 0;
		if($args['cal']->time_step == 60){
			$d_param = 0;
		}else{			
			$d_param = strval(intval(60 / $args['cal']->time_step)) . "n";
			
			if($args['cal']->view != "week" && $args['cal']->view != "day")
				$d_param .= "+1";	
		}
		
		$scroll_hour = 5;
		if($args['cal']->time_step < 30)
			$scroll_hour = 8;
		$ss->assign('scroll_slot',intval(60 / $args['cal']->time_step) * $scroll_hour);	
		
		$ss->assign('d_param',$d_param);	
		$ss->assign('editview_width',SugarConfig::getInstance()->get('calendar.editview_width',800));
		$ss->assign('editview_height',SugarConfig::getInstance()->get('calendar.editview_height',600));	
		$ss->assign('a_str',$args['cal']->get_activities_js());

		$ss->assign('sugar_body_only',(isset($_REQUEST['to_pdf']) && $_REQUEST['to_pdf'] || isset($_REQUEST['sugar_body_only']) && $_REQUEST['sugar_body_only']));
		require_once('include/json_config.php');
		global $json;
		$json = getJSONobj();
		$json_config = new json_config();
		$ss->assign('GRjavascript',$json_config->get_static_json_server(false, true, 'Meetings'));
		$ss->assign('hide_whole_day',$args['cal']->celcount == $args['cal']->cells_per_day);
	
		// details
		$user_default_date_start  = $timedate->asUser($timedate->getNow());
		$ss->assign('user_default_date_start',$user_default_date_start);
		// end details	
		
		if($_REQUEST['module'] == "Calendar"){
			$this->load_settings_template($ss);
			$settings = "custom/modules/Calendar/tpls/settings.tpl";
			if(!file_exists($settings))
				$settings = "modules/Calendar/tpls/settings.tpl";
			$ss->assign("settings",$settings);			
		}
	
		$main = "custom/modules/Calendar/tpls/main.tpl";
		if(!file_exists($main))
			$main = "modules/Calendar/tpls/main.tpl";
		$details = "custom/modules/Calendar/tpls/details.tpl";
		if(!file_exists($details))
			$details = "modules/Calendar/tpls/details.tpl";
			
	
		$ss->assign("details",$details);
			
		echo $ss->fetch($main);	
		
		// drid
		$grid = new CalendarGrid($args);
		echo $grid->display();
		// end grid	
	}
	
	/**
	 * load settings popup template
	 */	
	function load_settings_template(&$ss){	
		
		list($d_start_hour,$d_start_min) =  explode(":",$this->args['cal']->day_start_time);		
		list($d_end_hour,$d_end_min) =  explode(":",$this->args['cal']->day_end_time);	

		require_once("include/utils.php");
		global $app_strings,$app_list_strings,$beanList;
		global $timedate;
		
		$user_default_date_start  = $timedate->asUser($timedate->getNow());
		if(!isset($time_separator))
			$time_separator = ":";	
		$date_format = $timedate->get_cal_date_format();
		$time_format = $timedate->get_user_time_format();
		$TIME_FORMAT = $time_format;      
		$t23 = strpos($time_format, '23') !== false ? '%H' : '%I';
		if(!isset($match[2]) || $match[2] == '') {
			$CALENDAR_FORMAT = $date_format . ' ' . $t23 . $time_separator . "%M";
		}else{
			$pm = $match[2] == "pm" ? "%P" : "%p";
			$CALENDAR_FORMAT = $date_format . ' ' . $t23 . $time_separator . "%M" . $pm;
		}
		$hours_arr = array ();
		$num_of_hours = 24;
		$start_at = 0;
		$TIME_MERIDIEM = "";
		$time_pref = $timedate->get_time_format();
		$start_m = "";		
		if(strpos($time_pref, 'a') || strpos($time_pref, 'A')){
			$num_of_hours = 12;
			$start_at = 1;			
			$start_m = 'am';
			if($d_start_hour == 0){
				$d_start_hour = 12;
				$start_m = 'am';
			}else
				if($d_start_hour == 12)
			   		$start_m = 'pm';
			if($d_start_hour > 12){		   		
				$d_start_hour = $d_start_hour - 12;
			   	$start_m = 'pm';
			}			   	
			$end_m = 'am';
			if($d_end_hour == 0){
				$d_end_hour = 12;
				$end_m = 'am';
			}else
				if($d_end_hour == 12)
			   		$end_m = 'pm';		   		
			   		
			if($d_end_hour > 12){		   		
				$d_end_hour = $d_end_hour - 12;
				$end_m = 'pm';
			}			   	
			if(strpos($time_pref, 'A')){
				$start_m = strtoupper($start_m);
				$end_m = strtoupper($end_m);
			}
			$options = strpos($time_pref, 'a') ? $app_list_strings['dom_meridiem_lowercase'] : $app_list_strings['dom_meridiem_uppercase'];
			$TIME_MERIDIEM1 = get_select_options_with_id($options, $start_m);  
			$TIME_MERIDIEM2 = get_select_options_with_id($options, $end_m);  
			$TIME_MERIDIEM1 = "<select id='d_start_meridiem' name='d_start_meridiem' tabindex='2'>".$TIME_MERIDIEM1."</select>";
			$TIME_MERIDIEM2 = "<select id='d_end_meridiem' name='d_end_meridiem' tabindex='2'>".$TIME_MERIDIEM2."</select>";			
		}else{
			$TIME_MERIDIEM1 = $TIME_MERIDIEM2 = "";
		}	
		for($i = $start_at; $i <= $num_of_hours; $i ++){
			$i = $i."";
			if (strlen($i) == 1)
				$i = "0".$i;			
			$hours_arr[$i] = $i;
		}
		$TIME_START_HOUR_OPTIONS1 = get_select_options_with_id($hours_arr, $d_start_hour);
		$TIME_START_MINUTES_OPTIONS1 = get_select_options_with_id(array('0'=>'00','15'=>'15','30'=>'30','45'=>'45'), $d_start_min);
		$TIME_START_HOUR_OPTIONS2 = get_select_options_with_id($hours_arr, $d_end_hour);
		$TIME_START_MINUTES_OPTIONS2 = get_select_options_with_id(array('0'=>'00','15'=>'15','30'=>'30','45'=>'45'), $d_end_min);
		
		$ss->assign('day',$_REQUEST['day']);
		$ss->assign('week',$_REQUEST['week']);
		$ss->assign('month',$_REQUEST['month']);
		$ss->assign('year',$_REQUEST['year']);
		$ss->assign('TIME_START_HOUR_OPTIONS1',$TIME_START_HOUR_OPTIONS1);		
		$ss->assign('TIME_START_MINUTES_OPTIONS1',$TIME_START_MINUTES_OPTIONS1);
		$ss->assign('TIME_MERIDIEM1',$TIME_MERIDIEM1);
		$ss->assign('TIME_START_HOUR_OPTIONS2',$TIME_START_HOUR_OPTIONS2);		
		$ss->assign('TIME_START_MINUTES_OPTIONS2',$TIME_START_MINUTES_OPTIONS2);
		$ss->assign('TIME_MERIDIEM2',$TIME_MERIDIEM2);
		$ss->assign('show_calls',$this->args['cal']->show_calls);
		$ss->assign('show_tasks',$this->args['cal']->show_tasks);
	}
	
	// returns date info string (legacy of old calendar)
	function get_date_info($view, $date_time){
		$str = "";

		global $current_user;
		$dateFormat = $current_user->getUserDateTimePreferences();

		if($view == 'month'){
			for($i=0; $i<strlen($dateFormat['date']); $i++){
				switch($dateFormat['date']{$i}){
					case "Y":
						$str .= " ".$date_time->year;
						break;
					case "m":
						$str .= " ".$date_time->get_month_name();
						break;
				}
			}
		}else
			if($view == 'week' || $view == 'shared') {
				$first_day = $date_time;
				
				$first_day = CalendarUtils::get_first_day_of_week($date_time);				
				$last_day = $first_day->get("+6 days");				

				for($i=0; $i<strlen($dateFormat['date']); $i++) {
					switch($dateFormat['date']{$i}){
						case "Y":
							$str .= " ".$first_day->year;
							break;
						case "m":
							$str .= " ".$first_day->get_month_name();
							break;
						case "d":
							$str .= " ".$first_day->get_day();
							break;
					}
				}
				$str .= " - ";
				for($i=0; $i<strlen($dateFormat['date']); $i++) {
					switch($dateFormat['date']{$i}) {
						case "Y":
							$str .= " ".$last_day->year;
							break;
						case "m":
							$str .= " ".$last_day->get_month_name();
							break;
						case "d":
							$str .= " ".$last_day->get_day();
							break;
					}
				}
			}else if($view == 'day'){
					$str .= $date_time->get_day_of_week()." ";

					for($i=0; $i<strlen($dateFormat['date']); $i++){
						switch($dateFormat['date']{$i}){
							case "Y":
								$str .= " ".$date_time->year;
								break;
							case "m":
								$str .= " ".$date_time->get_month_name();
								break;
							case "d":
								$str .= " ".$date_time->get_day();
								break;
						}
					}
			}else if($view == 'year') {
				$str .= $date_time->year;
			}else{
				sugar_die("echo_date_info: date not supported");
			}
		return $str;
	}
	
	// Get link to next date range
	function get_next_calendar(){	
		global $cal_strings,$image_path;
		$str = "";
		if($_REQUEST['module'] == "Calendar"){
			$str .= "<a href='".ajaxLink("index.php?action=index&module=Calendar&view=".$this->args['cal']->view."&".$this->args['cal']->get_neighbor_date_str("next"))."'>";

		}else{
			$str .= "<a href='#' onclick='CAL.remove_record_dialog(); return SUGAR.mySugar.retrieveDashlet(\"".$this->args['dashlet_id']."\", \"index.php?module=Home&action=DynamicAction&DynamicAction=displayDashlet&sugar_body_only=1&".$this->args['cal']->get_neighbor_date_str("next")."&id=".$this->args['dashlet_id']."\")'>";
		}
			$str .= $cal_strings["LBL_NEXT_".strtoupper($this->args['cal']->view)]; 

		$str .= "&nbsp;&nbsp;".SugarThemeRegistry::current()->getImage("calendar_next", 'align="absmiddle" border="0"' ,null,null,'.gif', '') . "</a>"; //setting alt tag blank on purpose for 508 compliance
		return $str;
	}
	
	// Get link to previous date range
	function get_previous_calendar(){
		global $cal_strings,$image_path;
		$str = "";
		if($_REQUEST['module'] == "Calendar"){
			$str .= "<a href='".ajaxLink("index.php?action=index&module=Calendar&view=".$this->args['cal']->view."&".$this->args['cal']->get_neighbor_date_str("previous")."")."'>";
		}else{
			$str .= "<a href='#' onclick='CAL.remove_record_dialog(); return SUGAR.mySugar.retrieveDashlet(\"".$this->args['dashlet_id']."\", \"index.php?module=Home&action=DynamicAction&DynamicAction=displayDashlet&sugar_body_only=1&".$this->args['cal']->get_neighbor_date_str("previous")."&id=".$this->args['dashlet_id']."\")'>";
		}
		$str .= SugarThemeRegistry::current()->getImage('calendar_previous','align="absmiddle" border="0"', null, null, '.gif', ''); //setting alt tag blank on purpose for 508 compliance
		$str .= "&nbsp;&nbsp;".$cal_strings["LBL_PREVIOUS_".strtoupper($this->args['cal']->view)] . "</a>";
		return $str;
	}

	/**
	 * displays header
	 * @params boolean $controls display ui contol itmes 
	 */
	function display_calendar_header($controls = true){
		global $cal_strings;
		
		$ss = new Sugar_Smarty();
		$ss->assign("MOD",$cal_strings);
		$ss->assign("pview",$this->args['cal']->view);
		
		if($controls){
			$current_date = str_pad($this->args['cal']->date_time->month,2,'0',STR_PAD_LEFT)."/".str_pad($this->args['cal']->date_time->day,2,'0',STR_PAD_LEFT)."/".$this->args['cal']->date_time->year;
							
			$tabs = array('day', 'week', 'month', 'year', 'shared');
			$tabs_params = array();		
			foreach($tabs as $tab){ 
				$tabs_params[$tab]['title'] = $cal_strings["LBL_".strtoupper($tab)];
				$tabs_params[$tab]['link'] = "window.location.href='".ajaxLink("index.php?module=Calendar&action=index&view=". $tab . $this->args['cal']->date_time->get_date_str())."'";
			}		
			$ss->assign('controls',$controls);
			$ss->assign('tabs',$tabs);
			$ss->assign('tabs_params',$tabs_params);
			$ss->assign('current_date',$current_date);
			$ss->assign('start_weekday',$GLOBALS['current_user']->get_first_day_of_week());
			$ss->assign('cal_img',SugarThemeRegistry::current()->getImageURL("jscalendar.gif",false));		
		}
	
		$ss->assign('previous',$this->get_previous_calendar());
		$ss->assign('next',$this->get_next_calendar());
		
		$ss->assign('date_info',$this->get_date_info($this->args['view'],$this->args['cal']->date_time));
		
		$header = "custom/modules/Calendar/tpls/header.tpl";
		if(!file_exists($header))
			$header = "modules/Calendar/tpls/header.tpl";	
		echo $ss->fetch($header);		
	
	}

	/**
	 * displays footer
	 */
	function display_calendar_footer(){
		global $cal_strings;
		
		$ss = new Sugar_Smarty();
		$ss->assign("MOD",$cal_strings);
		$ss->assign("pview",$this->args['cal']->view);	
		
		$ss->assign('previous',$this->get_previous_calendar());
		$ss->assign('next',$this->get_next_calendar());	
		
		$footer = "custom/modules/Calendar/tpls/footer.tpl";
		if(!file_exists($footer))
			$footer = "modules/Calendar/tpls/footer.tpl";	
		echo $ss->fetch($footer);
	}

	/**
	 * displays title
	 */
	function display_title(){
		global $mod_strings;		
		echo get_module_title("Calendar","<span class='pointer'>&raquo;</span>".$mod_strings['LBL_MODULE_TITLE'], false);
	}
	
	/**
	 * displays html used in shared view (legacy code of old calendar)
	 */
	function display_shared_html(){
			global $app_strings,$action;
			$tools = '<div align="right"><a href="index.php?module=Calendar&action='.$action.'&view=shared" class="tabFormAdvLink">&nbsp;<a href="javascript: CAL.toggleDisplay(\'shared_cal_edit\');" class="tabFormAdvLink">'.SugarThemeRegistry::current()->getImage('edit', 'border="0"  align="absmiddle"', null, null, '.gif', $GLOBALS['cal_strings']['LBL_EDIT_USERLIST']).'&nbsp;'.$GLOBALS['cal_strings']['LBL_EDIT_USERLIST'].'</a></div>';
			echo get_form_header($GLOBALS['cal_strings']['LBL_SHARED_CAL_TITLE'], $tools, false);
			if(empty($_SESSION['shared_ids']))
				$_SESSION['shared_ids'] = "";

			echo "
			<script language=\"javascript\">
			function up(name){
				var td = document.getElementById(name+'_td');
				var obj = td.getElementsByTagName('select')[0];
				obj =(typeof obj == \"string\") ? document.getElementById(obj) : obj;
				if(obj.tagName.toLowerCase() != \"select\" && obj.length < 2)
					return false;
				var sel = new Array();
							
				for(i=0; i<obj.length; i++) {
					if(obj[i].selected == true) {
						sel[sel.length] = i;
					}
				}
				for(i in sel) {
					if(sel[i] != 0 && !obj[sel[i]-1].selected) {
						var tmp = new Array(obj[sel[i]-1].text, obj[sel[i]-1].value);
						obj[sel[i]-1].text = obj[sel[i]].text;
						obj[sel[i]-1].value = obj[sel[i]].value;
						obj[sel[i]].text = tmp[0];
						obj[sel[i]].value = tmp[1];
						obj[sel[i]-1].selected = true;
						obj[sel[i]].selected = false;
					}
				}
			}			
			function down(name){
				var td = document.getElementById(name+'_td');
				var obj = td.getElementsByTagName('select')[0];
				if(obj.tagName.toLowerCase() != \"select\" && obj.length < 2)
					return false;
				var sel = new Array();
				for(i=obj.length-1; i>-1; i--){
					if(obj[i].selected == true) {
						sel[sel.length] = i;
					}
				}
				for(i in sel){
					if(sel[i] != obj.length-1 && !obj[sel[i]+1].selected) {
						var tmp = new Array(obj[sel[i]+1].text, obj[sel[i]+1].value);
						obj[sel[i]+1].text = obj[sel[i]].text;
						obj[sel[i]+1].value = obj[sel[i]].value;
						obj[sel[i]].text = tmp[0];
						obj[sel[i]].value = tmp[1];
						obj[sel[i]+1].selected = true;
						obj[sel[i]].selected = false;
					}
				}
			}
			</script>
			
			<div id='shared_cal_edit' style='display: none;'>
			<form name='shared_cal' action=\"index.php\" method=\"post\" >
			<input type=\"hidden\" name=\"module\" value=\"Calendar\">
			<input type=\"hidden\" name=\"action\" value=\"".$action."\">
			<input type=\"hidden\" name=\"view\" value=\"shared\">
			<input type=\"hidden\" name=\"edit\" value=\"0\">
			<table cellpadding=\"0\" cellspacing=\"3\" border=\"0\" align=\"center\">
			<tr><th valign=\"top\"  align=\"center\" colspan=\"2\">
			";
			echo $GLOBALS['cal_strings']['LBL_SELECT_USERS'];
			echo "
			</th>
			</tr>
			<tr><td valign=\"top\"></td><td valign=\"top\">

			<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" class=\"edit view\" align=\"center\">
			<tr>
				<td valign='top' nowrap><b>".$GLOBALS['cal_strings']['LBL_USERS']."</b></td>
				<td valign='top' id=\"shared_ids_td\"><select id=\"shared_ids\" name=\"shared_ids[]\" multiple size='8'>";
				echo get_select_options_with_id(get_user_array(false), $this->args['cal']->shared_ids);
			echo "	</select></td>
				<td><a onclick=\"up('shared_ids');\">".SugarThemeRegistry::current()->getImage('uparrow_big', 'border="0" style="margin-bottom: 1px;"', null, null, '.gif', $app_strings['LBL_SORT'])."</a><br>
				<a onclick=\"down('shared_ids');\">".SugarThemeRegistry::current()->getImage('downarrow_big', 'border="0" style="margin-top: 1px;"', null, null, '.gif', $app_strings['LBL_SORT'])."</a></td>
			</tr>
			<tr>";
			echo "<td align=\"right\" colspan=\"2\"><input class=\"button\" type=\"submit\" title=\"".$app_strings['LBL_SELECT_BUTTON_TITLE']."\" accessKey=\"".$app_strings['LBL_SELECT_BUTTON_KEY']."\" value=\"".$app_strings['LBL_SELECT_BUTTON_LABEL']."\" /> <input class=\"button\" onClick=\"javascript: CAL.toggleDisplay('shared_cal_edit');\" type=\"button\" title=\"".$app_strings['LBL_CANCEL_BUTTON_TITLE']."\" accessKey=\"".$app_strings['LBL_CANCEL_BUTTON_KEY']."\" value=\"".$app_strings['LBL_CANCEL_BUTTON_LABEL']."\"/></td>
			</tr>
			</table>
			</td></tr>
			</table>
			</form>			
			</div>
			";			
	}	
	
}

?>
