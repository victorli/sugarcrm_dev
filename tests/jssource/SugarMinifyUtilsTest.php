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
require_once 'jssource/minify_utils.php';

class SugarMinifyUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * The file that is built by this process
     * 
     * @var string
     */
    protected $builtFile = 'cache/include/javascript/unit_test_built.min.js';

    public function setup()
    {
        $obj = new SugarMinifyUtilsForTesting;
        $obj->ConcatenateFiles('tests');
    }

    public function tearDown()
    {
        @unlink($this->builtFile);
    }

    public function testConcatenateFiles()
    {
        // Test the file was created
        $this->assertFileExists($this->builtFile);
        
        // Test the contents of the file. Using contains instead of equals so
        // systems without JSMin won't fail hard
        $content = file_get_contents($this->builtFile);
        $expect1 = file_get_contents('tests/jssource/minify/expect/var.js');
        $expect2 = file_get_contents('tests/jssource/minify/expect/if.js');
        $this->assertContains($expect1, $content);
        $this->assertContains($expect2, $content);
    }
}

class SugarMinifyUtilsForTesting extends SugarMinifyUtils
{
    protected function getJSGroupings()
    {
        return array(
            array(
                'jssource/minify/test/var.js' => 'include/javascript/unit_test_built.min.js',
                'jssource/minify/test/if.js' => 'include/javascript/unit_test_built.min.js',
            ),
        );
    }
}

