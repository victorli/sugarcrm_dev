<?php
require_once 'include/FileLocator/FileLocator.php';
class FileLocatorTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_locator;

    public function setUp()
    {
        $this->_locator = new FileLocator(array(dirname(__FILE__)));
    }

    public function testConstructor()
    {
        $this->assertEquals($this->_locator->getPaths(), array(dirname(__FILE__)));
    }

    public function testLocate()
    {
        $this->assertEquals($this->_locator->locate(basename(__FILE__)), __FILE__);
    }
}
