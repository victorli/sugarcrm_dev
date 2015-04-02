<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/
require_once 'tests/include/database/DBHelperTest.php';

class DBHelperTestPrep extends DBHelperTest
{
    public $usePreparedStatements = true;

    public function testDeleteSQLPrep()
    {
        list($sql, $data) = $this->_helper->deleteSQL(new Contact, array("id" => "17"), true);

        $this->assertRegExp('/update\s*contacts\s*set\s*deleted\s*=\s*1/i',$sql);
        $this->assertRegExp('/where\s*contacts.id\s*=\s*\?id/i',$sql);
        $this->assertContains("17", $data);
    }

    public function testRetrieveSQLPrep()
    {
        list($sql, $data) = $this->_helper->retrieveSQL(new Contact, array("id" => "18"), true);

        $this->assertRegExp('/select\s*\*\s*from\s*contacts/i',$sql);
        $this->assertRegExp('/where\s*contacts.id\s*=\s*\?id/i',$sql);
        $this->assertContains("18", $data);
    }

    public function testUpdateSQLPrep()
    {
        list($sql, $data) = $this->_helper->updateSQL(new Contact, array("id" => "19"), true);

        $this->assertRegExp('/update\s*contacts\s*set/i',$sql);
        $this->assertRegExp('/where\s*contacts.id\s*=\s*\?id/i',$sql);
        $this->assertContains("19", $data);
    }
}
