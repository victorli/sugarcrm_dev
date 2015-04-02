<?php

class ModuleInstallerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @covers ModuleInstaller::merge_files
     */
    public function testMergeFiles()
    {
        $minst = $this->getMock('ModuleInstaller', array('mergeModuleFiles'));
        $minst->expects($this->once())->method('mergeModuleFiles')
            ->with('application', 'foo', 'bar', 'baz');
        $minst->merge_files('foo', 'bar', 'baz', true);
    }

    /**
     * @covers ModuleInstaller::merge_files
     */
    public function testMergeFiles2()
    {
        $minst = $this->getMock('ModuleInstaller', array('mergeModuleFiles'));
        // We add one to the count for the application extension invocation.
        $count = count($minst->modules) + 1;
        $minst->expects($this->exactly($count))->method('mergeModuleFiles')
            ->with($this->anything(), 'foo', 'bar', 'baz');
        $minst->merge_files('foo', 'bar', 'baz', false);
    }

    public function testModuleDirs()
    {
        $modules = ModuleInstaller::getModuleDirs();
        $this->assertContains("ActivityStream/Activities", $modules, "ActivityStream/Activities not found!");
    }
}
