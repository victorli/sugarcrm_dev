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


require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/5_FTSHook.php';

class SugarUpgradeFTSHookTest extends UpgradeTestCase
{

    private $hookDef = array(
        1,
        'fts',
        'include/SugarSearchEngine/SugarSearchEngineQueueManager.php',
        'SugarSearchEngineQueueManager',
        'populateIndexQueue'
    );

    private $possibleHookDefs = array(
        'Ext/LogicHooks/fts.php',
        'application/Ext/LogicHooks/logichooks.ext.php',
        'Extension/application/Ext/LogicHooks/SugarFTSHooks.php',
    );

    public function dataProviderRun()
    {
        return array(
            array(null, array($this->hookDef)),                                 // one definition no actions
            array('create', array()),                                           // no definitions no create
            array('removeDublicates', array($this->hookDef, $this->hookDef)),   // no definitions no create
        );
    }

    /**
     * @param $needCall
     * @param $hookDefs
     * @covers       SugarUpgradeFTSHook::run
     * @dataProvider dataProviderRun
     */
    public function testRun($needCall, $hookDefs)
    {
        $mockInstaller = $this->getMock('SugarUpgradeFTSHook',
            array('create', 'removeDublicates', 'getHooks', 'isFTSHook'),
            array($this->upgrader)
        );

        $mockInstaller->expects($this->once())->method('getHooks')->willReturn($hookDefs);
        $mockInstaller->expects($this->exactly(count($hookDefs)))->method('isFTSHook')->willReturn(true);

        foreach (array('create', 'removeDublicates') as $method) {
            if ($needCall == $method) {
                $mockInstaller->expects($this->once())->method($method);
            } else {
                $mockInstaller->expects($this->never())->method($method);
            }
        }

        $mockInstaller->run();
    }

    public function dataProviderIsFTSHook()
    {
        return array(
            array(false, array(1, 'invalidGroup'), SugarUpgradeFTSHook::HOOK_CLASS),
            array(
                false,
                array(
                    1,
                    SugarUpgradeFTSHook::HOOK_GROUP,
                    '',
                    'invalidClass',
                    SugarUpgradeFTSHook::HOOK_METHOD
                ),
                SugarUpgradeFTSHook::HOOK_CLASS
            ),
            array(
                false,
                array(1, SugarUpgradeFTSHook::HOOK_GROUP, '', SugarUpgradeFTSHook::HOOK_CLASS, 'invalidMethod'),
                SugarUpgradeFTSHook::HOOK_CLASS
            ),
            array(
                true,
                array(
                    1,
                    SugarUpgradeFTSHook::HOOK_GROUP,
                    '',
                    SugarUpgradeFTSHook::HOOK_CLASS,
                    SugarUpgradeFTSHook::HOOK_METHOD
                ),
                SugarUpgradeFTSHook::HOOK_CLASS
            ),
        );
    }

    /**
     * @param $expected
     * @param $hook
     * @param $hookClass
     * @covers       SugarUpgradeFTSHook::isFTSHook
     * @dataProvider dataProviderIsFTSHook
     */
    public function testIsFTSHook($expected, $hook, $hookClass)
    {
        $mockInstaller = $this->getMock('SugarUpgradeFTSHook',
            array('getHookClass'),
            array($this->upgrader)
        );

        $mockInstaller->expects($this->any())->method('getHookClass')->willReturn($hookClass);

        $actualRes = SugarTestReflection::callProtectedMethod($mockInstaller, 'isFTSHook', array($hook));

        $this->assertEquals($expected, $actualRes);
    }

    /**
     * @param $fileThatExists
     * @covers       SugarUpgradeFTSHook::getMainDefFile
     * @dataProvider dataProviderMainDefFile
     */
    public function testGetMainDefFile($fileThatExists)
    {
        $mockInstaller = $this->getMock('SugarUpgradeFTSHook',
            array('fileExists'),
            array($this->upgrader)
        );

        foreach ($this->possibleHookDefs as $key => $file) {
            $mockInstaller->expects($this->at($key))
                ->method('fileExists')
                ->with($file)
                ->willReturn($file == $fileThatExists);
            if ($file == $fileThatExists) {
                break;
            }
        }

        SugarTestReflection::setProtectedValue($mockInstaller, 'possibleHookDefs', $this->possibleHookDefs);

        $actualRes = SugarTestReflection::callProtectedMethod($mockInstaller, 'getMainDefFile');

        $this->assertEquals($fileThatExists, $actualRes);
    }

    public function dataProviderMainDefFile()
    {
        $res = array();
        foreach ($this->possibleHookDefs as $file) {
            $res[] = array($file);
        }

        return $res;
    }

    public function testRemoveDublicates()
    {
        $mockInstaller = $this->getMock('SugarUpgradeFTSHook',
            array('unlink', 'getMainDefFile'),
            array($this->upgrader)
        );

        $mockInstaller->expects($this->exactly(2))
            ->method('unlink')
            ->with($this->logicalOr(
                $this->equalTo($this->possibleHookDefs[0]),
                $this->equalTo($this->possibleHookDefs[2])));

        $mockInstaller->expects($this->any())
            ->method('getMainDefFile')
            ->willReturn($this->possibleHookDefs[1]);


        SugarTestReflection::setProtectedValue($mockInstaller, 'possibleHookDefs', $this->possibleHookDefs);

        SugarTestReflection::callProtectedMethod($mockInstaller, 'removeDublicates');
    }

}
