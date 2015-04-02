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

require_once('modules/Users/User.php');
/**
 * Bug #47156
 * Reassigning Users With Instance That Has Numeric Ids
 * @ticket 47156
 */
class Bug47156Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $user1;
    private $user2;

    private function createUser($id = '', $status = '')
    {
        $time = mt_rand();
        $userId = 'SugarUser';
        $user = new User();
        $user->user_name = $userId . $time;
        $user->user_hash = md5($userId.$time);
        $user->first_name = $userId;
        $user->last_name = $time;
        if (!empty($status))
        {
            $user->status=$status;
        }
        else
        {
            $user->status='Active';
        }
        
        $user->default_team = '1'; //Set Default Team to Global
        if(!empty($id))
        {
            $user->new_with_id = true;
            $user->id = $id;
        }

        $user->save();
        $user->fill_in_additional_detail_fields();
        
        return $user;
    }
    
    /**
     * @group 47156
     */
    public function testCorrectUserListOutput()
    {
        $this->user1 = $this->createUser(11, 'Active');
        $this->user2 = $this->createUser(12, 'Inactive');
        
        $allUsers = User::getAllUsers(); 
        
        $this->assertArrayHasKey($this->user1->id, $allUsers);
        $this->assertArrayHasKey($this->user2->id, $allUsers);
        
        $dbManager = $GLOBALS['db'];
        $dbManager->query('DELETE FROM users WHERE id IN (' . $dbManager->quoted($this->user1->id) . ', ' . $dbManager->quoted($this->user2->id) . ')');
        $dbManager->query('DELETE FROM user_preferences WHERE assigned_user_id IN (' . $dbManager->quoted($this->user1->id) . ', ' . $dbManager->quoted($this->user2->id) . ')');
        $dbManager->query('DELETE FROM teams WHERE associated_user_id IN (' . $dbManager->quoted($this->user1->id) . ', ' . $dbManager->quoted($this->user2->id) . ')');
        $dbManager->query('DELETE FROM team_memberships WHERE user_id IN (' . $dbManager->quoted($this->user1->id) . ', ' . $dbManager->quoted($this->user2->id) . ')');
    }
}
?>