<?php
require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/7_MBMenu.php';

class UpgradeMBMenuTest extends UpgradeTestCase
{
    /**
     * @var array Temporary storage for $GLOBALS['bwcModules'].
     */
    protected $tmpBwcModules;

    /**
     * @var string Test module name.
     */
    protected $module = 'Test_Module';

    public function setUp()
    {
        mkdir_recursive("modules/{$this->module}");
        file_put_contents(
            "modules/{$this->module}/{$this->module}.php",
            "<?php\n class {$this->module} extends SugarBean {}"
        );
        file_put_contents(
            "modules/{$this->module}/Menu.php",
            "<?php\n \$module_menu = array(1);"
        );

        $this->tmpBwcModules = $GLOBALS['bwcModules'];

        parent::setUp();
    }

    public function tearDown()
    {
        $GLOBALS['bwcModules'] = $this->tmpBwcModules;

        rmdir_recursive("modules/{$this->module}");

        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Modules BWC'ed while upgrading should get sidecar menu.
     */
    public function testConvertMenuForBWCModules()
    {
        $GLOBALS['bwcModules'] = array($this->module);

        $scriptMock = $this->getMockBuilder('SugarUpgradeMBMenu')
            ->disableOriginalConstructor()
            ->setMethods(array('addMenu'))
            ->getMock();

        $scriptMock->expects($this->once())->method('addMenu');

        $scriptMock->run();
    }
}
