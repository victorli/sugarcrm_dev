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

/*
 * Test for CRYS-473. badVardefsKey code check
 */

class S_419_3_HealthCheckScannerCasesTestMock extends HealthCheckScannerCasesTestMock
{
    public $md5_files = array(
        './modules/ProspectLists/ProspectList.php' => 'fakeMD5'
    );

    public function init()
    {
        if (parent::init()) {
            $this->tearDown();
            return true;
        }
        return false;
    }

    protected function getModuleList()
    {
        $result = parent::getModuleList();
        $this->beanList['ProspectLists'] = 'ProspectList';
        $this->beanFiles['ProspectList'] = 'modules/ProspectLists/ProspectList.php';
        return $result;
    }

    public function tearDown()
    {
        unset($GLOBALS['dictionary']['ProspectList']);
        $GLOBALS['reload_vardefs'] = true;
        new ProspectList();
        $GLOBALS['reload_vardefs'] = null;
    }
}
