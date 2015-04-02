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
 
require_once("modules/Accounts/Account.php");

class Bug34993Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $_tablename;
    private $_old_installing;

    public function setUp()
    {
        $this->accountMockBean = $this->getMock('Account' , array('hasCustomFields'));
        $this->_tablename = 'test' . date("YmdHis");
        if ( isset($GLOBALS['installing']) )
        {
            $this->_old_installing = $GLOBALS['installing'];
        }
        $GLOBALS['installing'] = true;

        $GLOBALS['db']->createTableParams($this->_tablename . '_cstm',
            array(
                'id_c' => array (
                    'name' => 'id_c',
                    'type' => 'id',
                    ),                 
                ),
            array()
            );
        $GLOBALS['db']->query("INSERT INTO {$this->_tablename}_cstm (id_c) VALUES ('12345')");
        
        //Safety check in case the previous run had failed
        $GLOBALS['db']->query("DELETE FROM fields_meta_data WHERE id in ('Accountsbug34993_test_c', 'Accountsbug34993_test2_c')");
    }

    public function tearDown()
    {
        $GLOBALS['db']->dropTableName($this->_tablename . '_cstm');
        $GLOBALS['db']->query("DELETE FROM fields_meta_data WHERE id in ('Accountsbug34993_test_c', 'Accountsbug34993_test2_c')");
        if ( isset($this->_old_installing) ) {
            $GLOBALS['installing'] = $this->_old_installing;
        } else {
            unset($GLOBALS['installing']);
        }
        
        if(file_exists('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_bug34993_test_c.php'))
        {
           unlink('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_bug34993_test_c.php');
        }
        
        if(file_exists('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_bug34993_test2_c.php'))
        {
           unlink('custom/Extension/modules/Accounts/Ext/Vardefs/sugarfield_bug34993_test2_c.php');
        }
        
        VardefManager::clearVardef('Accounts', 'Account');
        VardefManager::refreshVardefs('Accounts', 'Account');
    }

    public function testCustomFieldDefaultValue()
    {
    	require_once('modules/DynamicFields/templates/Fields/TemplateText.php');
    	require_once('modules/DynamicFields/DynamicField.php');
    	require_once('modules/DynamicFields/FieldCases.php');
    	
    	//Simulate create a custom text field with a default value set to 123
    	$templateText = get_widget('varchar');
    	$templateText->type = 'varchar';
    	$templateText->view = 'edit';
    	$templateText->label = 'CUSTOM TEST';
    	$templateText->name = 'bug34993_test';
    	$templateText->size = 20;
    	$templateText->len = 255;
    	$templateText->required = false;
    	$templateText->default = '123';
    	$templateText->default_value = '123';
    	$templateText->comment = '';
    	$templateText->audited = 0;
    	$templateText->massupdate = 0;
    	$templateText->importable = true;
    	$templateText->duplicate_merge = 0;
    	$templateText->reportable = 1;
        $templateText->ext1 = NULL;
        $templateText->ext2 = NULL;
        $templateText->ext3 = NULL;
        $templateText->ext4 = NULL;    	
    	
        $bean = $this->accountMockBean;
        $bean->custom_fields = new DynamicField($bean->module_dir);
        $bean->custom_fields->setup($bean);
   
        $bean->expects($this->any())
             ->method('hasCustomFields')
             ->will($this->returnValue(true));
        $bean->table_name = $this->_tablename;
        $bean->id = '12345';
        $bean->custom_fields->addFieldObject($templateText);        
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->id_c, '12345', "Assert that the custom table exists");
        $this->assertEquals($bean->bug34993_test_c, NULL, "Assert that the custom text field has a default value set to NULL");
        
        
    	//Simulate create a custom text field with a default value set to 123
    	$templateText = get_widget('enum');
    	$templateText->type = 'enum';
    	$templateText->view = 'edit';
    	$templateText->label = 'CUSTOM TEST2';
    	$templateText->name = 'bug34993_test2';
    	$templateText->size = 20;
    	$templateText->len = 255;
    	$templateText->required = false;
    	$templateText->default = '123';
    	$templateText->default_value = '123';
    	$templateText->comment = '';
    	$templateText->audited = 0;
    	$templateText->massupdate = 0;
    	$templateText->importable = true;
    	$templateText->duplicate_merge = 0;
    	$templateText->reportable = 1;
        $templateText->ext1 = 'account_type_dom';
        $templateText->ext2 = NULL;
        $templateText->ext3 = NULL;
        $templateText->ext4 = NULL;    	
    	
        $bean = $this->accountMockBean;
        $bean->custom_fields = new DynamicField($bean->module_dir);
        $bean->custom_fields->setup($bean);
   
        $bean->expects($this->any())
             ->method('hasCustomFields')
             ->will($this->returnValue(true));
        $bean->table_name = $this->_tablename;
        $bean->id = '12345';
        $bean->custom_fields->addFieldObject($templateText);        
        $bean->custom_fields->retrieve();
        $this->assertEquals($bean->id_c, '12345', "Assert that the custom table exists");
        $this->assertEquals($bean->bug34993_test2_c, NULL, "Assert that the custom enum field has a default value set to NULL");        
    }
}
