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
 
require_once('modules/Emails/Email.php');

/**
 * Test case for Bugs 50972, 50973 and 50979
 */
class BugNullAssignedUserIdTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $old_current_user;
    private $current_user;
    private $email;
	
	public function setUp()
	{
	    global $current_user;
        
        if (!empty($current_user)) {
            $this->old_current_user = $current_user;
        }
		
	    $this->current_user = SugarTestUserUtilities::createAnonymousUser();
        
        $GLOBALS['current_user'] = $this->current_user;
	    $this->email = new Email();
	    $this->email->email2init();

        // Set some values for some fields so the query is actually built
        $this->email->id = '1';
        $this->email->created_by = $this->current_user->id;
        $this->email->date_modified = date('Y-m-d H:i:s');

        // Specify an empty assigned user id for testing nulls
        $this->email->assigned_user_id = '';
	}
	
	public function tearDown()
	{
		unset($this->email);
		SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
		unset($this->current_user);
        
        if ($this->old_current_user) {
            $GLOBALS['current_user'] = $this->old_current_user;
        }
	}

    public function testNullAssignedUserIdConvertedToEmptyInSave() {
        $query = $this->email->db->updateSQL($this->email);
        $this->assertContains("assigned_user_id=''", $query, 'Assigned user id set to empty string not found');
    }

    public function testNullAssignedUserIdInSave() {
        $this->email->setFieldNullable('assigned_user_id');
        $query = $this->email->db->updateSQL($this->email);
        $this->email->revertFieldNullable('assigned_user_id');
        $this->assertContains('assigned_user_id=NULL', $query, 'Assigned user id set to DB NULL value not found');
    }
}
?>