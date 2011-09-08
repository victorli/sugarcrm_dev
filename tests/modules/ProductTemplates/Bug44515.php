<?php

class Bug44515 extends Sugar_PHPUnit_Framework_TestCase
{
   
    /**
     * @group Bug44515
     */
    var $customDir = "custom/modules/ProductTemplates/formulas";

    public function setUp()
    {
        
        if (!is_dir($this->customDir))
          mkdir($this->customDir, 0700, TRUE); // Creating nested directories at a glance

        file_put_contents($this->customDir . "/customformula1.php", "<?php\nclass Customformula1 {\n}\n?>");
        file_put_contents($this->customDir . "/customformula2.php", "<?php\nclass Customformula2 {\n}\n?>");
    }


    public function tearDown()
    {
        unset($GLOBALS['price_formulas']['Customformula1']);
        unset($GLOBALS['price_formulas']['Customformula2']);
        unlink($this->customDir . "/customformula1.php");
        unlink($this->customDir . "/customformula2.php");
        rmdir($this->customDir);
    }

    public function testLoadCustomFormulas()
    {
      require_once "modules/ProductTemplates/Formulas.php";

      // At this point I expect to have 7 formulas (5 standard and 2 custom).
      $expectedIndexes = 7;
      $this->assertEquals($expectedIndexes, count($GLOBALS['price_formulas']));

      // Check if standard formulas are still in the array
      $this->assertArrayHasKey("Fixed", $GLOBALS['price_formulas']);
      $this->assertArrayHasKey("ProfitMargin", $GLOBALS['price_formulas']);
      $this->assertArrayHasKey("PercentageMarkup", $GLOBALS['price_formulas']);
      $this->assertArrayHasKey("PercentageDiscount", $GLOBALS['price_formulas']);
      $this->assertArrayHasKey("IsList", $GLOBALS['price_formulas']);
      // Check if custom formulas are in the array
      $this->assertArrayHasKey("Customformula1", $GLOBALS['price_formulas']);
      $this->assertArrayHasKey("Customformula2", $GLOBALS['price_formulas']);

      // Check if CustomFormula1 point to the right file (/custom/modules/ProductTemplates/formulas/customformula1.php)
      $_customFormula1FileName = "custom/modules/ProductTemplates/formulas/customformula1.php";
      $this->assertEquals($_customFormula1FileName, $GLOBALS['price_formulas']['Customformula1']);
    }
}

