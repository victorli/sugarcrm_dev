<?php
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


require_once "include/export_utils.php";
require_once "SugarTestProspectUtilities.php";

class Bug43805Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Contains created prospect lists' ids
     * @var Array
     */
    protected static $_createdProspectListsIds = array();

    /**
     * Instance of ProspectList
     * @var ProspectList
     */
    protected $_prospectList;

    /**
     * prospects array
     * @var Array
     */
    protected $_prospects = array();

    /**
     * Create prospect instance (with account)
     */
    public static function createProspect()
    {

		$prospect = SugarTestProspectUtilities::createProspect();
		
        $prospect->save();
        return $prospect;
       
    }

    /**
     * Create ProspectList instance
     * @param prospect instance to attach to prospect list
     */
    public static function createProspectList($prospect = null)
    {
        $prospectList = new ProspectList();
        $prospectList->name = "TargetList_code";
        $prospectList->save();
        self::$_createdProspectListsIds[] = $prospectList->id;

        if ($prospect instanceof Prospect) {
            self::attachProspectToProspectList($prospectList, $prospect);
        }

        return $prospectList;
    }

    /**
     *
     * Attach Prospect to prospect list
     * @param ProspectList $prospectList prospect list instance
     * @param prospect $prospect prospect instance
     */
    public static function attachProspectToProspectList($prospectList, $prospect)
    {
        $prospectList->load_relationship('prospects');
        $prospectList->prospects->add($prospect->id,array());
    }

    /**
     * Set up - create prospect list with 1 prospect
     */
    public function setUp()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();;

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $this->_prospects[] = self::createProspect();
        $this->_prospectList = self::createProspectList($this->_prospects[0]);
        self::attachProspectToProspectList($this->_prospectList, $this->_prospects[0]);
    }

    /**
     * Clear all created data
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->_clearProspects();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
    }

    /**
     * Test if Title exists within report
     */
    public function testTitleExistsExportList()
    {
        $content = export("ProspectLists", $this->_prospectList->id, true);
				
        $this->assertContains($this->_prospects[0]->title, $content, "Report should contain title of created Prospect");

    }

    private function _clearProspects()
    {
        $ids = implode("', '", self::$_createdProspectListsIds);
        $GLOBALS['db']->query('DELETE FROM prospect_list_campaigns WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists_prospects WHERE prospect_list_id IN (\'' . $ids . '\')');
        $GLOBALS['db']->query('DELETE FROM prospect_lists WHERE id IN (\'' . $ids . '\')');
    }
}