<?php

class Bug39598Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $tmpDir;
    private $tmpFile;

    public function setUp()
    {
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bug32821';
        mkdir($this->tmpDir);
        $this->tmpFile = tempnam($this->tmpDir, 'bug32821');
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