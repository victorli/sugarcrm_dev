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


require_once('include/SubPanel/SubPanel.php');
require_once('include/SubPanel/SubPanelDefinitions.php');

class Bug44272Test extends PHPUnit_Framework_TestCase
{

var $account;

public function setUp()
{
    $beanList = array();
    $beanFiles = array();
    require('include/modules.php');
    $GLOBALS['beanList'] = $beanList;
    $GLOBALS['beanFiles'] = $beanFiles;
    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	$this->account = SugarTestAccountUtilities::createAccount();
}

public function tearDown()
{
	SugarTestAccountUtilities::removeAllCreatedAccounts();
}

public function testSugarWidgetSubpanelTopButtonQuickCreate()
{
    $this->markTestIncomplete("FRM - We don't have a quick create button being sent anymore.");
	$defines = array();
	$defines['focus'] = $this->account;
	$defines['module'] = 'Accounts';
	$defines['action'] = 'DetailView';

	$subpanel_definitions = new SubPanelDefinitions(new Contact());
	$contactSubpanelDef = $subpanel_definitions->load_subpanel('contacts');

	$subpanel = new SubPanel('Accounts', $this->account->id, 'contacts', $contactSubpanelDef, 'Accounts');
	$defines['subpanel_definition'] = $subpanel->subpanel_defs;

	$button = new SugarWidgetSubPanelTopButtonQuickCreate();
	$code = $button->_get_form($defines);
	$this->assertRegExp('/\<input[^\>]*?name=\"return_name\"/', $code, "Assert that the hidden input field return_name was created");
}

}
