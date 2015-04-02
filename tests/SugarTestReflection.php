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

/*
 * This class helps testing by exposing protected and private elements to the tests
 */
class SugarTestReflection
{
    protected static $supported;
    
    /**
     * This verifies that the PHP we are running on is new enough for this fanciness
     * @return bool
     */
    public static function isSupported()
    {
        if (isset(self::$supported)) {
            return self::$supported;
        }
        if (version_compare(PHP_VERSION, '5.3.0') < 0) {
            self::$supported = false;
        } else {
            self::$supported = true;
        }
        
        return self::$supported;
    }
    
    public static function callProtectedMethod($classOrObject, $method, $args = array())
    {
        $rm = new ReflectionMethod($classOrObject, $method);
        $rm->setAccessible(true);
        $object = is_object($classOrObject) ? $classOrObject : null;
        return $rm->invokeArgs($object, $args);
    }

    public static function setProtectedValue($object, $property, $value)
    {
        $ro = new ReflectionObject($object);
        $rp = $ro->getProperty($property);
        $rp->setAccessible(true);
        $rp->setValue($object, $value);
    }

    public static function getProtectedValue($object, $property)
    {
        $ro = new ReflectionObject($object);
        $rp = $ro->getProperty($property);
        $rp->setAccessible(true);
        return $rp->getValue($object);
    }
}
