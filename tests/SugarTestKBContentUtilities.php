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

require_once 'modules/KBContents/KBContent.php';

class SugarTestKBContentUtilities
{

    protected static $_createdBeans = array();

    private function __construct() {}

    public static function createBean($values = array())
    {
        $defaults = array(
            'name' => 'SugarKBContent' . time(),
        );

        $values = array_merge($defaults, $values);
        $bean = new KBContentMock();
        $bean->populateFromRow($values);
        $bean->save();
        DBManagerFactory::getInstance()->commit();
        self::$_createdBeans[] = $bean;
        return $bean;
    }

    public static function removeAllCreatedBeans()
    {
        $db = DBManagerFactory::getInstance();
        $beans = self::$_createdBeans;
        $ids = array();

        foreach ($beans as $bean) {
        	$ids[] = $bean->id;
        	$db->query('DELETE FROM kbdocuments WHERE id = ' . $db->quoted($bean->kbdocument_id));	
        	$db->query('DELETE FROM kbarticles WHERE id = ' . $db->quoted($bean->kbarticle_id));	
        }

        $conditions = implode(',', array_map(array($db, 'quoted'), $ids));
        $db->query('DELETE FROM kbcontents WHERE id IN (' . $conditions . ')');
        $db->query('DELETE FROM kbcontents_audit WHERE id IN (' . $conditions . ')');
        self::$_createdBeans = array();
    }
}

class KBContentMock extends KBContent
{
    public function resetActiveRevision()
    {
        $this->resetActivRev();
    }
}
