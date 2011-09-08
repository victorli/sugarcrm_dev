<?php
require_once('include/SugarFields/Fields/Float/SugarFieldFloat.php');

class Bug38424FloatTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_fieldOutput;

    public function setUp()
    {
        $sfr = new SugarFieldFloat('float');
        $vardef = array(
            'len' => '10',
        );
        $this->_fieldOutput = $sfr->getEditViewSmarty(array(), $vardef, array(), 1);
    }

    
    public function testMaxLength()
    {
        $this->assertContains('maxlength=\'10\'', $this->_fieldOutput);
    }
}