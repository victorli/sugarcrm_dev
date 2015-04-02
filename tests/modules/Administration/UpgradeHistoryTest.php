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
require_once 'modules/Administration/UpgradeHistory.php';

class UpgradeHistoryTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function testCheckForExistingSQL()
    {
        $patchToCheck = new stdClass();
        $patchToCheck->name = 'abc';
        $patchToCheck->id = '';
            $GLOBALS['db']->query("INSERT INTO upgrade_history (id, name, md5sum, date_entered) VALUES
('444','abc','444','2008-12-20 08:08:20') ");
            $GLOBALS['db']->query("INSERT INTO upgrade_history (id, name, md5sum, date_entered) VALUES
('555','abc','555','2008-12-20 08:08:20')");
		$uh = new UpgradeHistory();
    	$return = $uh->checkForExisting($patchToCheck);
		$this->assertContains($return->id, array('444','555'));
    	
    	$patchToCheck->id = '555';
    	$return = $uh->checkForExisting($patchToCheck);
    	$this->assertEquals($return->id, '444');
    	
    	$GLOBALS['db']->query("delete from upgrade_history where id='444'");
   		$GLOBALS['db']->query("delete from upgrade_history where id='555'");
    }
    
    /**
     * @ticket 44075
     */
    public function testTrackerVisibilityBug44075()
    {
        $uh = new UpgradeHistory();
        $this->assertFalse($uh->tracker_visibility);
    }
}