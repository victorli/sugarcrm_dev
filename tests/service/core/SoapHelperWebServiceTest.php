<?php
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2011 SugarCRM Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/


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
        array($error, "id ###### 'abc'", false),
        array($error, "##################################################### ==== 'abc'", false),
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