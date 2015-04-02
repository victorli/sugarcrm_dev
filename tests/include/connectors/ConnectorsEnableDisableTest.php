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

require_once 'include/connectors/ConnectorsTestCase.php';

class ConnectorsEnableDisableTest extends Sugar_Connectors_TestCase
{
    public function setUp()
    {
        global $current_user;
        global $app_list_strings;
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        $user = new User();
        $current_user = $user->retrieve('1');
    }

    public function tearDown()
    {

    }

    public function testEnableAll()
    {
        require_once 'modules/Connectors/controller.php';
        require_once 'include/MVC/Controller/SugarController.php';

        $_REQUEST['display_values'] = "ext_rest_twitter:Accounts,ext_rest_twitter:Leads";
        $_REQUEST['display_sources'] = 'ext_rest_twitter';
        $_REQUEST['action'] = 'SaveModifyDisplay';
        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;

        $controller = new ConnectorsController();
        $controller->action_SaveModifyDisplay();

        require(CONNECTOR_DISPLAY_CONFIG_FILE);

        foreach ($modules_sources as $module => $entries) {
            if ($module == 'Accounts' || $module == 'Contacts') {
                $this->assertTrue(in_array('ext_rest_twitter', $entries));
            }
        }
    }

    public function testDisableAll()
    {
        require_once 'modules/Connectors/controller.php';
        require_once 'include/MVC/Controller/SugarController.php';
        $controller = new ConnectorsController();

        $_REQUEST['display_values'] = '';
        $_REQUEST['display_sources'] = 'ext_rest_twitter';
        $_REQUEST['action'] = 'SaveModifyDisplay';
        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;

        $controller->action_SaveModifyDisplay();

        require(CONNECTOR_DISPLAY_CONFIG_FILE);
        $this->assertTrue(empty($modules_sources['ext_rest_twitter']));
    }

    public function testDisableEnableEAPM()
    {
        require_once 'modules/Connectors/controller.php';
        require_once 'include/MVC/Controller/SugarController.php';
        $controller = new ConnectorsController();

        $_REQUEST['display_values'] = '';
        $_REQUEST['display_sources'] = 'ext_rest_twitter,ext_eapm_webex';
        $_REQUEST['ext_rest_twitter'] = 1;
        $_REQUEST['action'] = 'SaveModifyDisplay';
        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;
        $controller->action_SaveModifyDisplay();
        ConnectorUtils::getConnectors(true);
        $this->assertFalse(ConnectorUtils::eapmEnabled('ext_rest_twitter'), "Failed to disable Twitter");
        $this->assertFalse(ConnectorUtils::eapmEnabled('ext_eapm_webex'), "Failed to disable WebEx");

        // now reenable them
        $_REQUEST['display_values'] = '';
        $_REQUEST['display_sources'] = 'ext_rest_twitter,ext_eapm_webex';
        $_REQUEST['ext_rest_twitter_external'] = 1;
        $_REQUEST['ext_eapm_webex_external'] = 1;
        $_REQUEST['action'] = 'SaveModifyDisplay';
        $_REQUEST['module'] = 'Connectors';
        $_REQUEST['from_unit_test'] = true;

        $controller->action_SaveModifyDisplay();
        ConnectorUtils::getConnectors(true);
        $this->assertTrue(ConnectorUtils::eapmEnabled('ext_rest_twitter'), "Failed to enable Twitter");
        $this->assertTrue(ConnectorUtils::eapmEnabled('ext_eapm_webex'), "Failed to enable WebEx");
    }
}
