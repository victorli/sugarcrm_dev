<?php

require_once('include/generic/SugarWidgets/SugarWidgetSubPanelTopButtonQuickCreate.php');
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
    
	$this->account = SugarTestAccountUtilities::createAccount();
}	

public function tearDown()
{
	SugarTestAccountUtilities::removeAllCreatedAccounts();
}
	
public function testSugarWidgetSubpanelTopButtonQuickCreate()
{
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
