<?php
require_once "tests/upgrade/UpgradeTestCase.php";

/**
 * Test for SugarUpgradeFixSugarPDF
 */
class UpgradeFixPdfTest extends UpgradeTestCase
{

    public function testFixSugarPdf()
    {
        SugarTestHelper::saveFile("custom/include/Sugarpdf/sugarpdf_default.php");
        mkdir_recursive("custom/include/Sugarpdf");
        copy("tests/upgrade/sugarpdf_default.php", "custom/include/Sugarpdf/sugarpdf_default.php");
        $script = $this->upgrader->getScript("post", "3_FixSugarPDF");
        $script->run();
        require "custom/include/Sugarpdf/sugarpdf_default.php";
        $this->assertEquals("vendor/tcpdf/", $sugarpdf_default['K_PATH_MAIN'], "K_PATH_MAIN is wrong");
        $this->assertEquals("vendor/tcpdf/fonts/", $sugarpdf_default['K_PATH_FONTS'], "K_PATH_FONTS is wrong");
        $this->assertEquals("customized/include/tcpdf/", $sugarpdf_default['K_PATH_URL'], "K_PATH_URL is wrong");
        $this->assertEquals("custom/include/tcpdf/fonts/", $sugarpdf_default['K_PATH_CUSTOM_FONTS'], "K_PATH_CUSTOM_FONTS is wrong");
    }

    public function testFixSugarPdfUnchanged()
    {
        SugarTestHelper::saveFile("custom/include/Sugarpdf/sugarpdf_default.php");
        mkdir_recursive("custom/include/Sugarpdf");
        copy("include/Sugarpdf/sugarpdf_default.php", "custom/include/Sugarpdf/sugarpdf_default.php");
        $script = $this->upgrader->getScript("post", "3_FixSugarPDF");
        $script->run();
        $this->assertFileEquals("include/Sugarpdf/sugarpdf_default.php", "custom/include/Sugarpdf/sugarpdf_default.php", "File should not be changed");
    }

    public function testFixSugarPdfNone()
    {
        SugarTestHelper::saveFile("custom/include/Sugarpdf/sugarpdf_default.php");
        if(file_exists("custom/include/Sugarpdf/sugarpdf_default.php")) {
            unlink("custom/include/Sugarpdf/sugarpdf_default.php");
        }
        $script = $this->upgrader->getScript("post", "3_FixSugarPDF");
        $script->run();
        $this->assertFileNotExists("custom/include/Sugarpdf/sugarpdf_default.php");
    }

}