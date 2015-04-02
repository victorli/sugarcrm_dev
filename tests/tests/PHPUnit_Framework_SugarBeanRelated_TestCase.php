<?php

require_once 'data/SugarBean.php';

class PHPUnit_Framework_SugarBeanRelated_TestCase extends PHPUnit_Framework_TestCase
{
    public function getBeanMock($className = null)
    {
        $builder = $this->getMockBuilder('SugarBean')->disableOriginalConstructor();

        if($className)
        {
            $builder->setMockClassName($className);
        }

        return $builder->getMock();
    }
}