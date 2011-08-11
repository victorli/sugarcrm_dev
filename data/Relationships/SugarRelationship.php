<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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


global $dictionary;
//Load all relationship metadata
include_once("modules/TableDictionary.php");
require_once("data/BeanFactory.php");


define('REL_LHS','LHS');
define('REL_RHS','RHS');
define('REL_BOTH','BOTH_SIDES');
define('REL_MANY_MANY', 'many-to-many');
define('REL_ONE_MANY', 'one-to-many');
define('REL_ONE_ONE', 'one-to-one');
/**
 * A relationship is between two modules.
 * It contains at least two links.
 * Each link represents a connection from one record to the records linked in this relationship.
 * Links have a context(focus) bean while relationships do not.
 *
 *
 */
abstract class SugarRelationship
{
    protected $def;
    protected $lhsLink;
    protected $rhsLink;
    protected $ignore_role_filter = false;
    protected $self_referencing = false; //A relationship is self referencing when LHS module = RHS Module

    protected static $beansToResave = array();

    public abstract function add($lhs, $rhs, $additionalFields = array());

    /**
     * @abstract
     * @param  $lhs SugarBean
     * @param  $rhs SugarBean
     * @return void
     */
    public abstract function remove($lhs, $rhs);

    public abstract function load($link);

    /**
     * Gets the query to load a link.
     * This is currently public, but should prob be made protected later.
     * @abstract
     * @param  $link Link Object to get query for.
     * @return void
     */
    public abstract function getQuery($link, $params = array());

    public abstract function getJoin($link);

    public abstract function relationship_exists($lhs, $rhs);

    public abstract function getRelationshipTable();

    /**
     * @param  $link Link2 removes this link from the relationship.
     * @return void
     */
    public function removeAll($link)
    {
        $focus = $link->getFocus();
        $related = $link->getBeans();
        foreach($related as $relBean)
        {
            if (empty($relBean->id)) {
                continue;
            }

            if ($link->getSide() == REL_LHS)
                $this->remove($focus, $relBean);
            else
                $this->remove($relBean, $focus);
        }
    }

    public function removeById($rowID){
        $this->removeRow(array("id" => $rowID));
    }

    public function getRHSModule()
    {
        return $this->def['rhs_module'];
    }

    public function getLHSModule()
    {
        return $this->def['lhs_module'];
    }

    public function getLHSLink()
    {
        return $this->lhsLink;
    }

    public function getRHSLink()
    {
        return $this->rhsLink;
    }

    public function getFields()
    {
        return isset($this->def['fields']) ? $this->def['fields'] : array();
    }

    protected function addRow($row)
    {
        $existing = $this->checkExisting($row);
        if (!empty($existing)) //Update the existing row, overriding the values with those passed in
            return $this->updateRow($existing['id'], array_merge($existing, $row));

        $values = array();
        foreach($this->getFields() as  $def)
        {
            $field = $def['name'];
            if (isset($row[$field]))
                $values[$field] = "'{$row[$field]}'";
            else
                $values[$field] = "''";
        }
        $values = implode(',', $values);
        if (!empty($values))
        {
            $query = "INSERT INTO {$this->getRelationshipTable()} VALUES ($values)";
            DBManagerFactory::getInstance()->query($query);
        }
    }

    protected function updateRow($id, $values)
    {
        $newVals = array();
        //Unset the ID since we are using it to update the row
        if (isset($values['id'])) unset($values['id']);
        foreach($values as $field => $val)
        {
            $newVals[] = "$field='$val'";
        }

        $newVals = implode(",",$newVals);

        $query = "UPDATE {$this->getRelationshipTable()} set $newVals WHERE id='$id'";

        return DBManagerFactory::getInstance()->query($query);
    }

    protected function removeRow($where)
    {
        if (empty($where))
            return false;

        $date_modified = TimeDate::getInstance()->getNow()->asDb();
        $stringSets = array();
        foreach ($where as $field => $val)
        {
            $stringSets[] = "$field = '$val'";
        }
        $whereString = "WHERE " . implode(" AND ", $stringSets);

        $query = "UPDATE {$this->getRelationshipTable()} set deleted=1 , date_modified = '$date_modified' $whereString";

        return DBManagerFactory::getInstance()->query($query);

    }

