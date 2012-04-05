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

require_once ('modules/DynamicFields/FieldCases.php') ;

class Bug48826Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
	{
        $this->markTestSkipped('Skipping a broken unit test, dev will work on fixing this.');
	}
	
	public function tearDown()
	{
	}
    
    public function provider()
    {
        $types = array(
            'char','varchar','varchar2','text','textarea','double','float','decimal','int','date','bool','relate',
            'enum','multienum','radioenum','email','url','iframe','html','phone','currency','parent','parent_type',
            'currency_id','address','encrypt','id','datetimecombo','datetime','image','_other_'
        );
        $provider_array = array();
        foreach ( $types as $type )
        {
            $provider_array[] = array($type, array('name' => 'equal($dd1_c,&quot;Analyst&quot;)'), 'equal($dd1_c,&quot;Analyst&quot;)');
            $provider_array[] = array($type, array('dependency' => 'equal($dd1_c,&quot;Analyst&quot;)'), 'equal($dd1_c,"Analyst")');
            $provider_array[] = array($type, array('dependency' => 'equal($dd1_c,"Analyst")'), 'equal($dd1_c,"Analyst")');
            $provider_array[] = array($type, array('formula' => 'equal($dd1_c,&quot;Analyst&quot;)'), 'equal($dd1_c,"Analyst")');
            $provider_array[] = array($type, array('formula' => 'equal($dd1_c,"Analyst")'), 'equal($dd1_c,"Analyst")');
        }
        
        return $provider_array;
    }
    
    /**
     * @dataProvider provider
     */
    public function testPopulateFromPost($type, $request_data, $expected)
    {
        $tested_key = null;
        foreach ( $request_data as $_key => $_data )
        {
            $_REQUEST[$_key] = $_data;
            $tested_key = $_key;
        }
        
        $field = get_widget($type) ;
        $field->populateFromPost();

        if ( isset($field->$tested_key) )
        {
            $this->assertEquals($expected, $field->$tested_key);
        } 
        else 
        {
            $this->markTestSkipped();
        }
    }
}
?>
