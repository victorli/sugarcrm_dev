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

/**
 * SoapHelperWebServiceTest.php
 *
 * This test may be used to write tests against the SoapHelperWebService.php file and the utility functions found there.
 *
 * @author Collin Lee
 */

require_once('service/core/SoapHelperWebService.php');
require_once('soap/SoapError.php');

class SoapHelperWebServiceTest extends Sugar_PHPUnit_Framework_TestCase {

static $original_service_object;

public static function setUpBeforeClass()
{
    global $service_object;
    if(!empty($service_object))
    {
        self::$original_service_object = $service_object;
    }
}

public static function tearDownAfterClass()
{
    if(!empty(self::$original_service_object))
    {
        global $service_object;
        $service_object = self::$original_service_object;
    }
}

/**
 * retrieveCheckQueryProvider
 *
 */
public function retrieveCheckQueryProvider()
{
    global $service_object;
    $service_object = new ServiceMockObject();
    $error = new SoapError();
    return array(
        array($error, "id = 'abc'", true),
        array($error, "user.id = prospects.id", true),
        array($error, "id $% 'abc'", false),
    );
}

/**
 * testCheckQuery
 * This function tests the checkQuery function in the SoapHelperWebService class
 *
 * @dataProvider retrieveCheckQueryProvider();
 */
public function testCheckQuery($errorObject, $query, $expected)
{
     $helper = new SoapHelperWebServices();
     if(!method_exists($helper, 'checkQuery'))
     {
         $this->markTestSkipped('Method checkQuery does not exist');
     }

     $result = $helper->checkQuery($errorObject, $query);
     $this->assertEquals($expected, $result, 'SoapHelperWebService->checkQuery functions as expected');
}

}

/**
 * ServiceMockObject
 *
 * Used to override global service_object
 */
class ServiceMockObject {
    public function error()
    {

    }
}