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

class Bug39598Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $tmpDir;
    private $tmpFile;

    public function setUp()
    {
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bug39598';
        sugar_mkdir($this->tmpDir);
        $this->tmpFile = tempnam($this->tmpDir, 'bug39598');
        //rename file to 'relationships.php'
        rename($this->tmpFile, $this->tmpDir . DIRECTORY_SEPARATOR . 'relationships.php');
        $this->tmpFile = $this->tmpDir . DIRECTORY_SEPARATOR . 'relationships.php';
        file_put_contents($this->tmpFile, '[\'test_oldname\'] => ');
    }

    public function tearDown()
    {
        unlink($this->tmpFile);
        rmdir($this->tmpDir);
    }

    public function testRelationshipName()
    {
        $mbModule = new MBModule('newname', $this->tmpDir, 'test', 'test');
        $mbModule->renameMetaData($this->tmpDir, 'test_oldname');
        $replacedContents = file_get_contents($this->tmpFile);
        $this->assertEquals('[\'test_newname\'] => ', $replacedContents, 'Module name replaced correctly in relationships metadata');

    }
}
