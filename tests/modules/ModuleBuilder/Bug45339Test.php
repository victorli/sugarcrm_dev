<?php

/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2012 SugarCRM Inc.
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
 * Bug #45339
 * Export Customizations Does Not Cleanly Handle Relationships.
 *
 * @ticket 45339
 */
class Bug45339Test extends Sugar_PHPUnit_Framework_TestCase
{

    private $relationAccountContact = null;
    private $relationContactAccount = null;
    private $mbPackage = null;
    private $keys = array(
        'module' => "ModuleBuilder",
        'action' => "SaveRelationship",
        'remove_tables' => "true",
        'view_module' => "",
        'relationship_lang' => "en_us",
        'relationship_name' => "",
        'lhs_module' => "",
        'relationship_type' => "many-to-many",
        'rhs_module' => "",
        'lhs_label' => "",
        'rhs_label' => "",
        'lhs_subpanel' => "default",
        'rhs_subpanel' => "default",
    );
    private $packName = 'test_package';

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');

        $_REQUEST = $this->keys;

        $_REQUEST['view_module'] = "Accounts";
        $_REQUEST['lhs_module'] = "Accounts";
        $_REQUEST['rhs_module'] = "Contacts";
        $_REQUEST['lhs_label'] = "Accounts";
        $_REQUEST['rhs_label'] = "Contacts";

        $relationAccountContact = new DeployedRelationships($_REQUEST['view_module']);
        $this->relationAccountContact = $relationAccountContact->addFromPost();
        $relationAccountContact->save();
        $relationAccountContact->build();

        $_REQUEST['view_module'] = "Contacts";
        $_REQUEST['lhs_module'] = "Contacts";
        $_REQUEST['rhs_module'] = "Accounts";
        $_REQUEST['lhs_label'] = "Contacts";
        $_REQUEST['rhs_label'] = "Accounts";

        $relationContactAccount = new DeployedRelationships($_REQUEST['view_module']);
        $this->relationContactAccount = $relationContactAccount->addFromPost();
        $relationContactAccount->save();
        $relationContactAccount->build();

