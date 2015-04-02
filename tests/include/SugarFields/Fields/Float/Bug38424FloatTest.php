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