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


require_once 'include/SearchForm/SearchForm2.php';

class Bug48846Test extends Sugar_PHPUnit_Framework_TestCase {

    var $module = 'Cases';
    var $action = 'wirelesslist';
    var $seed;
    var $form;
    var $array;

    public function setUp() {
        require('include/modules.php');
	    $GLOBALS['beanList'] = $beanList;
	    $GLOBALS['beanFiles'] = $beanFiles;

        require "modules/".$this->module."/metadata/searchdefs.php";
        require "modules/".$this->module."/metadata/SearchFields.php";
        require "modules/".$this->module."/metadata/listviewdefs.php";

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);

        $this->seed = new $beanList[$this->module];
        $this->form = new SearchForm($this->seed, $this->module, $this->action);
        $this->form->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl', "advanced_search", $listViewDefs);

        $this->array = array(
            'module'=>$this->module,
            'action'=>$this->action,
            'searchFormTab'=>'advanced_search',
            'query'=>'true',
        );
    }

    public function tearDown() {
        unset($this->array);
        unset($this->form);
        unset($this->seed);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }


    /**
     * testSearchInt
     *
     * tests where generation in search form
     *
     * @dataProvider searchIntProvider
     */
    public function testSearchInt($exp, $val) {
        $this->array['case_number_advanced'] = $val;

        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);

        $this->assertSame($exp, $query[0]);
    }

    /**
     * searchIntProvider
     *
     * @return Array values for testing
     */
    public function searchIntProvider() {
        return array(
            array("cases.case_number in (123)", 123),
            array("cases.case_number in (-1)", 'test'),
            array("cases.case_number in (12,14,16)", '12,14,16'),
            array("cases.case_number in (12,-1,16)", '12,junk,16'),
            array("cases.case_number in (-1,12,-1,16,34,124,-1)", 'stuff,12,junk,16,34,124,morejunk'),
        );
    }

}
 
?>
 