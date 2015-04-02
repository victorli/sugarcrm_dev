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

class S_503_HealthCheckScannerCasesTestMock extends HealthCheckScannerCasesTestMock
{
    public function init()
    {
        if (parent::init()) {
            $this->beanList['503Module'] = '503Module';
            $this->newModules['503Module'] = '503Module';
            return true;
        }
        return false;
    }

    protected function getModuleList()
    {
        return array('503Module');
    }

    public function tearDown()
    {
        unset($this->beanList['503Module'], $this->newModules['503Module']);
    }
}
