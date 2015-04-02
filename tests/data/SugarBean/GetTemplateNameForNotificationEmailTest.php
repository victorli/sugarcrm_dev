<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

class GetTemplateNameForNotificationEmailTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $accountBeanName;
    protected $accountObjectName;

    protected function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        global $beanList, $objectList;
        if (isset($beanList['Accounts'])) {
            $this->accountBeanName = $beanList['Accounts'];
        }
        if (isset($objectList['Accounts'])) {
            $this->accountObjectName = $objectList['Accounts'];
        }
        $beanList['Accounts'] = 'CustomAccountGetTemplateName';
        $objectList['Accounts'] = 'Account';
    }

    protected function tearDown()
    {
        global $beanList, $objectList;
        if (isset($this->accountBeanName)) {
            $beanList['Accounts'] = $this->accountBeanName;
        }
        else {
            unset($beanList['Accounts']);
        }
        if (isset($this->accountObjectName)) {
            $objectList['Accounts'] = $this->accountObjectName;
        }
        else {
            unset($objectList['Accounts']);
        }
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testCustomModule()
    {
        $account = new CustomAccountGetTemplateName();
        $this->assertEquals('Account', SugarTestReflection::callProtectedMethod($account, 'getTemplateNameForNotificationEmail'), 'Template name should be Account');
    }
}

class CustomAccountGetTemplateName extends Account
{
}