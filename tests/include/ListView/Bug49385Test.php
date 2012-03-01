<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2012 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


 
require_once('data/SugarBean.php');

/**
 * Bug49385Test.php
 *
 * This test handles verifying that the SQL string or array returned from the create_new_list_query call can properly
 * processing the alter_many_to_many_query flag in SugarBean.
 *
 * @author Collin Lee
 *
 */
class Bug49385Test extends Sugar_PHPUnit_Framework_OutputTestCase
{

    static $call_id = null;
    static $meeting_id = null;
    static $contact_id = null;

    static public function setUpBeforeClass()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();

        global $beanList, $beanFiles;
        require('include/modules.php');

        $GLOBALS['db']->query("DELETE FROM meetings WHERE name = 'Bug49385Test'");
        $GLOBALS['db']->query("DELETE FROM calls WHERE name = 'Bug49385Test'");

        $contact1 = SugarTestContactUtilities::createContact();
        Bug49385Test::$contact_id = $contact1->id;

        $contact2 = SugarTestContactUtilities::createContact();
        $contact3 = SugarTestContactUtilities::createContact();

        $meeting = SugarTestMeetingUtilities::createMeeting();
        Bug49385Test::$meeting_id = $meeting->id;
        $meeting->name = 'Bug49385Test';
        $meeting->save();

        $data_values = array('accept_status'=>'none');

        $relate_values = array('contact_id'=>$contact1->id,'meeting_id'=>$meeting->id);
     	$meeting->set_relationship($meeting->rel_contacts_table, $relate_values, true, true, $data_values);

        $relate_values = array('contact_id'=>$contact2->id,'meeting_id'=>$meeting->id);
     	$meeting->set_relationship($meeting->rel_contacts_table, $relate_values, true, true, $data_values);

        $relate_values = array('contact_id'=>$contact3->id,'meeting_id'=>$meeting->id);
     	$meeting->set_relationship($meeting->rel_contacts_table, $relate_values, true, true, $data_values);

        $call = SugarTestCallUtilities::createCall();
        Bug49385Test::$call_id = $call->id;
        $call->name = 'Bug49385Test';
        $call->save();

        $relate_values = array('contact_id'=>$contact1->id,'call_id'=>$call->id);
     	$call->set_relationship($call->rel_contacts_table, $relate_values, true, true, $data_values);

        $relate_values = array('contact_id'=>$contact2->id,'call_id'=>$call->id);
     	$call->set_relationship($call->rel_contacts_table, $relate_values, true, true, $data_values);

