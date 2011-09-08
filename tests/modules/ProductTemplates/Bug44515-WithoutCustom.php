<?php

class Bug44515 extends Sugar_PHPUnit_Framework_TestCase
{
   
    /**
     * @group Bug44515
     */
    public function setUp()
    {
        
    }


    public function tearDown()
    {
    }

    public function testLoadCustomFormulas()
    {
      require_once "modules/ProductTemplates/Formulas.php";

      // At this point I expect to have only the 5 standard formulas
      $expectedIndexes = 5;
      $this->assertEquals($expectedIndexes, count($GLOBALS['price_formulas']));
    }
}

