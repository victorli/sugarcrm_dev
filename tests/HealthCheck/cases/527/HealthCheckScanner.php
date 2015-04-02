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

class S_527_HealthCheckScannerCasesTestMock extends HealthCheckScannerCasesTestMock
{
    public $md5_files = array(
        './modules/FakeAccounts/FakeAccount.php' => 'fakeMD5',
    );

    public function init()
    {
        if (parent::init()) {
            $this->tearDown();
            SugarAutoLoader::addToMap('modules/FakeAccounts/vardefs.php');
            SugarAutoLoader::addToMap('modules/FakeAccounts/FakeAccount.php');
            require_once 'modules/FakeAccounts/FakeAccount.php';
            return true;
        }
        return false;
    }

    public function getModuleList()
    {
        $result = parent::getModuleList();
        $this->beanList['FakeAccounts'] = 'FakeAccount';
        $GLOBALS['beanList']['FakeAccounts'] = 'FakeAccount';
        return $result;
    }

    public function tearDown()
    {
        SugarAutoLoader::delFromMap('modules/FakeAccounts/vardefs.php');
        SugarAutoLoader::delFromMap('modules/FakeAccounts/FakeAccount.php');
        unset($GLOBALS['dictionary']['FakeAccount']);
        unset($GLOBALS['beanList']['FakeAccounts']);
    }
}
