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

class S_501_2_HealthCheckScannerCasesTestMock extends HealthCheckScannerCasesTestMock
{
    public $skip = true;
    public $not = true;
    public $md5_files = array(
        './styleguide/less/bootstrap-mobile.less copy' => 'fakeMD5',
        './styleguide/less/bootstrap.less copy' => 'fakeMD5',
    );
}
