<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2012 SugarCRM Inc.
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


 
require_once("modules/ModuleBuilder/Module/StudioModule.php");


/**
 * Bug #46196
 * Deleted field is not removed from subpanel for custom relationship
 *
 * @author dkroman@sugarcrm.com
 * @ticket 46196
 */
class Bug46196Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_backup = array();

	public function setUp()
    {
        $this->markTestIncomplete('Test works fine but not in queue on Jenkins');
        return;

        $this->_backup = array(
            '_REQUEST' => $_REQUEST,
            'sugarCache' => sugarCache::$isCacheReset
        );

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }
    
    public function tearDown() 
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        rmdir_recursive('custom/modules/Accounts/metadata');
        rmdir_recursive('custom/modules/Accounts/Ext');
        rmdir_recursive('custom/modules/Accounts/language');

        $_REQUEST = $this->_backup['_REQUEST'];
        sugarCache::$isCacheReset = $this->_backup['sugarCache'];
        unset($GLOBALS['reload_vardefs']);
    }
    
    
    /**
     * Test tries to assert that field is not exist after removal it from subpanel
     * 
     * @group 46196
     */
    public function testRemoveCustomFieldFromSubpanelForCustomRelation()
    {
        
        $controller = new ModuleBuilderController;
        
        $module_name = 'Accounts';
        $_REQUEST['view_module'] = $module_name;
        
        $test_field_name = 'testfield_222222';
        $_REQUEST['name'] = $test_field_name;
        $_REQUEST['labelValue'] = 'testfield 222222';
        $_REQUEST['label'] = 'LBL_TESTFIELD_222222';
        $_REQUEST['type'] = 'varchar';

        $controller->action_SaveField();
        
        $_REQUEST['view_module'] = $module_name;
        $_REQUEST['relationship_type'] = 'many-to-many';
        $_REQUEST['lhs_module'] = $module_name;
        $_REQUEST['lhs_label'] = $module_name;
        $_REQUEST['rhs_module'] = $module_name;
        $_REQUEST['rhs_label'] = $module_name;
        $_REQUEST['lhs_subpanel'] = 'default';
        $_REQUEST['rhs_subpanel'] = 'default';
        
        $controller->action_SaveRelationship();
        
        $parser = ParserFactory::getParser('listview', $module_name, null, 'accounts_accounts');
        $field = $parser->_fielddefs[$test_field_name . '_c'];
        $parser->_viewdefs[$test_field_name . '_c'] = $field;
        $parser->handleSave(false);
        
        $_REQUEST['type'] = 'varchar';
        $_REQUEST['name'] = $test_field_name . '_c';
        $controller->action_DeleteField();

        $parser = ParserFactory::getParser('listview', $module_name, null, 'accounts_accounts');

        $_REQUEST['relationship_name'] = 'accounts_accounts';
        $controller->action_DeleteRelationship();

        $this->assertArrayNotHasKey($test_field_name . '_c', $parser->_viewdefs);
    }
}