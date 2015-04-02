<?php
require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'modules/ModuleBuilder/parsers/constants.php';
require_once 'modules/ModuleBuilder/parsers/views/PopupMetaDataParser.php';
require_once 'modules/ModuleBuilder/parsers/views/SidecarListLayoutMetaDataParser.php';

class UpgradeConvertPopupListView extends UpgradeTestCase
{
    /**
     * @var string
     */
    public $popupListPath;

    /**
     * @var string
     */
    public $selectionListPath;

    /**
     * @var array
     */
    public $newDefs;

    /**
     * @var string
     */
    public $module = 'Accounts';

    public function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');

        $this->newDefs = array(
            'NAME' => array(
                'width' => '40%',
                'label' => 'LBL_LIST_ACCOUNT_NAME',
                'link' => true,
                'default' => true,
                'name' => 'name',
            ),
            'EMAIL1' => array(
                'type' => 'varchar',
                'studio' => array(
                    'editview' => true,
                    'editField' => true,
                    'searchview' => false,
                    'popupsearch' => false,
                ),
                'label' => 'LBL_EMAIL_ADDRESS',
                'width' => '10%',
                'default' => true,
            ),
            // Hidden by default field.
            'DESCRIPTION' => array(
                'type' => 'text',
                'label' => 'LBL_DESCRIPTION',
                'sortable' => false,
                'width' => '10%',
                // Name intentionally omitted.
                'default' => true,
            ),
        );
        $this->popupListPath = "custom/modules/{$this->module}/metadata/popupdefs.php";
        $this->selectionListPath = "custom/modules/{$this->module}" .
            '/clients/base/views/selection-list/selection-list.php';

        $parser = new PopupMetaDataParser(MB_POPUPLIST, $this->module);
        $parser->_viewdefs = $this->newDefs;
        $parser->handleSave(false);
    }

    public function tearDown()
    {
        if (is_file($this->popupListPath)) {
            SugarAutoLoader::unlink($this->popupListPath);
        }
        if (is_file($this->selectionListPath)) {
            SugarAutoLoader::unlink($this->selectionListPath);
        }
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testConvertPopupListFieldsToSidecarFormat()
    {
        $script = $this->upgrader->getScript('post', '7_ConvertPopupListView');
        $script->from_version = 6.7;
        $script->to_version = 7.2;
        $script->run();

        $this->assertFileExists($this->selectionListPath);
        $sidecarParser = new SidecarListLayoutMetaDataParser(MB_SIDECARPOPUPVIEW, $this->module, null, 'base');

        $name = $sidecarParser->panelGetField('name');
        $this->assertTrue($name['field']['default']);

        $email = $sidecarParser->panelGetField('email1');
        $this->assertTrue($email['field']['default']);

        $description = $sidecarParser->panelGetField('description');
        $this->assertTrue($description['field']['default']);
    }
}
