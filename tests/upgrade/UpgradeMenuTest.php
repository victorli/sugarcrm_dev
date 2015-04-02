<?php
require_once "tests/upgrade/UpgradeTestCase.php";

class UpgradeMenuTest extends UpgradeTestCase
{

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
        sugar_mkdir('modules/menutest');
        mkdir_recursive('modules/menutest2/clients/base/menus/header');
        mkdir_recursive('modules/menutest3/clients/base/menus/header');
        sugar_mkdir('modules/menutestBWC');
        mkdir_recursive('custom/modules/menutest3/clients/base/menus/header');

        file_put_contents('modules/menutest2/clients/base/menus/header/header.php', "<?php echo 'Hello world!'; ");
        file_put_contents('custom/modules/menutest3/clients/base/menus/header/header.php', "<?php echo 'Hello world!'; ");
        $this->bwc = $GLOBALS['bwcModules'];
    }

    public function tearDown()
    {
        parent::tearDown();
        SugarTestHelper::tearDown();
        rmdir_recursive("modules/menutest");
        rmdir_recursive("modules/menutest2");
        rmdir_recursive("modules/menutest3");
        rmdir_recursive("modules/menutestBWC");
        rmdir_recursive("custom/modules/menutest2");
        rmdir_recursive("custom/modules/menutest3");
        $GLOBALS['bwcModules'] = $this->bwc;
    }

    /**
     * Test for ScanModules
     */
    public function testScanModules()
    {
        $this->upgrader->state['MBModules'] = array('menutest', 'menutest2', 'menutest3');
        $GLOBALS['bwcModules'][] = 'menutestBWC';
        $script = $this->upgrader->getScript("post", "7_MBMenu");
        $script->run();

        $this->assertFileExists('modules/menutest/clients/base/menus/header/header.php');
        $this->assertEquals("<?php echo 'Hello world!'; ", file_get_contents('modules/menutest2/clients/base/menus/header/header.php'), "File overwritten for module menutest2");
        $this->assertFileNotExists('modules/menutest3/clients/base/menus/header/header.php');
        $this->assertFileExists('modules/menutestBWC/clients/base/menus/header/header.php');

        include 'modules/menutest/clients/base/menus/header/header.php';
        $this->assertEquals('LNK_NEW_RECORD', $viewdefs['menutest']['base']['menu']['header'][0]['label']);
        $this->assertEquals('#menutest', $viewdefs['menutest']['base']['menu']['header'][1]['route']);
    }
}