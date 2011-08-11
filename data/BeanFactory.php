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


require_once('data/SugarBean.php');

class BeanFactory {
    protected static $loadedBeans = array();
    protected static $maxLoaded = 10;
    protected static $total = 0;
    protected static $loadOrder = array();
    public static $hits = 0;

    /**
     * Returns a SugarBean object by id. The Last 10 loaded beans are cached in memory to prevent multiple retrieves per request.
     * If no id is passed, a new bean is created.
     * @static
     * @param  String $module
     * @param String $id
     * @return SugarBean
     */
    static function getBean($module, $id = null)
    {
        if (!isset(self::$loadedBeans[$module]))
            self::$loadedBeans[$module] = array();

        $beanClass = self::getBeanName($module);

        if (empty($beanClass) || !class_exists($beanClass)) return false;

        if (!empty($id))
        {
            if (empty(self::$loadedBeans[$module][$id]))
            {
                $bean = new $beanClass();
                $bean->retrieve($id);
                self::registerBean($module, $bean, $id);
            } else
            {
                self::$hits++;
                $bean = self::$loadedBeans[$module][$id];
            }
        } else {
            $bean = new $beanClass();
        }
        
        return $bean;
    }

    static function newBean($module)
    {
        return self::getBean($module);
    }

    static function getBeanName($module)
    {
        global $beanList;
        if (empty($beanList[$module]))  return false;

        return $beanList[$module];
    }

    static function registerBean($module, $bean, $id=false)
    {
        global $beanList;
        if (empty($beanList[$module]))  return false;

        if (!isset(self::$loadedBeans[$module]))
            self::$loadedBeans[$module] = array();

        if (self::$total > self::$maxLoaded)
        {
            $index = self::$total - self::$maxLoaded;
            $info = self::$loadOrder[$index];
            unset(self::$loadedBeans[$info['module']][$info['id']]);
            unset(self::$loadOrder[$index]);
        }

        if(!empty($bean->id))
           $id = $bean->id;
        
        if ($id)
        {
            self::$loadedBeans[$module][$id] = $bean;
            self::$total++;
            self::$loadOrder[self::$total] = array("module" => $module, "id" => $id);
        }

        return $beanList[$module];
    }
}

