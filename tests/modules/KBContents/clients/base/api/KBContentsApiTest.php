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

require_once 'modules/KBContents/clients/base/api/KBContentsApi.php';

class KBContentsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var RestService
     */
    protected $service = null;

    /**
     * @var KBContentsApi
     */
    protected $api = null;

    /**
     * @var KBContentsMock
     */
    protected $bean;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new KBContentsApi();
        $this->bean = SugarTestKBContentUtilities::createBean();
    }

    public function tearDown()
    {
        $this->service = null;
        $this->api = null;

        SugarTestKBContentUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
    }

    public function testRelatedDocuments()
    {
        $result = $this->api->relatedDocuments($this->service, array(
            'module' => $this->bean->module_name,
            'record' => $this->bean->id,
        ));

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('next_offset', $result);
        $this->assertArrayHasKey('records', $result);
        $this->assertInternalType('array', $result['records']);
    }
}
