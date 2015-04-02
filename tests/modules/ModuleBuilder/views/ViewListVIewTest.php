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
 
require_once("modules/ModuleBuilder/views/view.listview.php");

class ViewListViewTest extends Sugar_PHPUnit_Framework_TestCase
{
	
	public function setUp() 
    {
	    global $app_list_strings;
        include("include/language/en_us.lang.php");
    }
    
    public function tearDown() 
    {
        $_REQUEST = array();
    }

    /**
     * Simple test that the list view class will not throw errors when used.
     * (Bug 42036)
     */
    public function testConstructor()
    {
    	$_REQUEST = array(
            "to_pdf" => "1",
            "sugar_body_only"=>"1",
            "module"=>"ModuleBuilder",
            "view_package"=>"",
            "view_module"=>"Bugs",
            "view"=>"listview",
        );
        $view = new ViewListView();
        $ajax = $view->constructAjax();
        $this->assertNotNull($ajax);
    }
    
}