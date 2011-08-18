<?php

require_once('modules/Import/Importer.php');

class Bug45963Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }

    /**
     * @group bug45963
     */
    public function testGetImportableModules()
    {
        $modules = Importer::getImportableModules();

        $this->assertEmpty($modules['Groups']);
        $this->assertNotEmpty($modules['Contacts']);
        $this->assertNotEmpty($modules['Accounts']);
    }
}

