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

class S_424_3_HealthCheckScannerCasesTestMock extends HealthCheckScannerCasesTestMock
{
    public $md5_files = array(
        './modules/Accounts/Account.php' => 'fakeMD5'
    );

    public function isStockBWCModule()
    {
        return false;
    }

    public function init()
    {
        if (parent::init()) {
            $this->tearDown();
            return true;
        }
        return false;
    }

    public function tearDown()
    {
        unset($GLOBALS['dictionary']['Account']);
        $GLOBALS['reload_vardefs'] = true;
        new Account();
        $GLOBALS['reload_vardefs'] = null;
    }
}
