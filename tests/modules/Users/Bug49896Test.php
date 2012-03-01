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

require_once 'modules/Users/User.php';

class Bug49896Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $_passwordSetting;
    var $_currentUser;

    public function setUp()
    {
        if(isset($GLOBALS['sugar_config']['passwordsetting']))
        {
            $this->_passwordSetting = $GLOBALS['sugar_config']['passwordsetting'];
        }
        $GLOBALS['sugar_config']['passwordsetting'] = array('onenumber'=>1,
                'onelower'=>1,
                'oneupper'=>1,
                'onespecial'=>1,
                'minpwdlength'=>6,
                'maxpwdlength'=>15);
        $this->_currentUser = SugarTestUserUtilities::createAnonymousUser(false);        
    }

    public function tearDown()
    {
        if(!empty($this->_passwordSetting))
        {
            $GLOBALS['sugar_config']['passwordsetting'] = $this->_passwordSetting;
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testMinLength()
    {
        $result = $this->_currentUser->check_password_rules('Tes1!');
        $this->assertEquals(false, $result, 'Assert that min length rule is checked');
    }

    public function testMaxLength()
    {
        $result = $this->_currentUser->check_password_rules('Tester123456789!');
        $this->assertEquals(false, $result, 'Assert that max length rule is checked');
    }
        
    public function testOneNumber()
    {
        $result = $this->_currentUser->check_password_rules('Tester!');
        $this->assertEquals(false, $result, 'Assert that one number rule is checked');
    }

    public function testOneLower()
    {
        $result = $this->_currentUser->check_password_rules('TESTER1!');
        $this->assertEquals(false, $result, 'Assert that one lower rule is checked');
    }
    
    public function testOneUpper()
    {
        $result = $this->_currentUser->check_password_rules('tester1!');
        $this->assertEquals(false, $result, 'Assert that one upper rule is checked');
    } 
    
    public function testOneSpecial()
    {
        $result = $this->_currentUser->check_password_rules('Tester1');
        $this->assertEquals(false, $result, 'Assert that one special rule is checked');
    }  
    
    public function testCustomRegex()
    {
        $GLOBALS['sugar_config']['passwordsetting']['customregex'] = '/^T/';
        $result = $this->_currentUser->check_password_rules('tester1!');
        $this->assertEquals(false, $result, 'Assert that custom regex is checked');
    } 

    public function testAllCombinations()
    {
        $result = $this->_currentUser->check_password_rules('Tester1!');
        $this->assertEquals(true, $result, 'Assert that all rules are checked and passed');
    }    
}
?>