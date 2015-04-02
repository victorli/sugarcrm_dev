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

require_once 'tests/rest/RestTestBase.php';
require_once 'include/MetaDataManager/MetaDataManager.php';

/**
 * Tests the rest metadata endpoint
 *
 * Note: To prevent issues with caching, if you send more than one rest request
 * in a test, make sure to call $this->_clearMetadataCache() after every rest
 * call you make in this class. This is not required for the last call since it
 * will be called in the tearDown method as well.
 */
class RestMetadataModuleListTest extends RestTestBase {

    public $unitTestFiles = array();
    public $createdStudioFile = false;

    public function setUp()
    {
        $this->markTestIncomplete("Marking this test skipped until we can refactor because it's invalid");
        return;
        parent::setUp();
        // Portal test needs this one, tear down happens in parent
        SugarTestHelper::setup('mod_strings', array('ModuleBuilder'));

        $this->unitTestFiles[] = 'custom/include/MVC/Controller/wireless_module_registry.php';

        // Start off by clearing our metadata cache
        $this->_clearMetadataCache();
    }

    public function tearDown()
    {
        // Clear the metadata cache for other tests downstream
        $this->_clearMetadataCache();

        foreach($this->unitTestFiles as $unitTestFile ) {
            if ( file_exists($unitTestFile) ) {
                // Ignore the warning on this, the file stat cache causes the file_exist to trigger even when it's not really there
                SugarAutoLoader::unlink($unitTestFile);
            }
        }

        if ($this->createdStudioFile && file_exists('modules/Opportunities/metadata/studio.php')) {
            SugarAutoLoader::unlink('modules/Opportunities/metadata/studio.php');
        }
        SugarAutoLoader::saveMap();
        parent::tearDown();
    }


    /**
     * @group rest
     */
    public function testMetadataGetModuleListMobile() {
        $restReply = $this->_restCall('metadata?type_filter=module_list&platform=mobile&test=4');
        $this->_clearMetadataCache();

        foreach (SugarAutoLoader::existingCustom('include/MVC/Controller/wireless_module_registry.php') as $file) {
            require $file;
        }

        // $wireless_module_registry is defined in the file loaded above
        $enabledMobile = array_keys($wireless_module_registry);


        $this->assertTrue(isset($restReply['reply']['module_list']['_hash']),'There is no mobile module list');
        $restModules = $restReply['reply']['module_list'];
        unset($restModules['_hash']);
        foreach ( $enabledMobile as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the mobile module list.');
        }
        $this->assertEquals(count($enabledMobile),count($restModules),'There are extra modules in the mobile module list');

        // Create a custom set of wireless modules to test if it is loading those properly
        SugarAutoLoader::ensureDir('custom/include/MVC/Controller');
        SugarAutoLoader::put('custom/include/MVC/Controller/wireless_module_registry.php','<'."?php\n".'$wireless_module_registry = array("Accounts"=>"Accounts","Contacts"=>"Contacts","Opportunities"=>"Opportunities");', true);

        $enabledMobile = array('Accounts','Contacts','Opportunities');

        $restReply = $this->_restCall('metadata?type_filter=module_list&platform=mobile&test=5');
        $this->assertTrue(isset($restReply['reply']['module_list']['_hash']),'There is no mobile module list on the second pass');
        $restModules = $restReply['reply']['module_list'];
        unset($restModules['_hash']);
        foreach ( $enabledMobile as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the mobile module list on the second pass');
        }
        $this->assertEquals(count($enabledMobile),count($restModules),'There are extra modules in the mobile module list on the second pass');


    }


    /**
     * @group rest
     */
    public function testMetadataGetModuleListBase() {
        $restReply = $this->_restCall('metadata?type_filter=module_list&test=6');

        $this->assertTrue(isset($restReply['reply']['module_list']['_hash']),'There is no base module list');
        $restModules = $restReply['reply']['module_list'];
        unset($restModules['_hash']);

        // Get the expected
        $modules = $this->_getModuleListsLikeTheAPIDoes();
        $modules = $modules['module_list'];

        // Diff
        $extras = array_diff($restModules, $modules);

        // Assert
        $this->assertEmpty($extras, "There are extra modules in the REST list");
    }