    /**
     * Checks for an existing row who's keys matche the one passed in.
     * @param  $row
     * @return array|bool returns false if now row is found, otherwise the row is returned
     */
    protected function checkExisting($row)
    {
        $leftIDName = $this->def['join_key_lhs'];
        $rightIDName = $this->def['join_key_rhs'];
        if (empty($row[$leftIDName]) ||  empty($row[$rightIDName]))
            return false;

        $leftID = $row[$leftIDName];
        $rightID = $row[$rightIDName];
        $query = "SELECT * FROM {$this->getRelationshipTable()} WHERE $leftIDName='$leftID' AND $rightIDName='$rightID' AND deleted=0";

        $db = DBManagerFactory::getInstance();
        $result = $db->query($query);
        $row = $db->fetchByAssoc($result);
        if (!empty($row))
        {
            return $row;
        } else{
            return false;
        }
    }

    protected function getCustomLogicArguments($focus, $related, $link_name)
    {
        $custom_logic_arguments = array();
        $custom_logic_arguments['id'] = $focus->id;
        $custom_logic_arguments['related_id'] = $related->id;
        $custom_logic_arguments['module'] = $focus->module_dir;
        $custom_logic_arguments['related_module'] = $related->module_dir;
        $custom_logic_arguments['link'] = $link_name;
        $custom_logic_arguments['relationship'] = $this->name;

        return $custom_logic_arguments;
    }

    /**
     * @param  SugarBean $focus
     * @param  SugarBean $related
     * @param string $link_name
     * @return void
     */
    protected function callAfterAdd($focus, $related, $link_name="")
    {
        $custom_logic_arguments = $this->getCustomLogicArguments($focus, $related, $link_name);
        $focus->call_custom_logic('after_relationship_add', $custom_logic_arguments);
    }

    /**
     * @param  SugarBean $focus
     * @param  SugarBean $related
     * @param string $link_name
     * @return void
     */
    protected function callAfterDelete($focus, $related, $link_name="")
    {
        $custom_logic_arguments = $this->getCustomLogicArguments($focus, $related, $link_name);
        $focus->call_custom_logic('after_relationship_delete', $custom_logic_arguments);
    }

    protected function add_deleted_clause($deleted=0,$add_and='',$prefix='') {

		if (!empty($prefix)) $prefix.='.';
		if (!empty($add_and)) $add_and=' '.$add_and.' ';

		if ($deleted==0)  return $add_and.$prefix.'deleted=0';
		if ($deleted==1) return $add_and.$prefix.'deleted=1';
		else return '';
	}

	protected function add_optional_where_clause($optional_array, $add_and='',$prefix='') {

		if (!empty($prefix)) $prefix.='.';
		if (!empty($add_and)) $add_and=' '.$add_and.' ';

		if(!empty($optional_array)){
			return $add_and.$prefix."".$optional_array['lhs_field']."".$optional_array['operator']."'".$optional_array['rhs_value']."'";
		}
		return '';
	//end function _add_optional_where_clause
	}

    /**
     * @param  SugarBean $bean
     * @return void
     */
    public static function addToResaveList($bean)
    {
        if (!isset(self::$beansToResave[$bean->module_dir]))
        {
            self::$beansToResave[$bean->module_dir] = array();
        }
        self::$beansToResave[$bean->module_dir][$bean->id] = $bean;
    }

    public static function resaveRelatedBeans()
    {
        $GLOBALS['resavingRelatedBeans'] = true;

        //Resave any bean not currently in the middle of a save operation
        foreach(self::$beansToResave as $module => $beans)
        {
            foreach ($beans as $bean)
            {
                if (empty($bean->deleted) && empty($bean->in_save))
                {
                    $bean->save();
                }
            }
        }

        $GLOBALS['resavingRelatedBeans'] = false;

        //Reset the list of beans that will need to be resaved
        self::$beansToResave = array();
    }


    public function isParentRelationship()
    {
        //Update role fields
        if(!empty($this->def["relationship_role_column"]) && !empty($this->def["relationship_role_column_value"])
           && $this->def["relationship_role_column"] == "parent_type" && $this->def['rhs_key'] == "parent_id")
        {
            return true;
        }
        return false;
    }

    public function __get($name)
    {
        if (isset($this->def[$name]))
            return $this->def[$name];

        switch($name)
        {
            case "relationship_type":
                return $this->type;
            case 'relationship_name':
                return $this->name;
            case "lhs_module":
                return $this->getLHSModule();
            case "rhs_module":
                return $this->getRHSModule();
            case "lhs_table" :
                isset($this->def['lhs_table']) ? $this->def['lhs_table'] : "";
            case "rhs_table" :
                isset($this->def['rhs_table']) ? $this->def['rhs_table'] : "";
            case "list_fields":
                return array('lhs_table', 'lhs_key', 'rhs_module', 'rhs_table', 'rhs_key', 'relationship_type');
        }

        if (isset($this->$name))
            return $this->$name;

        return null;
    }
}