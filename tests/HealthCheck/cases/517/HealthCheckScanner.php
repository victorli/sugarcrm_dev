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

class S_517_PackageManager extends PackageManager
{
    function getinstalledPackages($types = array('module', 'langpack'))
    {
        return array(
            array(
                'enabled' => 'ENABLED',
                'name' => 'Zendesk',
                'version' => '2.7',
            ),
        );
    }
}

class S_517_HealthCheckScannerCasesTestMock extends HealthCheckScannerCasesTestMock
{
    public function getPackageManager()
    {
        return new S_517_PackageManager();
    }
}
