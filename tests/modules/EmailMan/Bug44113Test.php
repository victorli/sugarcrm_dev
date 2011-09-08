<?php 

require_once('modules/Configurator/Configurator.php');
require_once('modules/EmailMan/EmailMan.php');

/***
 * Test cases for Bug 44113
 */
class Bug44113Test extends Sugar_PHPUnit_Framework_TestCase
{
	private $cfg;   // configurator
	private $emailMan;
    private $email_xss; // the security settings to be saved in config_ovverride
    
	public function setUp()
	{
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '1';

          // email_xss settings to be saved using config_override
        $this->email_xss = array(
            'applet' => 'applet',
            'form' => 'form',
            'iframe' => 'iframe',
            'script' => 'script'
            );

	}
	
	public function tearDown()
	{
		unset($this->cfg);
        unset($this->emailMan);
        unset($this->email_xss);
        unset($GLOBALS['current_user']);

	}

    public function testEmailManController()
    {


      require_once('modules/EmailMan/controller.php');
      require_once('include/MVC/Controller/SugarController.php');

      global $sugar_config;
      $conn = new EmailManController();

        // populate the REQUEST array because configurator will read that to write config_override 
      foreach ($this->email_xss as $key=>$val) {
           $_REQUEST["$key"] = $val;
      }

      $new_security_settings = base64_encode(serialize($this->email_xss));



      // make sure that settings from config.php are untouched
      require("config.php");
      $original_security_settings = $sugar_config['email_xss'];
      $this->assertNotEquals($original_security_settings, $new_security_settings,
                            "ensure that original email_xss is not touched");

       $conn->action_Save();   // testing the save,
                              // it should use the above request vars
                              // to create a new config_override.php 

      // now check to make sure that config_override received the updated settings
      require("config_override.php");
      $this->assertEquals($new_security_settings, $sugar_config['email_xss'],
                          "testing that new email_xss settings got saved");

   }



    /**
     * make sure that new configs are saved using handleOverride
     */
	public function testSavingToConfigOverride()
	{
        $this->cfg = new Configurator();
        global $sugar_config;

       $new_security_settings = base64_encode(serialize($this->email_xss));

       $this->cfg->config['email_xss'] = $new_security_settings;
       $this->cfg->handleOverride();

       // just test to make sure that configuration is saved
       $this->assertEquals($sugar_config['email_xss'], $new_security_settings,
                         "testing configurator");


    }

}

?>