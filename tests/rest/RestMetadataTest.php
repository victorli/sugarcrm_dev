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

require_once('tests/rest/RestTestBase.php');

class RestMetadataTest extends RestTestBase {
    public $createdFiles = array();

    public function tearDown()
    {
        // Cleanup
        foreach($this->createdFiles as $file)
        {
        	if (is_file($file)) {
        		SugarAutoLoader::unlink($file, true);
            }

            if (file_exists($file . '.testbackup')) {
                rename($file . '.testbackup', $file);
            }
        }

        sugar_cache_clear('app_strings.en_us');

        parent::tearDown();
    }
    /**
     * @group rest
     */
    public function testFullMetadata() {
        $restReply = $this->_restCall('metadata');

        $this->assertTrue(isset($restReply['reply']['_hash']),'Primary hash is missing.');
        $this->assertTrue(isset($restReply['reply']['modules']),'Modules are missing.');

        $this->assertTrue(isset($restReply['reply']['fields']),'SugarFields are missing.');
        $this->assertTrue(isset($restReply['reply']['views']),'Views are missing.');
        $this->assertTrue(isset($restReply['reply']['currencies']),'Currencies are missing.');
        $this->assertTrue(isset($restReply['reply']['jssource']),'JSSource is missing.');
        // SIDECAR-14 - Move server info into the metadata api
        $this->assertTrue(isset($restReply['reply']['server_info']), 'ServerInfo is missing');
    }

}
