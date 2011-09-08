<?php
require_once 'modules/Accounts/Account.php';
require_once 'modules/Meetings/Meeting.php';
require_once 'include/SearchForm/SearchForm2.php';


class Bug45287_2Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $meetingsArr;
    var $searchDefs;
    var $searchFields;
    
    public function setup()
    {
        global $current_user, $timedate;
        // Create Anon User setted on PDT TimeZone
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->setPreference('datef', "d/m/Y");
        $current_user->setPreference('timef', "H:i:s");
        $current_user->setPreference('timezone', "America/Los_Angeles");

        // new object to avoid TZ caching
        $timedate = new TimeDate();

        $this->meetingsArr = array();

        // Create a Bunch of Meetings
        $d = 12;
        $cnt = 0;
        while ($d < 15)
        {
          $this->meetingsArr[$cnt] = new Meeting();
          $this->meetingsArr[$cnt]->name = 'Bug45287 Meeting ' . ($cnt + 1);
          $this->meetingsArr[$cnt]->date_start = $timedate->to_display_date_time(gmdate("Y-m-d H:i:s", mktime(10+$cnt, 30, 00, 7, $d, 2011)));
          $this->meetingsArr[$cnt]->save();
          $d++;
          $cnt++;
        }

        $this->searchDefs = array("Meetings" => array("layout" => array("basic_search" => array("name" => array("name" => "name",
                                                                                                                "default" => true,
                                                                                                                "width" => "10%",
                                                                                                               ),
                                                                                                "date_start" => array("name" => "date_start",
                                                                                                                      "default" => true,
                                                                                                                      "width" => "10%",
                                                                                                                      "type" => "datetimecombo",
                                                                                                                     ), 
                                                                                               ),
                                                                       ),
                                                     ),
                                 );

        $this->searchFields = array("Meetings" => array("name" => array("query_type" => "default"),
                                                        "date_start" => array("query_type" => "default"),
                                                        "range_date_start" => array("query_type" => "default",
                                                                                    "enable_range_search" => 1,
                                                                                    "is_date_field" => 1),
                                                        "range_date_start" => array("query_type" => "default",
                                                                                    "enable_range_search" => 1,
                                                                                    "is_date_field" => 1),
                                                        "start_range_date_start" => array("query_type" => "default",
                                                                                          "enable_range_search" => 1,
                                                                                          "is_date_field" => 1),
                                                        "end_range_date_start" => array("query_type" => "default",
                                                                                        "enable_range_search" => 1,
                                                                                        "is_date_field" => 1),
                                                       ),
                                   );
    }
    
    public function tearDown()
    {

        foreach ($this->meetingsArr as $m)
        {
            $GLOBALS['db']->query('DELETE FROM meetings WHERE id = \'' . $m->id . '\' ');
        }

        unset($m);
        unset($this->meetingsArr);
        unset($this->searchDefs);
        unset($this->searchFields);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
	
    
    public function testRetrieveByExactDate()
    {
        global $current_user, $timedate;

        $_REQUEST = $_POST = array("module" => "Meetings",
                                   "action" => "index",
                                   "searchFormTab" => "basic_search",
                                   "query" => "true",
                                   "name_basic" => "",
                                   "current_user_only_basic" => "0",
                                   "favorites_only_basic" => "0",
                                   "open_only_basic" => "0",
                                   "date_start_basic_range_choice" => "=",
                                   "range_date_start_basic" => "14/07/2011",
                                   "start_range_date_start_basic" => "",
                                   "end_range_date_start_basic" => "", 
                                   "button" => "Search",
                                  );

        $srch = new SearchForm(new Meeting(), "Meetings");
        $srch->setup($this->searchDefs, $this->searchFields, "");
        $srch->populateFromRequest();
        $w = $srch->generateSearchWhere();

        // Due to daylight savings, I cannot hardcode intervals...
        $GMTDates = $timedate->getDayStartEndGMT("2011-07-14");

        // Current User is on GMT-7.
        // Asking for meeting of 14 July 2011, I expect to search (GMT) from 14 July at 07:00 until 15 July at 07:00 (excluded)
        $expectedWhere = "meetings.date_start >= '" . $GMTDates['start'] . "' AND meetings.date_start <= '" . $GMTDates['end'] . "'";
        $this->assertEquals($w[0], $expectedWhere);
    }
	

    public function testRetrieveByDaterange()
    {
        global $current_user, $timedate;

        $_REQUEST = $_POST = array("module" => "Meetings",
                                   "action" => "index",
                                   "searchFormTab" => "basic_search",
                                   "query" => "true",
                                   "name_basic" => "",
                                   "current_user_only_basic" => "0",
                                   "favorites_only_basic" => "0",
                                   "open_only_basic" => "0",
                                   "date_start_basic_range_choice" => "between",
                                   "range_date_start_basic" => "",
                                   "start_range_date_start_basic" => "13/07/2011",
                                   "end_range_date_start_basic" => "14/07/2011", 
                                   "button" => "Search",
                                  );


        $srch = new SearchForm(new Meeting(), "Meetings");
        $srch->setup($this->searchDefs, $this->searchFields, "");
        $srch->populateFromRequest();
        $w = $srch->generateSearchWhere();

        // Due to daylight savings, I cannot hardcode intervals...
        $GMTDatesStart = $timedate->getDayStartEndGMT("2011-07-13");
        $GMTDatesEnd = $timedate->getDayStartEndGMT("2011-07-14");
 
        // Current User is on GMT-7.
        // Asking for meeting between 13 and 14 July 2011, I expect to search from 13 July at 07:00 until 15 July at 07:00 (excluded)
        $expectedWhere = "meetings.date_start >= '" . $GMTDatesStart['start'] . "' AND meetings.date_start <= '" . $GMTDatesEnd['end'] . "'";
        $this->assertEquals($w[0], $expectedWhere);
   }
	

}
