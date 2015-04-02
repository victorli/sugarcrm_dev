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

class S_902_HealthCheckScannerCasesTestMock extends HealthCheckScannerCasesTestMock
{
    public function getVersionAndFlavor()
    {
        return array('6.5.18', 'ent');
    }

    public function getVersion()
    {
        return array('7.6.0.0', 'ent');
    }

    public function isDBValid($sugar_version)
    {
        return !(version_compare($sugar_version, 7, '<') && !($this->db instanceof MysqlManager));
    }

    protected function init()
    {
        $this->db = null;
        $this->bwcModulesHash = array_flip($this->bwcModules);
        return true;
    }
}
