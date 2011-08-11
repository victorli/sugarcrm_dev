<?php
require_once('include/SugarFields/Fields/Relate/SugarFieldRelate.php');

class Bug43770Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_fieldOutput;

    public function setUp()
    {
        $sfr = new SugarFieldRelate('relate');
        $vardef = array(
            'name' => 'assigned_user_name',
            'id_name' => 'assigned_user_id',
            'module' => 'Users'
        );
        $displayParams = array(
            'idName' => 'Contactsassigned_user_name'
        );
        $this->_fieldOutput = $sfr->getEditViewSmarty(array(), $vardef, $displayParams, 1);
    }
    /**
     * @group	bug43770
     */
    public function testCustomIdName()
    {
        $this->assertContains('id="Contacts{$Array.assigned_user_name.id_name}"', $this->_fieldOutput);
    }

    public function testCustomIdNameJS()
    {
        $this->assertContains('"id":"Contactsassigned_user_id"', $this->_fieldOutput);
    }
}