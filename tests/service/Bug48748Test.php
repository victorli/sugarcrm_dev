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


require_once('tests/service/RestTestCase.php');

class Bug48748Test extends RestTestCase
{

    protected $package = 'Accounts';
    protected $packageExists = false;
    protected $aclRole;
    protected $aclField;


    public function setUp()
    {
        parent::setUp();

        //If somehow this package already exists copy it
        if(file_exists('custom/modules/' . $this->package))
        {
           $this->packageExists = true;
           mkdir_recursive('custom/modules/' . $this->package . '_bak');
           copy_recursive('custom/modules/' . $this->package, 'custom/modules/' . $this->package . '_bak');
        }

        //Make the custom package directory and simulate copying the file in
        mkdir_recursive('custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs');

        $theArray = array ($this->package => array('subpanel_setup' => array ( $this->package.'_accounts' => array(
          'order' => 100,
          'module' => 'Contacts',
          'subpanel_name' => 'default',
          'title_key' => 'LBL_BUG48784TEST',
          'get_subpanel_data' => 'Bug48748Test',
        ))));
        $theFile = 'custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs/wireless.subpaneldefs.ext.php';
        write_array_to_file('layout_defs', $theArray, $theFile);

        sugar_chmod('custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs/wireless.subpaneldefs.ext.php', 0655);

        global $beanList, $beanFiles, $current_user;
        //$beanList['Contacts'] = 'Contact';
        //$beanFiles['Bug48784Mock'] = 'modules/Contacts/Contact.php';

        //Create an anonymous user for login purposes/
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->status = 'Active';
        $current_user->is_admin = 1;
        $current_user->save();
        $GLOBALS['db']->commit(); // Making sure we commit any changes before continuing

        $_SESSION['avail_modules'][$this->package] = 'write';
    }

    public function tearDown()
    {
        parent::tearDown();
        if($this->packageExists)
        {
            //Copy original contents back in
            copy_recursive('custom/modules/' . $this->package . '_bak', 'custom/modules/' . $this->package);
            rmdir_recursive('custom/modules/' . $this->package . '_bak');
        } else {
            rmdir_recursive('custom/modules/' . $this->package);
        }

        unset($_SESSION['avail_modules'][$this->package]);
    }

    public function testWirelessModuleLayoutForCustomModule()
    {

        $this->assertTrue(file_exists('custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs/wireless.subpaneldefs.ext.php'));
        //$contents = file_get_contents('custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs/wireless.subpaneldefs.ext.php');
        include('custom/modules/' . $this->package . '/Ext/WirelessLayoutdefs/wireless.subpaneldefs.ext.php');

        global $current_user;
        $result = $this->_login($current_user);
        $session = $result['id'];
        $results = $this->_makeRESTCall('get_module_layout',
        array(
            'session' => $session,
            'module' => array($this->package),
            'type' => array('wireless'),
            'view' => array('subpanel'),
            )
        );

        $this->assertEquals('Bug48748Test', $results[$this->package]['wireless']['subpanel']["{$this->package}_accounts"]['get_subpanel_data'], 'Cannot load custom wireless.subpaneldefs.ext.php file');
    }
}
