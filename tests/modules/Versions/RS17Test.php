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

require_once('modules/Versions/Version.php');
require_once('modules/Versions/CheckVersions.php');

/*
* RS-17: Prepare Versions Module
*/

class RS17Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * Test assert that we don't get any db error in the function
     */
    public function testGetInvalidVersions()
    {
    	$invalidVersions = get_invalid_versions();
    	$this->assertInternalType('array', $invalidVersions);
    }

    public function dataProviderDefaultVersions()
    {
        static $versions;
        if (!$versions) {
            include 'modules/Versions/DefaultVersions.php';
            $versions = $default_versions;
        }
        return $versions;
    }

    /**
     * Test assert that we don't get any db error in the method
     * @dataProvider dataProviderDefaultVersions
     * @covers Versions::mark_upgraded
     */
    public function testMarkUpdated($name, $db_version, $file_version)
    {
        $version = new Version();
        $this->assertEmpty($version->mark_upgraded($name, $db_version, $file_version));
    }

}
