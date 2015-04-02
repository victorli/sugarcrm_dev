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


require_once 'modules/ModuleBuilder/parsers/relationships/DeployedRelationships.php' ;
require_once 'modules/MySettings/TabController.php';
require_once 'include/SubPanel/SubPanelDefinitions.php';
require_once 'modules/ModuleBuilder/parsers/views/SubpanelMetaDataParser.php';

/**
 * Bug #52361
 * Relate field data is not displayed in subpanel
 *
 * @author mgusev@sugarcrm.com
 * @ticked 52361
 */
class Bug52361Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    protected $user = null;

    /**
     * @var DeployedRelationships
     */
    protected $relationships = null;

    /**
     * @var OneToOneRelationship
     */
    protected $relationship = null;

    /**
     * @var TabController
     */
    protected $tabs = null;

    /**
     * @var bool
     */
    protected $isTabsUpdated = false;

    /**
     * @var bool
     */
    protected $isSubPanelUpdated = false;

    public function setUp()
    {
        $this->markTestIncomplete("Marking as incomplete as it can take long time to run");
        return;
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user', array(true, 1));
        parent::setUp();

        // Adding products to visible modules
        $this->tabs = new TabController();
        $tabs = $this->tabs->get_system_tabs();
        if (isset($tabs['Products']) == false)
        {
            $tabs['Products'] = 'Products';
            $this->tabs->set_system_tabs($tabs);
            $this->isTabsUpdated = true;
        }

        // Adding products to visible subpanels
        $subpanels = SubPanelDefinitions::get_hidden_subpanels();
        if (isset($subpanels['products']))
        {
            unset($subpanels['products']);
            SubPanelDefinitions::set_hidden_subpanels($subpanels);
            $this->isSubPanelUpdated = true;
        }

        // Adding relation between products and users
        $this->relationships = new DeployedRelationships('Products');
        $definition = array(
            'lhs_module' => 'Products',
            'relationship_type' => 'one-to-one',
            'rhs_module' => 'Users'
        );
        $this->relationship = RelationshipFactory::newRelationship($definition);
        $this->relationships->add($this->relationship);
        $this->relationships->save();
        $this->relationships->build();
        SugarTestHelper::setUp('relation', array(
            'Products',
            'Users'
        ));

        // Creating local user for relations
        $this->user = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        $this->markTestIncomplete("Marking as incomplete as it can take long time to run");
        return;
        // Removing relation between products and users
        $this->relationships->delete($this->relationship->getName());
        $this->relationships->save();

        // Hiding products from subpanels
        if ($this->isSubPanelUpdated == true)
        {
            $subpanels = SubPanelDefinitions::get_hidden_subpanels();
            $subpanels['products'] = 'products';
            SubPanelDefinitions::set_hidden_subpanels($subpanels);
            $this->isSubPanelUpdated = false;
        }

        // Hiding products from modules
        if ($this->isTabsUpdated == true)
        {
            $tabs = $this->tabs->get_system_tabs();
            unset($tabs[array_search('Products', $tabs)]);
            $this->tabs->set_system_tabs($tabs);
            $this->isTabsUpdated = false;
        }

        // Restoring $GLOBALS
        parent::tearDown();
        $_REQUEST = array();
        unset($_SERVER['REQUEST_METHOD']);
        SugarCache::$isCacheReset = false;

        // Removing temp data
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test creates relation between product account and user
     * and tries to assert that user name is present in product subpanel in accounts
     *
     * @group 52361
     * @return void
     */
    public function testAccounts()
    {
        $this->markTestIncomplete("Marking as incomplete as it can take long time to run");
        return;
        global $currentModule;
         $currentModule = 'Accounts';

        // Adding username field to subpanel of products
        $studio = new SubpanelMetaDataParser('products', 'Accounts', '');
        foreach ($studio->getFieldDefs() as $name => $def)
        {
            if (isset($def['type']) == false || $def['type'] != 'relate')
            {
                continue;
            }
            if ($def['link'] != $this->relationship->getName())
            {
                continue;
            }
            $studio->_viewdefs[$name] = $def;
            break;
        }
        $studio->handleSave(false);

        // Creating beans and relations
        $field = $this->relationship->getName();
        $account = SugarTestAccountUtilities::createAccount();
        $product = SugarTestProductUtilities::createProduct();
        $product->load_relationship($field);
        $product->{$field}->add($this->user);
        $product->account_id = $account->id;
        $product->account_name = $account->name;
        $product->save();

        // Getting data of subpanel
        $_REQUEST['module'] = 'Accounts';
        $_REQUEST['action'] = 'DetailView';
        $_REQUEST['record'] = $account->id;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($GLOBALS['focus']);
        $subpanels = new SubPanelDefinitions($account, 'Accounts');
        $subpanelDef = $subpanels->load_subpanel('products');
        $subpanel = new SubPanel('Accounts', $account->id, 'products', $subpanelDef, 'Accounts');
        $subpanel->setTemplateFile('include/SubPanel/SubPanelDynamic.html');
        $subpanel->display();
        $actual = $this->getActualOutput();

        unset($studio->_viewdefs[$name]);
        $studio->handleSave(false);
        if (sugar_is_file('custom/Extension/modules/Accounts/Ext/Layoutdefs/_overrideAccount_subpanel_products.php', 'w'))
        {
            unlink('custom/Extension/modules/Accounts/Ext/Layoutdefs/_overrideAccount_subpanel_products.php');
        }

        $this->assertContains($this->user->name, $actual, 'User name is not displayed in subpanel');
    }

    /**
     * Test creates relation between product contact and user
     * and tries to assert that user name is present in product subpanel in contacts
     *
     * @group 52361
     * @return void
     */
    public function testContacts()
    {
        $this->markTestIncomplete("Marking as incomplete as it can take long time to run");
        return;
        global $currentModule;
        $currentModule = 'Contacts';

        // Adding username field to subpanel of products
        $studio = new SubpanelMetaDataParser('products', 'Contacts', '');
        foreach ($studio->getFieldDefs() as $name => $def)
        {
            if (isset($def['type']) == false || $def['type'] != 'relate')
            {
                continue;
            }
            if ($def['link'] != $this->relationship->getName())
            {
                continue;
            }
            $studio->_viewdefs[$name] = $def;
            break;
        }
        $studio->handleSave(false);

        // Creating beans and relations
        $field = $this->relationship->getName();
        $contact = SugarTestContactUtilities::createContact();
        $product = SugarTestProductUtilities::createProduct();
        $product->load_relationship($field);
        $product->{$field}->add($this->user);
        $product->contact_id = $contact->id;
        $product->contact_name = $contact->name;
        $product->save();

        // Getting data of subpanel
        $_REQUEST['module'] = 'Contacts';
        $_REQUEST['action'] = 'DetailView';
        $_REQUEST['record'] = $contact->id;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($GLOBALS['focus']);
        $subpanels = new SubPanelDefinitions($contact, 'Contacts');
        $subpanelDef = $subpanels->load_subpanel('products');
        $subpanel = new SubPanel('Contacts', $contact->id, 'products', $subpanelDef, 'Contacts');
        $subpanel->setTemplateFile('include/SubPanel/SubPanelDynamic.html');
        $subpanel->display();
        $actual = $this->getActualOutput();

        unset($studio->_viewdefs[$name]);
        $studio->handleSave(false);
        if (sugar_is_file('custom/Extension/modules/Contacts/Ext/Layoutdefs/_overrideContact_subpanel_products.php', 'w'))
        {
            unlink('custom/Extension/modules/Contacts/Ext/Layoutdefs/_overrideContact_subpanel_products.php');
        }

        $this->assertContains($this->user->name, $actual, 'User name is not displayed in subpanel');
    }
}
