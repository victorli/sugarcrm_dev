<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
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


require_once('include/SearchForm/SearchForm2.php');

/**
 * Bug #54929
 * Search Filtering is Broken when using Numbers as the Item Names in the Sales Stage Dropdown Menu
 *
 * @author vromanenko@sugarcrm.com
 * @ticked 54929
 */
class Bug54929Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SearchForm
     */
    protected $searchForm;

    /**
     * @var Opportunity
     */
    protected $seed;

    protected $module;

    protected $action;

    protected $normalAppListStringsOfSalesStageDom;

    protected function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        $this->seed = new Opportunity();
        $this->module = 'Opportunities';
        $this->action = 'index';

        $this->normalAppListStringsOfSalesStageDom = $GLOBALS['app_list_strings']['sales_stage_dom'];
        $GLOBALS['app_list_strings']['sales_stage_dom'] = array(
            ''      => '',
            '00'    => '0-zero',
            '10'    => '10-ten',
            '100'   => '100-hundred'
        );
    }

    protected function tearDown()
    {
        $GLOBALS['app_list_strings']['sales_stage_dom'] = $this->normalAppListStringsOfSalesStageDom;
        SugarTestHelper::tearDown();
    }

    /**
     * Test that indexes of the sales stage field options has not been changed.
     * @group bug54929
     */
    public function testIntegerIndexesOfMultiSelectFieldOptionsOnTheAdvancedSearch()
    {
        $searchMetaData = SearchForm::retrieveSearchDefs($this->module);
        $this->searchForm = new SearchForm($this->seed, $this->module, $this->action);
        $this->searchForm->setup(
            $searchMetaData['searchdefs'],
            $searchMetaData['searchFields'],
            'SearchFormGeneric.tpl',
            'advanced_search',
            array()
        );
        $result = $this->searchForm->fieldDefs['sales_stage_advanced']['options'];

        $this->assertArrayHasKey('', $result);
        $this->assertArrayHasKey('00', $result);
        $this->assertArrayHasKey(10, $result);
        $this->assertArrayHasKey(100, $result);
        $this->assertEquals(4, count($result));
    }

}
