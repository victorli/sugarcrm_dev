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

require_once 'include/MetaDataManager/MetaDataManager.php';
        
class MetaDataLocationChangeTest extends Sugar_PHPUnit_Framework_TestCase
{
    
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }
    
    /**
     * @dataProvider _mobileMetaDataFilesExistsProvider
     * @param string $module The module name
     * @param string $view The view type
     * @param string $filepath The path to the metadata file
     */
    public function testMobileMetaDataFilesExists($module, $view, $filepath)
    {
        $exists = file_exists($filepath);
        $this->assertTrue($exists, "Mobile metadata file for $view view of the $module module was not found");
    }
    
    
    /**
     * @dataProvider _platformList
     * @param string $platform The platform to test
     */
    public function testMetaDataManagerReturnsCorrectPlatformResults($platform)
    {
        $mm = MetaDataManager::getManager(array($platform));
        $data = $mm->getModuleViews('Bugs');
        $this->assertTrue(isset($data['list']['meta']['panels']), "Panels meta array for list not set for $platform platform of Bugs module");
        $this->assertTrue(isset($data['record']['meta']['panels']), "Panels meta array for record not set for $platform platform of Bugs module");
    }
    
    
    public function _mobileMetaDataFilesExistsProvider()
    {
        return array(
            array('module' => 'Accounts', 'view' => 'edit', 'filepath' => 'modules/Accounts/clients/mobile/views/edit/edit.php'),
            array('module' => 'Cases', 'view' => 'detail', 'filepath' => 'modules/Cases/clients/mobile/views/detail/detail.php'),
            array('module' => 'Contacts', 'view' => 'edit', 'filepath' => 'modules/Contacts/clients/mobile/views/edit/edit.php'),
            array('module' => 'Employees', 'view' => 'list', 'filepath' => 'modules/Employees/clients/mobile/views/list/list.php'),
            array('module' => 'Meetings', 'view' => 'detail', 'filepath' => 'modules/Meetings/clients/mobile/views/detail/detail.php'),
        );
    }
    
    
    public function _platformList()
    {
        return array(
            array('platform' => 'mobile'),
        );
    }
    
}
