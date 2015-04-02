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

require_once "include/generic/LayoutManager.php";
require_once "include/generic/SugarWidgets/SugarWidgetReportField.php";
require_once "include/generic/SugarWidgets/SugarWidgetFieldvarchar.php";
require_once "include/generic/SugarWidgets/SugarWidgetFieldurl.php";

class Bug36246Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testIfWidgetFieldUrlReturnsALink()
	{
        $fieldurl = new SugarWidgetFieldURLBug36246Mock(new LayoutManager());
        $link = $fieldurl->displayList(array());
        $this->assertRegExp("|<a([^>]*)href=\"sugarcrm.com\"([^>]*)>sugarcrm.com<\/a>|", $link, 'SugarWidgetFieldurl should return a link');
	}
}

class SugarWidgetFieldURLBug36246Mock extends SugarWidgetFieldURL {
    
    function _get_list_value($layoutDef) 
    {
        return 'sugarcrm.com';
    }
}
