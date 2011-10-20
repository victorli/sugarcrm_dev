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


require_once('include/MVC/View/views/view.edit.php');
require_once('modules/Users/UserViewHelper.php');


class UsersViewEdit extends ViewEdit {

 	function UsersViewEdit(){
 		parent::ViewEdit();
 	}

    function preDisplay() {
        $this->fieldHelper = new UserViewHelper($this->ss, $this->bean, 'EditView');
        $this->fieldHelper->setupAdditionalFields();

        parent::preDisplay();
    }

    public function getMetaDataFile() {
        $userType = 'Regular';
        if($this->fieldHelper->usertype == 'GROUP'){
            $userType = 'Group';
        }

        if ( $userType != 'Regular' ) {
            $oldType = $this->type;
            $this->type = $oldType.'group';
        }
        $metadataFile = parent::getMetaDataFile();
        if ( $userType != 'Regular' ) {
            $this->type = $oldType;
        }
        return $metadataFile;
    }

    function display() {
        global $current_user, $app_list_strings;

        if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
            $this->bean->id = "";
            $this->bean->user_name = "";
        }

        ///////////////////////////////////////////////////////////////////////////////
        ////	REDIRECTS FROM COMPOSE EMAIL SCREEN
        if(isset($_REQUEST['type']) && (isset($_REQUEST['return_module']) && $_REQUEST['return_module'] == 'Emails')) {
            $this->ss->assign('REDIRECT_EMAILS_TYPE', $_REQUEST['type']);
        }
        ////	END REDIRECTS FROM COMPOSE EMAIL SCREEN
        ///////////////////////////////////////////////////////////////////////////////

        ///////////////////////////////////////////////////////////////////////////////
        ////	NEW USER CREATION ONLY
        if(empty($this->bean->id)) {
            $this->ss->assign('SHOW_ADMIN_CHECKBOX','height="30"');
            $this->ss->assign('NEW_USER','1');
        }else{
            $this->ss->assign('NEW_USER','0');
            $this->ss->assign('NEW_USER_TYPE','DISABLED');
        }

        ////	END NEW USER CREATION ONLY
        ///////////////////////////////////////////////////////////////////////////////





        // FIXME: Translate error prefix
        if(isset($_REQUEST['error_string'])) $this->ss->assign('ERROR_STRING', '<span class="error">Error: '.$_REQUEST['error_string'].'</span>');
        if(isset($_REQUEST['error_password'])) $this->ss->assign('ERROR_PASSWORD', '<span id="error_pwd" class="error">Error: '.$_REQUEST['error_password'].'</span>');




        // Build viewable versions of a few fields for non-admins
        if(!empty($this->bean->id)) {
            if( !empty($this->bean->status) ) {
                $this->ss->assign('STATUS_READONLY',$app_list_strings['user_status_dom'][$this->bean->status]); }
            if( !empty($this->bean->employee_status) ) {
                $this->ss->assign('EMPLOYEE_STATUS_READONLY', $app_list_strings['employee_status_dom'][$this->bean->employee_status]);
            }
            if( !empty($this->bean->reports_to_id) ) {
                $this->ss->assign('REPORTS_TO_READONLY', get_assigned_user_name($this->bean->reports_to_id));
            }
        }

        $processSpecial = false;
        $processFormName = '';
        if ( $this->fieldHelper->usertype == 'GROUP'
            ) {
            $this->ev->formName = 'EditViewGroup';
            
            $processSpecial = true;
            $processFormName = 'EditViewGroup';            
        }

        $this->ev->process($processSpecial,$processFormName);

		echo $this->ev->display($this->showTitle);
        
    }

}