<?php

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2015 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('modules/ModuleBuilder/parsers/parser.label.php');

class BugCRYS697ParserLabelTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $lang = 'en_us';
    private $testModule = 'Accounts';

    protected function setUp()
    {
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($this->getFileName());
        if (SugarAutoLoader::fileExists($this->getFileName())) {
            SugarAutoLoader::unlink($this->getFileName());
        }
        parent::setUp();
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    private function getFileName()
    {
        return "custom/modules/{$this->testModule}/Ext/Language/{$this->lang}.lang.ext.php";
    }


    public function testSavingEmptyLabels()
    {
        ParserLabel::addLabels($this->lang, array(), $this->testModule);
        $this->assertFalse(SugarAutoLoader::fileExists($this->getFileName()));
    }

} 
