<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Bug #48826
 * Module Builder - Dependent multiselect fields are always displayed
 * Bug #49774
 * [IBM RTC 3020] XSS - Administration, Studio, Edit Fields, formula
 *
 * @ticket
 */

require_once ('modules/DynamicFields/FieldCases.php') ;

class Bug48826Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
	{
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
            // Bug #48826
            $provider_array[] = array($type, array('name' => 'equal($dd1_c,&quot;Analyst&quot;)'), 'equal($dd1_c,&quot;Analyst&quot;)');
            $provider_array[] = array($type, array('dependency' => 'equal($dd1_c,&quot;Analyst&quot;)'), 'equal($dd1_c,"Analyst")');
            $provider_array[] = array($type, array('dependency' => 'equal($dd1_c,"Analyst")'), 'equal($dd1_c,"Analyst")');
            $provider_array[] = array($type, array('formula' => 'equal($dd1_c,&quot;Analyst&quot;)'), 'equal($dd1_c,"Analyst")');
            $provider_array[] = array($type, array('formula' => 'equal($dd1_c,"Analyst")'), 'equal($dd1_c,"Analyst")');
            // Bug #49775
            $provider_array[] = array($type, array('formula' => 'concat(&quot;<script>alert(1623651453416)</script>&quot;, &quot;<script>alert(1623651453416)</script>&quot;)'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
            $provider_array[] = array($type, array('formula' => 'concat("<script>alert(1623651453416)</script>", "<script>alert(1623651453416)</script>")'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
            $provider_array[] = array($type, array('formula' => 'concat(&quot;&lt;script&gt;alert(1623651453416)&lt;/script&gt;&quot;, &quot;&lt;script&gt;alert(1623651453416)&lt;/script&gt;&quot;)'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
            $provider_array[] = array($type, array('dependency' => 'concat(&quot;<script>alert(1623651453416)</script>&quot;, &quot;<script>alert(1623651453416)</script>&quot;)'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
            $provider_array[] = array($type, array('dependency' => 'concat("<script>alert(1623651453416)</script>", "<script>alert(1623651453416)</script>")'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
            $provider_array[] = array($type, array('dependency' => 'concat(&quot;&lt;script&gt;alert(1623651453416)&lt;/script&gt;&quot;, &quot;&lt;script&gt;alert(1623651453416)&lt;/script&gt;&quot;)'), 'concat("alert(1623651453416)", "alert(1623651453416)")');
        }
        
        return $provider_array;
    }
    
    /**
     * @group 48826, 49774
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
