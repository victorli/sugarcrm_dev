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

require_once 'modules/ExpressionEngine/clients/base/api/RelatedValueApi.php';

/**
 * Tests RelatedValueApi.
 */
class RS148Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var SugarBean
     */
    protected $account;

    /**
     * @var SugarBean
     */
    protected $contact;

    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
        $this->api = new RelatedValueApi();
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->contact = SugarTestContactUtilities::createContact('', array('first_name' => 'RS148Test_FName'));
        $this->account->load_relationship('contacts');
        $this->account->contacts->add($this->contact);
        $this->account->save();
    }

    protected function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * @param array $fields
     * @param mixed $expected
     * @param bool $setRelatedId
     * @dataProvider relatedProvider
     */
    public function testRelatedValue($fields, $expected, $setRelatedId)
    {
        if ($setRelatedId) {
            $fields['contacts']['relId'] = $this->contact->id;
            $expected['contacts']['relId'] = $this->contact->id;
        }
        $encoded_fields = json_encode($fields);
        $result = $this->api->getRelatedValues(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts', 'fields' => $encoded_fields, 'record' => $this->account->id)
        );
        $this->assertEquals($expected, $result);
    }

    public function relatedProvider()
    {
        return array(
            'empty' => array(
                array(),
                array(),
                false,
            ),
            'related' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'related',
                        'relate' => 'first_name',
                    )
                ),
                array(
                    'contacts' => array(
                        'related' => array(
                            'first_name' => 'RS148Test_FName'
                        )
                    )
                ),
                true,
            ),
            'count' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'count',
                        'relate' => 'first_name',
                    )
                ),
                array(
                    'contacts' => array(
                        'count' => 1
                    )
                ),
                false,
            ),
            'rollupMin' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'rollupMin',
                        'relate' => 'first_name',
                    )
                ),
                array(
                    'contacts' => array(
                        'rollupMin' => array(
                            'first_name' => ''
                        )
                    )
                ),
                false,
            ),
            'rollupCurrencySum' => array(
                array(
                    'contacts' => array(
                        'link' => 'contacts',
                        'type' => 'rollupCurrencySum',
                        'relate' => 'first_name',
                    )
                ),
                array(
                    'contacts' => array(
                        'rollupCurrencySum' => array(
                            'first_name' => 0
                        )
                    )
                ),
                false,
            )
        );
    }
}