        $this->mbPackage = new Bug45339MBPackageMock($this->packName);
    }

    public function tearDown()
    {
        $relationshipAccountContact = new DeployedRelationships($this->relationAccountContact->getLhsModule());
        $relationshipAccountContact->delete($this->relationAccountContact->getName());
        $relationshipAccountContact->save();

        $relationshipContactAccount = new DeployedRelationships($this->relationContactAccount->getLhsModule());
        $relationshipContactAccount->delete($this->relationContactAccount->getName());
        $relationshipContactAccount->save();

        SugarRelationshipFactory::deleteCache();

        unset($_REQUEST);

        SugarTestHelper::tearDown();
    }

    /**
     * @group 45339
     */
    public function testGetCustomRelationshipsByModuleName()
    {
        /* @var $this->mbPackage MBPackage */
        $accountsAllCustomRelationships = $this->mbPackage->getCustomRelationshipsByModuleNameTest('Accounts');
        // Created in the Account module.
        $accountsLhsCustomRelationships = $this->mbPackage->getCustomRelationshipsByModuleNameTest('Accounts', true);
        $wrongModuleName = $this->mbPackage->getCustomRelationshipsByModuleNameTest('Wrong_module_name');

        $this->assertArrayHasKey($this->relationAccountContact->getName(), $accountsAllCustomRelationships);
        $this->assertArrayHasKey($this->relationContactAccount->getName(), $accountsAllCustomRelationships);

        $this->assertArrayHasKey($this->relationAccountContact->getName(), $accountsLhsCustomRelationships);
        $this->assertArrayNotHasKey($this->relationContactAccount->getName(), $accountsLhsCustomRelationships);

        $this->assertFalse($wrongModuleName); // check
    }

    /**
     * @group 45339
     */
    public function testGetCustomRelationshipsMetaFilesByModuleName()
    {
        $accountContactMetaPath = sprintf(
                'custom%1$smetadata%1$s' . $this->relationAccountContact->getName() . 'MetaData.php',
                DIRECTORY_SEPARATOR
        );
        $accountContactTablePath = sprintf(
                'custom%1$sExtension%1$sapplication%1$sExt%1$sTableDictionary%1$s' . $this->relationAccountContact->getName() . '.php',
                DIRECTORY_SEPARATOR
        );
        $contactAccountMetaPath = sprintf(
                'custom%1$smetadata%1$s' . $this->relationContactAccount->getName() . 'MetaData.php',
                DIRECTORY_SEPARATOR
        );

        /* @var $this->mbPackage MBPackage */
        $accountsAllFiles = $this->mbPackage->getCustomRelationshipsMetaFilesByModuleNameTest('Accounts');
        $accountsOnlyMetaFile = $this->mbPackage->getCustomRelationshipsMetaFilesByModuleNameTest('Accounts', true, true);
        $wrongModuleName = $this->mbPackage->getCustomRelationshipsMetaFilesByModuleNameTest('Wrong_module_name');

        $this->assertContains($accountContactMetaPath, $accountsAllFiles);
        $this->assertContains($accountContactTablePath, $accountsAllFiles);
        $this->assertContains($contactAccountMetaPath, $accountsAllFiles);

        $this->assertContains($accountContactMetaPath, $accountsOnlyMetaFile);
        $this->assertNotContains($contactAccountMetaPath, $accountsOnlyMetaFile);

        $this->assertInternalType('array', $wrongModuleName);
        $this->assertEmpty($wrongModuleName);
    }

    /**
     * @group 45339
     */
    public function testGetExtensionsList()
    {
        // Create new relationship between Leads and Accounts
        $_REQUEST['view_module'] = "Leads";
        $_REQUEST['lhs_module'] = "Leads";
        $_REQUEST['rhs_module'] = "Accounts";
        $_REQUEST['lhs_label'] = "Leads";
        $_REQUEST['rhs_label'] = "Accounts";

        $deployedRelation = new DeployedRelationships($_REQUEST['view_module']);
        $relationLeadAccount = $deployedRelation->addFromPost();
        $deployedRelation->save();
        $deployedRelation->build();

        $accountContactRelInAccountVardefExtensions = sprintf(
                'custom%1$sExtension%1$smodules%1$sAccounts%1$sExt%1$sVardefs%1$s' . $this->relationAccountContact->getName() . '_Accounts.php',
                DIRECTORY_SEPARATOR
        );
        $contactAccountRelInAccountVardefExtensions = sprintf(
                'custom%1$sExtension%1$smodules%1$sAccounts%1$sExt%1$sVardefs%1$s' . $this->relationContactAccount->getName() . '_Accounts.php',
                DIRECTORY_SEPARATOR
        );
        $leadAccountRelInAccountVardefExtensions = sprintf(
                'custom%1$sExtension%1$smodules%1$sAccounts%1$sExt%1$sVardefs%1$s' . $relationLeadAccount->getName() . '_Accounts.php',
                DIRECTORY_SEPARATOR
        );

        /* @var $this->mbPackage MBPackage */
        $accountAllExtensions = $this->mbPackage->getExtensionsListTest('Accounts');
        $accountExtContacts = $this->mbPackage->getExtensionsListTest('Accounts', array('Contacts'));
        $accountExtWithWrongRelationship = $this->mbPackage->getExtensionsListTest('Accounts', array(''));
        $wrongModuleName = $this->mbPackage->getExtensionsListTest('Wrong_module_name');

        // Remove relationship
        $deployedRelation->delete($relationLeadAccount->getName());
        $deployedRelation->save();
        SugarRelationshipFactory::deleteCache();

        $this->assertContains($accountContactRelInAccountVardefExtensions, $accountAllExtensions);
        $this->assertContains($contactAccountRelInAccountVardefExtensions, $accountAllExtensions);
        $this->assertContains($leadAccountRelInAccountVardefExtensions, $accountAllExtensions);

        $this->assertContains($accountContactRelInAccountVardefExtensions, $accountExtContacts);
        $this->assertContains($contactAccountRelInAccountVardefExtensions, $accountExtContacts);
        $this->assertNotContains($leadAccountRelInAccountVardefExtensions, $accountExtContacts);

        $this->assertEmpty($accountExtWithWrongRelationship);

        $this->assertInternalType('array', $wrongModuleName);
        $this->assertEmpty($wrongModuleName);
    }

    /**
     * @group 45339
     */
    public function testGetExtensionsManifestForPackage()
    {
        /* @var $this->mbPackage MBPackage */
        $this->mbPackage->exportCustom(array('Accounts'), false, false);
        $installDefs = array();
        $packExtentionsPath = $this->mbPackage->getBuildDir() . DIRECTORY_SEPARATOR . 'Extension' . DIRECTORY_SEPARATOR . 'modules';
        $expected = 0;

        $this->mbPackage->getExtensionsManifestForPackageTest($this->mbPackage->getBuildDir(), $installDefs);

        $recursiveIterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($packExtentionsPath),
                        RecursiveIteratorIterator::SELF_FIRST
        );

        /* @var $fInfo SplFileInfo */
        foreach (new RegexIterator($recursiveIterator, "/\.php$/i") as $fInfo)
        {
            if ($fInfo->isFile())
            {
                ++$expected;
            }
        }

        $this->mbPackage->delete();
        $this->mbPackage->deleteBuild();

        $this->assertEquals($expected, count($installDefs['copy']));
    }

    /**
     * @group 45339
     */
    public function testCustomBuildInstall()
    {
        /* @var $this->mbPackage MBPackage */
        $this->mbPackage->exportCustom(array('Accounts'), false, false);
        $installDefString = $this->mbPackage->customBuildInstall(array('Accounts'), $this->mbPackage->getBuildDir());

        eval($installDefString);

        $this->mbPackage->delete();
        $this->mbPackage->deleteBuild();
        
        $this->assertArrayHasKey('relationships', $installdefs);
    }

}

class Bug45339MBPackageMock extends MBPackage
{

    public function getExtensionsManifestForPackageTest($path, &$installdefs)
    {
        return $this->getExtensionsManifestForPackage($path, $installdefs);
    }

    public function getExtensionsListTest($module, $includeRelationships = true)
    {
        return $this->getExtensionsList($module, $includeRelationships);
    }

    public function getCustomRelationshipsMetaFilesByModuleNameTest($moduleName, $lhs = false, $metadataOnly = false)
    {
        return $this->getCustomRelationshipsMetaFilesByModuleName($moduleName, $lhs, $metadataOnly);
    }

    public function getCustomRelationshipsByModuleNameTest($moduleName, $lhs = false)
    {
        return $this->getCustomRelationshipsByModuleName($moduleName, $lhs);
    }

}
