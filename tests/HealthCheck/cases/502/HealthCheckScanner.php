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

class S_502_HealthCheckScannerCasesTestMock extends HealthCheckScannerCasesTestMock
{
    // TODO: unskip when we decide to enable it once again in later releases (probably > 7.5.x). (See CRYS-455).
    public $skip = true;

    public $md5_files = array(
        './randomFile.php' => 'incorrectMD5'
    );
}
