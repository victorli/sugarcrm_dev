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

require_once 'include/SubPanel/SubPanelDefinitions.php';

/**
 * Bug #52361
 * Relate field data is not displayed in subpanel
 * part 2
 * @author arymarchik@sugarcrm.com
 * @ticked 52361
 */
class Bug52361p2Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Quote
     */
    protected $_quote;

    /**
     * @var Contact
     */
    protected $_contact;

    /**
     * @var Account
     */
    protected $_account;

    public function setUp()
    {
        // TODO: FIX THIS WHEN THE MERGE WITH NUTMEG COMES IN
        $this->markTestSkipped();
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        $user = $GLOBALS['current_user'];
        parent::setUp();

        $this->_quote = SugarTestQuoteUtilities::createQuote();

        $this->_account = SugarTestAccountUtilities::createAccount();
        $this->_quote->account_id = $this->_account->id;
        $this->_quote->shipping_account_id = $this->_account->id;
        $this->_quote->billing_account_id = $this->_account->id;

        $this->_contact = SugarTestContactUtilities::createContact();
        $this->_quote->shipping_contact_id = $this->_contact->id;
        $this->_quote->billing_contact_id = $this->_contact->id;

        $this->_quote->team_id = $user->team_id;
        $this->_quote->team_set_id = $user->team_set_id;
        $this->_quote->assigned_user_id = $user->id;
        $this->_quote->save();

        $bundle = SugarTestProductBundleUtilities::createProductBundle();
        $bundle->team_id = $this->_quote->team_id;
        $bundle->team_set_id = $this->_quote->team_set_id;
        $bundle->save();
        $bundle->set_productbundle_quote_relationship($this->_quote->id, $bundle->id);

        for($i = 0; $i < rand(2,5); $i++)
        {
            $product = SugarTestProductUtilities::createProduct();
            $product->team_id = $this->_quote->team_id;
            $product->team_set_id = $this->_quote->team_set_id;
            $product->quote_id = $this->_quote->id;
            $product->account_id = $this->_quote->billing_account_id;
            $product->contact_id = $this->_quote->billing_contact_id;
            $product->modified_user_id = $user->id;
            $product->created_by = $user->id;
            $product->assigned_user_id = $user->id;
            $product->save();
            $bundle->set_productbundle_product_relationship($product->id, 1, $bundle->id );
        }
    }

    public function tearDown()
    {
        if ( $this->_quote ) {
            foreach($this->_quote->get_product_bundles() as $bundle)
            {
                foreach($bundle->get_products() as $product)
                {
                    $product->mark_deleted($product->id);
                }
            }
            $this->_quote->mark_deleted($this->_quote->id);
        }
        if ( $this->_contact ) {
            $this->_contact->mark_deleted($this->_contact->id);
        }
        if ( $this->_account ) {
            $this->_account->mark_deleted($this->_account->id);
        }
        parent::tearDown();
    }

    /**
     * Test product counts in product subpanel in contacts
     *
     * @group 52361
     * @return void
     */
    public function testContactSubPanel()
    {
        $layout_defs = array();
        include('modules/Contacts/metadata/subpaneldefs.php');
        $sum_products = 0;
        foreach($this->_quote->get_product_bundles() as $bundle)
        {
            $sum_products += count($bundle->get_products());
        }
        $panel = new aSubPanel('', $layout_defs['Contacts']['subpanel_setup']['products'], $this->_contact, true);
        $response = SugarBean::get_union_related_list($this->_contact, '', '', '', 0, $sum_products * 2, $sum_products * 2, '',$panel);
        $this->assertEquals($sum_products, $response['row_count']);
        $this->assertEquals($sum_products, count($response['list']));

        $this->_quote->quote_stage = (rand(0,1) == 1) ? 'Closed Lost' : 'Closed Dead';
        $this->_quote->save();
        $response = SugarBean::get_union_related_list($this->_contact, '', '', '', 0, $sum_products * 2, $sum_products * 2, '',$panel);
        $this->assertEquals(0, $response['row_count']);
        $this->assertEquals(0, count($response['list']));
    }

    /**
     * Test product counts in product subpanel in account
     *
     * @group 52361
     * @return void
     */
    public function testAccountSubPanel()
    {
        $layout_defs = array();
        include('modules/Accounts/metadata/subpaneldefs.php');
        $sum_products = 0;
        foreach($this->_quote->get_product_bundles() as $bundle)
        {
            $sum_products += count($bundle->get_products());
        }
        $panel = new aSubPanel('', $layout_defs['Accounts']['subpanel_setup']['products'], $this->_account, true);
        $response = SugarBean::get_union_related_list($this->_account, '', '', '', 0, $sum_products * 2, $sum_products * 2, '',$panel);
        $this->assertEquals($sum_products, $response['row_count']);
        $this->assertEquals($sum_products, count($response['list']));

        $this->_quote->quote_stage = (rand(0,1) == 1) ? 'Closed Lost' : 'Closed Dead';
        $this->_quote->save();
        $response = SugarBean::get_union_related_list($this->_account, '', '', '', 0, $sum_products * 2, $sum_products * 2, '',$panel);
        $this->assertEquals(0, $response['row_count']);
        $this->assertEquals(0, count($response['list']));
    }
}
