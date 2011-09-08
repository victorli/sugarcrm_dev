<?php

require_once 'PHPUnit/Extensions/OutputTestCase.php';

class Bugs39819_39820 extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @ticket 39819
     * @ticket 39820
     */
    public function setUp()
    {
        if (!is_dir("custom/modules/Accounts/language")) {
            mkdir("custom/modules/Accounts/language", 0700, TRUE); // Creating nested directories at a glance
        }
    }
    
    public function testLoadEnHelp()
    {
        // en_us help on a standard module.
        file_put_contents("modules/Accounts/language/en_us.help.DetailView.html", "<h1>ENBugs39819-39820</h1>");
        
        $_SERVER['HTTP_HOST'] = "";
        $_SERVER['SCRIPT_NAME'] = "";
        $_SERVER['QUERY_STRING'] = "";

        $_REQUEST['view'] = 'documentation';
        $_REQUEST['lang'] = 'en_us';
        $_REQUEST['help_module'] = 'Accounts';
        $_REQUEST['help_action'] = 'DetailView';

        ob_start();
        require "modules/Administration/SupportPortal.php";

        $tStr = ob_get_contents();
        ob_clean();
        
        unlink("modules/Accounts/language/en_us.help.DetailView.html");
        
        // I expect to get the en_us normal help file....
        $this->assertRegExp("/.*ENBugs39819\-39820.*/", $tStr);
    }
    
    public function testLoadCustomItHelp()
    {
        // Custom help (NOT en_us) on a standard module.
        file_put_contents("custom/modules/Accounts/language/it_it.help.DetailView.html", "<h1>Bugs39819-39820</h1>");

        $_SERVER['HTTP_HOST'] = "";
        $_SERVER['SCRIPT_NAME'] = "";
        $_SERVER['QUERY_STRING'] = "";

        $_REQUEST['view'] = 'documentation';
        $_REQUEST['lang'] = 'it_it';
        $_REQUEST['help_module'] = 'Accounts';
        $_REQUEST['help_action'] = 'DetailView';
        
        ob_start();
        require "modules/Administration/SupportPortal.php";

        $tStr = ob_get_contents();
        ob_clean();

        unlink("custom/modules/Accounts/language/it_it.help.DetailView.html");
        
        // I expect to get the it_it custom help....
        $this->assertRegExp("/.*Bugs39819\-39820.*/", $tStr);
    }
}
