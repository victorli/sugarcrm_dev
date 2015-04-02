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

require_once 'include/SugarObjects/SugarConfig.php';
require_once 'include/SugarObjects/VardefManager.php';

/**
 * @group bug60047
 */
class Bug60047Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $reloadVardefs;
    protected static $inDeveloperMode;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        
        self::$reloadVardefs = isset($GLOBALS['reload_vardefs']) ? $GLOBALS['reload_vardefs'] : null;
        self::$inDeveloperMode = isset($_SESSION['developerMode']) ? $_SESSION['developerMode'] : false;
        
        // Force a vardef refresh forcefully because some tests in the suite 
        // actively destroy some globals. This will have an effect on getBean
        // for the duration of this test
        $GLOBALS['reload_vardefs'] = true;
        $_SESSION['developerMode'] = true;
    }
    
    public static function tearDownAfterClass()
    {
        if (self::$reloadVardefs) {
            $GLOBALS['reload_vardefs'] = self::$reloadVardefs;
        }
        
        if (self::$inDeveloperMode) {
            $_SESSION['developerMode'] = self::$inDeveloperMode;
        }
    }

    public function testForecastBean()
    {
        VardefManager::loadVardef("Forecasts", 'Forecast', true);
        $this->assertArrayHasKey("acls", $GLOBALS['dictionary']['Forecast']);
        $this->assertArrayHasKey("SugarACLStatic", $GLOBALS['dictionary']['Forecast']['acls']);
    }

    public function get_beans()
    {
        return array(
            array('ForecastOpportunities'),
        );
    }

    /**
     * @dataProvider get_beans
     */
    public function testForecastSubordinateBean($module)
    {
        // drop forecasting vardefs
        foreach(glob("cache/modules/Forecasts/*vardefs.php") as $file) {
            @unlink($file);
        }
        $bean = BeanFactory::getBean($module);
        $this->assertNotEmpty($bean);
        $this->assertTrue(empty($GLOBALS['dictionary'][$bean->object_name]['acls']));
    }
}
