<?php

require_once "modules/Leads/Lead.php";
require_once "include/Popups/PopupSmarty.php";

class Bug43452Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']); 
    }
    
    public function tearDown()
    {
        unset($GLOBALS['app_strings']);
    }
    
    /**
     * @ticket 43452
     */
    public function testGenerateSearchWhereWithUnsetBool()
    {
        // Looking for a NON Converted Lead named "Fabio".
        // Without changes, PopupSmarty return a bad query, with AND and OR at the same level.
        // With this fix we get parenthesis:
        //     1) From SearchForm2->generateSearchWhere, in case of 'bool' (they surround "converted = '0' or converted IS NULL")
        //     2) From PopupSmarty->_get_where_clause, when items of where's array are imploded.

        $tGoodWhere = "( leads.first_name like 'Fabio%' and ( leads.converted = '0' OR leads.converted IS NULL ) )";

        $_searchFields['Leads'] = array ('first_name'=> array('value' => 'Fabio', 'query_type'=>'default'),
                                         'converted'=> array('value' => '0', 'query_type'=>'default'),
                                        );
        // provides $searchdefs['Leads']
        require "modules/Leads/metadata/searchdefs.php";
        
        $bean = $this->getMock('Lead');
        $popup = new PopupSmarty($bean, "Leads");
        $popup->searchForm->searchdefs =  $searchdefs['Leads'];
        $popup->searchForm->searchFields = $_searchFields['Leads'];
        $tWhere = $popup->_get_where_clause();

        $this->assertEquals($tGoodWhere, $tWhere);
    }
}