        $relate_values = array('contact_id'=>$contact3->id,'call_id'=>$call->id);
     	$call->set_relationship($call->rel_contacts_table, $relate_values, true, true, $data_values);

    }

    static public function tearDownAfterClass()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestCallUtilities::removeAllCreatedCalls();
        $GLOBALS['db']->query("DELETE FROM meetings_contacts WHERE meeting_id = '" . Bug49385Test::$meeting_id . "'");
        $GLOBALS['db']->query("DELETE FROM calls_contacts WHERE call_id = '" . Bug49385Test::$call_id . "'");
        unset($GLOBALS['current_user']);
    }


    /**
     * testMeetingsController
     *
     * This tests the controller code added to make sure we get contacts displayed for the popup window
     */
    public function testMeetingsController()
    {
        require_once('modules/Meetings/controller.php');
        $controller = new MeetingsController();
        $_REQUEST['bean_id'] = Bug49385Test::$meeting_id;
        $_REQUEST['related_id'] = Bug49385Test::$contact_id;
        $controller->action_DisplayInline();
        $this->expectOutputRegex('/SugarContact/');
    }


    /**
     * testCallsController
     *
     * This tests the controller code to make sure we get contacts displayed for the popup window
     */
    public function testCallsController()
    {
        require_once('modules/Calls/controller.php');
        $controller = new CallsController();
        $_REQUEST['bean_id'] = Bug49385Test::$call_id;
        $_REQUEST['related_id'] = Bug49385Test::$contact_id;
        $controller->action_DisplayInline();
        $this->expectOutputRegex('/SugarContact/');
    }


    /**
     * testMeetingsCreateNewListQuery
     *
     * This tests the create_new_list_query function of Meetings module
     */
    public function testMeetingsCreateNewListQuery()
    {
        $meeting = new Meeting();
        $order_by = 'name ASC';
        $where = "(meetings.name like 'Bug49385Test%')";
        $filter = array
        (
            'set_complete' => 1,
            'status' => 1,
            'join_meeting' => 1,
            'join_url' => 1,
            'host_url' => 1,
            'name' => 1,
            'contact_name' => 1,
            'first_name' => 1,
            'last_name' => 1,
            'parent_name' => 1,
            'parent_id' => 1,
            'parent_type' => 1,
            'date_start' => 1,
            'time_start' => 1,
            'assigned_user_name' => 1,
            'date_entered' => 1,
            'favorites_only' => 1
        );

        $params = array(
            'massupdate' => 1,
            'favorties' => 1
        );

        $ret_array = $meeting->create_new_list_query($order_by, $where, $filter, $params, 0, '', true, $meeting);
        $ret_array['inner_join'] = '';
        if (!empty($meeting->listview_inner_join)) {
            $ret_array['inner_join'] = ' ' . implode(' ', $meeting->listview_inner_join) . ' ';
        }
        $sql =  $ret_array['select'] . $ret_array['from'] . $ret_array['inner_join'] . $ret_array['where'] . $ret_array['order_by'];
        $sql = $ret_array['select'] . $ret_array['from'] . $ret_array['where'] . $ret_array['order_by'];

        $result = $GLOBALS['db']->query($sql);
        $count = 0;
        while($row = $GLOBALS['db']->fetchByAssoc($result))
        {
            $count++;
        }

        $this->assertEquals(1, $count, 'Assert that the query returned 1 rows');

        $params = array(
            'massupdate' => 1,
            'favorties' => 1,
            'collection_list' => array()
        );

        $meeting = new Meeting();
        $ret_array = $meeting->create_new_list_query($order_by, $where, $filter, $params, 0, '', true, $meeting);
        $ret_array['inner_join'] = '';
        if (!empty($meeting->listview_inner_join)) {
            $ret_array['inner_join'] = ' ' . implode(' ', $meeting->listview_inner_join) . ' ';
        }
        $sql =  $ret_array['select'] . $ret_array['from'] . $ret_array['inner_join'] . $ret_array['where'] . $ret_array['order_by'];

        $result = $GLOBALS['db']->query($sql);
        $count = 0;
        while($row = $GLOBALS['db']->fetchByAssoc($result))
        {
            $count++;
        }

        $this->assertEquals(3, $count, 'Assert that the query returned 3 rows');
    }


    /**
      * testCallsCreateNewListQuery
     *
      * This tests the create_new_list_query function of Calls module
      */
     public function testCallsCreateNewListQuery()
     {
         $call = new Call();
         $order_by = 'name ASC';
         $where = "(calls.name like 'Bug49385Test%')";
         $filter = array
         (
             'set_complete' => 1,
             'status' => 1,
             'join_call' => 1,
             'join_url' => 1,
             'host_url' => 1,
             'name' => 1,
             'contact_name' => 1,
             'first_name' => 1,
             'last_name' => 1,
             'parent_name' => 1,
             'parent_id' => 1,
             'parent_type' => 1,
             'date_start' => 1,
             'time_start' => 1,
             'assigned_user_name' => 1,
             'date_entered' => 1,
             'favorites_only' => 1
         );

         $params = array(
             'massupdate' => 1,
             'favorties' => 1
         );

         $ret_array = $call->create_new_list_query($order_by, $where, $filter, $params, 0, '', true, $call);
         $ret_array['inner_join'] = '';
         if (!empty($call->listview_inner_join)) {
             $ret_array['inner_join'] = ' ' . implode(' ', $call->listview_inner_join) . ' ';
         }
         $sql =  $ret_array['select'] . $ret_array['from'] . $ret_array['inner_join'] . $ret_array['where'] . $ret_array['order_by'];

         $result = $GLOBALS['db']->query($sql);
         $count = 0;
         while($row = $GLOBALS['db']->fetchByAssoc($result))
         {
             $count++;
         }

         $this->assertEquals(1, $count, 'Assert that the query returned 1 row');

         $params = array(
             'massupdate' => 1,
             'favorties' => 1,
             'collection_list' => array()
         );

         $call = new Call();
         $ret_array = $call->create_new_list_query($order_by, $where, $filter, $params, 0, '', true, $call);
         $ret_array['inner_join'] = '';
         if (!empty($call->listview_inner_join)) {
             $ret_array['inner_join'] = ' ' . implode(' ', $call->listview_inner_join) . ' ';
         }
         $sql =  $ret_array['select'] . $ret_array['from'] . $ret_array['inner_join'] . $ret_array['where'] . $ret_array['order_by'];

         $result = $GLOBALS['db']->query($sql);
         $count = 0;
         while($row = $GLOBALS['db']->fetchByAssoc($result))
         {
             $count++;
         }

         $this->assertEquals(3, $count, 'Assert that the query returned 3 rows');
     }


}

class Bug49385SugarBeanMock extends SugarBean
{
    public function create_many_to_many_query($ret_array=array())
    {
        return parent::create_many_to_many_query($ret_array);
    }
}

?>