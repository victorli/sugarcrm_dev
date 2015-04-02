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

require_once 'modules/ModuleBuilder/parsers/views/SidecarListLayoutMetaDataParser.php';
require_once 'modules/ModuleBuilder/parsers/views/SidecarGridLayoutMetaDataParser.php';

/**
 * Accessor class, in the event the parsers public properties go protected, which
 * they are slated to do.
 */
class Bug54939TestListParser extends SidecarListLayoutMetaDataParser {
    public function changeFieldType($field, $type) {
        $this->_fielddefs[$field]['type'] = $type;
    }
}

class Bug54939TestGridParser extends SidecarGridLayoutMetaDataParser {
    public function changeFieldType($field, $type) {
        $this->_fielddefs[$field]['type'] = $type;
    }

    public function isAvailableFieldName($name, $fields) {
        foreach ($fields as $field) {
            if (isset($field['name']) && $field['name'] == $name) {
                return true;
            }
        }

        return false;
    }
}

class Bug54939Test extends Sugar_PHPUnit_Framework_TestCase {
    public function setUp() {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testClientIsSet() {
        $grid = new Bug54939TestGridParser(MB_WIRELESSEDITVIEW, 'Bugs', '', MB_WIRELESS);
        $this->assertNotEmpty($grid->client, 'Client was not set');
        $this->assertEquals(MB_WIRELESS, $grid->client, 'Client was not properly set');
    }
    
    
    public function testMobileLayoutDoesIncludeInvalidPortalFields() {
        $list = new Bug54939TestListParser(MB_WIRELESSLISTVIEW, 'Cases', '', MB_WIRELESS);
        $list->changeFieldType('description', 'iframe');
        $list->changeFieldType('work_log', 'relate');
        $fields = $list->getAvailableFields();
        $this->assertArrayHasKey('description', $fields, 'The resolution field was excluded');
        $this->assertArrayHasKey('work_log', $fields, 'The work_log field was excluded');

        $grid = new Bug54939TestGridParser(MB_WIRELESSDETAILVIEW, 'Cases', '', MB_WIRELESS);
        $grid->changeFieldType('work_log', 'parent');
        $grid->changeFieldType('system_id', 'encrypt');
        $fields = $grid->getAvailableFields();

        $available = $grid->isAvailableFieldName('work_log', $fields);
        $this->assertTrue($available, 'The work_log field was excluded');

        $available = $grid->isAvailableFieldName('system_id', $fields);
        $this->assertTrue($available, 'The system_id field was excluded');
    }
}
