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
 
class Bug41845Test extends Sugar_PHPUnit_Framework_TestCase {
      
    public function testEnableDecimalFieldForRangeSearch()
    {
    	require_once('modules/DynamicFields/templates/Fields/TemplateRange.php');
    	require_once('modules/DynamicFields/templates/Fields/TemplateFloat.php');
    	require_once('modules/DynamicFields/templates/Fields/TemplateDecimal.php');
    	$decimal = new TemplateDecimal();
    	$this->assertTrue(isset($decimal->vardef_map['enable_range_search']), 'Assert that enable_range_search is in the vardef_map Array'); 	
    } 
   
}
?>