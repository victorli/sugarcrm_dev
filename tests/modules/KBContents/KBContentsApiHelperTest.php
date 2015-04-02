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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

require_once 'data/SugarBeanApiHelper.php';
require_once 'modules/KBContents/KBContentsApiHelper.php';
require_once 'include/api/RestService.php';

class KBContentsApiHelperTest extends Sugar_PHPUnit_Framework_TestCase 
{
    /**
     * @var KBContents
     */
    protected $bean;

    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->bean = SugarTestKBContentUtilities::createBean();
    }

    public function tearDown()
    {
        SugarTestKBContentUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
    }

    public function testFormatForApi() 
    {
        $helper = new KBContentsApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $data = $helper->formatForApi($this->bean);
        $lang = $this->bean->getPrimaryLanguage();

        $this->assertEquals($data['name'], $this->bean->name);
        $this->assertEquals($data['language'], $lang['key']);
        $this->assertInternalType('array', $data['attachment_list']);
    }
}
