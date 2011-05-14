<?php
require_once 'include/dir_inc.php';

class UpgradeCustomTemplateMetaTest extends Sugar_PHPUnit_Framework_TestCase  {

    var $merge;

    function setUp()
    {
       $this->setOutputBuffering = false;
       SugarTestMergeUtilities::setupFiles(array('Calls', 'Meetings'), array('editviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/metadata_files');
    }


    function tearDown()
    {
       SugarTestMergeUtilities::teardownFiles();
    }

    function testMergeCallsEditviewdefsFor611()
    {
       require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
       $this->merge = new EditViewMerge();
       $this->merge->merge('Calls', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/611/modules/Calls/metadata/editviewdefs.php','modules/Calls/metadata/editviewdefs.php','custom/modules/Calls/metadata/editviewdefs.php');

       //Load file
       require('custom/modules/Calls/metadata/editviewdefs.php');

        $this->assertNotContains('forms[0]', $viewdefs['Calls']['EditView']['templateMeta']['form']['buttons'][0]['customCode'], "forms[0] did not get replaced");
    }

    function testMergeMeetingsEditviewdefsFor611()
    {
       require_once 'modules/UpgradeWizard/SugarMerge/EditViewMerge.php';
       $this->merge = new EditViewMerge();
       $this->merge->merge('Meetings', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/611/modules/Meetings/metadata/editviewdefs.php','modules/Meetings/metadata/editviewdefs.php','custom/modules/Meetings/metadata/editviewdefs.php');

       //Load file
       require('custom/modules/Meetings/metadata/editviewdefs.php');

        $this->assertNotContains('this.form.', $viewdefs['Meetings']['EditView']['templateMeta']['form']['buttons'][0]['customCode'], "this.form did not get replaced");
    }


}