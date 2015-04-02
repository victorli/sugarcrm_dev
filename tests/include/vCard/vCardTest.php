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

require_once 'include/vCard.php';

class vCardTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $GLOBALS['beanList']['vCardMockModule'] = 'vCardMockModule';
        $GLOBALS['beanFiles']['vCardMockModule'] = 'tests/include/vCard/vCardTest.php';
        global $app_strings, $app_list_strings, $current_language;
        $app_strings = return_application_language($current_language);
        $app_list_strings = return_app_list_strings_language($current_language);
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    // data providers

    public function vCardsWithSalutations()
    {
        return array(
            array('MAR-1510a.vcf'),
            array('MAR-1510b.vcf'),
        );
    }

    // test cases

    /**
     * Check if exception is thrown when required fields are not present
     *
     * @ticket 60613
     * @dataProvider vCardsWithoutRequiredFields
     * @expectedException SugarException
     */
    public function testImportVCardWithoutRequiredFields($filename, $module)
    {
        $filename  = dirname(__FILE__)."/vcf/$filename";

        $vcard = new vCard();
        $vcard->importVCard($filename, $module);
    }

    public static function vCardsWithoutRequiredFields()
    {
        return array(
            array(
                'VCardWithoutAllRequired.vcf', // vCard without last_name
                'Contacts'
            ),
            array(
                'VCardEmpty.vcf', // Empty vCard
                'Leads'
            ),
        );
    }

    /**
     * @ticket 10419
     */
	public function testImportedVcardWithDifferentCharsetIsTranslatedToTheDefaultCharset()
    {
        $filename  = dirname(__FILE__)."/vcf/ISO88591SampleFile.vcf";
        $module = "vCardMockModule";

        $vcard = new vCard();
        $record = $vcard->importVCard($filename,$module);

        $bean = new vCardMockModule;
        $bean = $bean->retrieve($record);

        $this->assertEquals('Hans Müster',$bean->first_name.' '.$bean->last_name);
    }

    public function testImportedVcardWithSameCharsetIsNotTranslated()
    {
        $filename  = dirname(__FILE__)."/vcf/UTF8SampleFile.vcf";
        $module = "vCardMockModule";

        $vcard = new vCard();
        $record = $vcard->importVCard($filename,$module);

        $bean = new vCardMockModule;
        $bean = $bean->retrieve($record);

        $this->assertEquals('Hans Müster',$bean->first_name.' '.$bean->last_name);
    }

    /**
     * @dataProvider vCardsWithSalutations
     */
    public function testImportVcard_NameIncludesSalutation_PersonIsCreatedWithFirstNameAndLastNameAndSalutation($vcard)
    {
        $filename = dirname(__FILE__) . "/vcf/{$vcard}";
        $module = 'vCardMockModule';
        $vcard = new vCard();
        $record = $vcard->importVCard($filename, $module);
        $bean = new vCardMockModule();
        $bean = $bean->retrieve($record);
        $this->assertNotEmpty($bean->first_name, 'The first name should have been parsed from the vcard');
        $this->assertNotEmpty($bean->last_name, 'The last name should have been parsed from the vcard');
        $this->assertNotEmpty($bean->salutation, 'The salutation should have been parsed from the vcard');
    }

    public function vCardNames()
    {
        return array(
            array('', "Last Name"),
            array('First Name', "Last Name"),
            array("Иван", "Č, Ć ŐŐŐ Lastname"),
        );
    }

    /**
     * @ticket 24487
	 * @dataProvider vCardNames
     */
    public function testExportVcard($fname, $lname)
    {
        $vcard = new vCard();

        $data = new vCardMockModule();
        $data->first_name = $fname;
        $data->last_name = $lname;
        $GLOBALS['current_user']->setPreference('default_export_charset', 'UTF-8');
        $id = $data->save();

        $vcard->loadContact($id, 'vCardMockModule');
        $cardtext = $vcard->toString();

        $this->assertContains("N;CHARSET=utf-8:$lname;$fname", $cardtext, "Cannot find N name", true);
        $this->assertContains("FN;CHARSET=utf-8: $fname $lname", $cardtext, "Cannot find FN name", true);
    }
    
    public function testClear()
    {
        $vcard = new vCard();
        $vcard->setProperty('dog','cat');
        $vcard->clear();
        
        $this->assertNull($vcard->getProperty('dog'));
    }
    
    public function testSetProperty()
    {
        $vcard = new vCard();
        $vcard->setProperty('dog','cat');
        
        $this->assertEquals('cat',$vcard->getProperty('dog'));
    }
    
    public function testGetPropertyThatDoesNotExist()
    {
        $vcard = new vCard();
        
        $this->assertNull($vcard->getProperty('dog'));
    }
    
    public function testSetTitle()
    {
        $vcard = new vCard();
        $vcard->setTitle('cat');
        
        $this->assertEquals('cat',$vcard->getProperty('TITLE'));
    }
    
    public function testSetORG()
    {
        $vcard = new vCard();
        $vcard->setORG('foo','bar');
        
        $this->assertEquals('foo;bar',$vcard->getProperty('ORG'));
    }
}

class vCardMockModule extends Person
{
    public static $_savedObjects = array();
    
    public $first_name;
    public $last_name;
    public $salutation;
    public $phone_fax;
    public $phone_home;
    public $phone_mobile;
    public $phone_work;
    public $email1;
    public $primary_address_street;
    public $primary_address_city;
    public $primary_address_state;
    public $primary_address_postalcode;
    public $primary_address_country;
    public $department;
    public $title;

    public function save()
    {
        $this->id = create_guid();

        self::$_savedObjects[$this->id] = $this;

        return $this->id;
    }

    public function retrieve($id = -1, $encode=true,$deleted=true)
	{
        if ( isset(self::$_savedObjects[$id]) ) {
            foreach(get_object_vars(self::$_savedObjects[$id]) as $var => $val) {
                $this->$var = $val;
            }
            return self::$_savedObjects[$id];
        }

        return null;
    }

    public function ACLFilterFields()
    {
    }
}
