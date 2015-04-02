<?php

require_once 'modules/ForecastManagerWorksheets/clients/base/api/ForecastManagerWorksheetsExportApi.php';

/**
 * RS-126
 * Prepare ForecastManagerWorksheetsExport Api
 */
class RS126Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));
        $GLOBALS['current_user']->reports_to_id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user']->save();

        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test behavior of export method
     */
    public function testExport()
    {
        $api = $this->getMock('ForecastManagerWorksheetsExportApi', array('doExport'));
        $api->expects($this->once())->method('doExport')->with($this->equalTo($this->service), $this->logicalNot($this->isEmpty()), $this->logicalNot($this->isEmpty()));
        $api->export($this->service, array());
    }
}
