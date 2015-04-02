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

require_once "tests/upgrade/UpgradeTestCase.php";
require_once 'upgrade/scripts/post/7_MBMenu.php';

/**
 * Class CRYS174Test tests vCard menu item creation
 */
class CRYS174Test extends UpgradeTestCase
{
    private $configBackup = array();

    public function setUp()
    {
        parent::setUp();
        $this->configBackup = $this->upgrader->config;
        $this->upgrader->config['default_permissions'] = array();
    }

    public function tearDown()
    {
        $this->upgrader->config = $this->configBackup;
        parent::tearDown();
    }

    /**
     * Data provider for testVCardMenuItemCreation
     *
     * @return array with Module Name under test and Should vCard item be created or not
     */
    public function modulesProvider()
    {
        return array(
            array('Contacts', true),
            array('Accounts', false),
        );
    }

    /**
     * @dataProvider modulesProvider
     *
     * @group CRYS174
     */
    public function testVCardMenuItemCreation($moduleName, $menuItemShouldBeCreated)
    {
        $menuFile = "modules/$moduleName/clients/base/menus/header/header.php";
        SugarTestHelper::saveFile($menuFile);
        file_put_contents($menuFile, '');

        $scriptObject = new SugarUpgradeMBMenu($this->upgrader);
        SugarTestReflection::callProtectedMethod($scriptObject, 'addMenu', array($moduleName));
        $contents = file_get_contents($menuFile);

        $stringToLookFor = "#$moduleName/vcard-import";
        if ($menuItemShouldBeCreated) {
            $this->assertContains($stringToLookFor, $contents);
        } else {
            $this->assertNotContains($stringToLookFor, $contents);
        }
    }
}
