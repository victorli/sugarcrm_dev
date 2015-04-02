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

require_once('modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php');

class SidecarMetaDataUpgraderBugCrys697Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function getStates()
    {
        return array(
            array(SidecarMetaDataUpgrader::UPGRADE_BASE, array('setBaseFilesToUpgrade')),
            array(SidecarMetaDataUpgrader::UPGRADE_MOBILE, array('setMobileFilesToUpgrade')),
            array(SidecarMetaDataUpgrader::UPGRADE_PORTAL, array('setPortalFilesToUpgrade')),
            array(SidecarMetaDataUpgrader::UPGRADE_SUBPANEL, array('setSubpanelFilesToUpgrade')),
            array(SidecarMetaDataUpgrader::UPGRADE_ALL, array('setBaseFilesToUpgrade', 'setPortalFilesToUpgrade', 'setMobileFilesToUpgrade', 'setSubpanelFilesToUpgrade'))
        );
    }

    /**
     * Tests correct use of diffirent upgrade type flags
     *
     * @dataProvider getStates
     * @param $state
     * @param $methods
     */
    public function testSetFilesToUpgrade($state, $methods)
    {
        $mock = $this->getMock('SidecarMetaDataUpgrader', array('setBaseFilesToUpgrade', 'setPortalFilesToUpgrade', 'setMobileFilesToUpgrade', 'setSubpanelFilesToUpgrade'));

        foreach ($methods as $method) {
            $mock->expects($this->once())->method($method);
        }
        $mock->setUpgradeCategories($state);
        $mock->setFilesToUpgrade();
    }
}