    /**
     * @group rest
     */
    public function testMetadataGetFullModuleListBase() {
        $restReply = $this->_restCall('metadata?type_filter=full_module_list&test=7');
        $this->assertArrayHasKey('full_module_list', $restReply['reply'], "Full Module List is missing from the reply");
        $fullRestModules = $restReply['reply']['full_module_list'];
        $this->assertArrayHasKey('_hash', $fullRestModules, 'There is no _hash key in the response');
        unset($fullRestModules['_hash']);

        // Now get what we expect
        $fullModuleList = $this->_getFullModuleListLikeTheAPIDoes();

        // Check for differences
        $extras = array_diff($fullRestModules, $fullModuleList);

        // Assert
        $this->assertEmpty($extras, "There are extra modules in the rest reply");
    }

    /**
     * Helper function that gets a full module list like the API would do
     *
     * @return array
     */
    protected function _getFullModuleListLikeTheAPIDoes() {
        $data = $this->_getModuleListsLikeTheAPIDoes();
        return $data['full_module_list'];
    }

    /**
     * Helper method to get all the module lists that the API would get. Returns
     * an array of modules, module_list and full_module_list
     *
     * @return array
     */
    protected function _getModuleListsLikeTheAPIDoes() {
        // Get the metadata manager
        $mm = MetaDataManager::getManager();

        // Get the api
        require_once 'clients/base/api/MetadataApi.php';
        $api = new MetadataApi();

        $data['module_list'] = $api->getModuleList();
        $data['full_module_list'] = $data['module_list'];

        $data['modules'] = array();

        foreach($data['full_module_list'] as $module) {
            $bean = BeanFactory::newBean($module);
            if (!$bean || !is_a($bean,'SugarBean') ) {
                // There is no bean, we can't get data on this
                continue;
            }

            $modData = $mm->getModuleData($module);

            //Skip for beans that don't have a real table because they don't have indices declared which mean
            //they don't have the sortable key in the field definition by default (not sure why the code operates that way)
            if(isset($modData['table']) && $modData['table'] == 'does_not_exist')
            {
               continue;
            }

            $data['modules'][$module] = $modData;

            if (isset($data['modules'][$module]['fields'])) {
                $fields = $data['modules'][$module]['fields'];
                foreach($fields as $fieldName => $fieldDef) {
                    // make sure we got sortable in all these field defs.
                    $this->assertArrayHasKey('sortable', $fieldDef, "Sortable isn't listed in the fields for $module");
                    if (isset($fieldDef['type']) && ($fieldDef['type'] == 'relate')) {
                        if (isset($fieldDef['module']) && !in_array($fieldDef['module'], $data['full_module_list'])) {
                            $data['full_module_list'][$fieldDef['module']] = $fieldDef['module'];
                        }
                    } elseif (isset($fieldDef['type']) && ($fieldDef['type'] == 'link')) {
                        $bean->load_relationship($fieldDef['name']);
                        $otherSide = $bean->$fieldDef['name']->getRelatedModuleName();
                        $data['full_module_list'][$otherSide] = $otherSide;
                    }
                }
            }
        }

        foreach($data['modules'] as $moduleName => $moduleDef) {
            if (!array_key_exists($moduleName, $data['full_module_list'])) {
                unset($data['modules'][$moduleName]);
            }
        }

        return $data;
    }


    /**
     * @group Bug57644
     */
    public function testMetadataModulesWithoutIndex() {

        require_once("data/BeanFactory.php");
        $obj = BeanFactory::getObjectName("Bugs");

        require_once("include/SugarObjects/VardefManager.php");
        VardefManager::loadVardef("Bugs", $obj);
        global $dictionary;

        // Blank the indices
        if(isset($dictionary[$obj]['indices'])) {
            $dictionary[$obj]['indices'] = array();
        }
        $reply = $this->_getModuleListsLikeTheAPIDoes();
        $this->assertNotEmpty($reply);
        $this->assertNotEmpty($reply['modules']['Bugs']);
        foreach($reply['modules']['Bugs']['fields'] as $fieldName => $fieldDef){
            $this->assertTrue(isset($fieldDef['sortable']), 'Bugs field ' . $fieldName . ' does not have sortable set');
        }

    }


}
