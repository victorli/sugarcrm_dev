<?php

require_once 'include/SugarFields/Fields/Fullname/SugarFieldFullname.php';
require_once 'include/MetaDataManager/ViewIterator.php';

/**
 * @covers SugarFieldFullname
 */
class SugarFieldFullnameTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SugarFieldFullname
     */
    private $sf;

    public function setUp()
    {
        $this->sf = new SugarFieldFullname('fullname');
    }

    public function testNameFormatFieldsAreConsidered()
    {
        global $locale;

        $locale = $this->getMockBuilder('Localization')
            ->setMethods(array('getNameFormatFields'))
            ->disableOriginalConstructor()
            ->getMock();
        $locale->expects($this->once())
            ->method('getNameFormatFields')
            ->with('TheModule')
            ->willReturn(array('foo', 'bar'));

        /** @var ViewIterator|PHPUnit_Framework_MockObject_MockObject $it */
        $it = $this->getMockBuilder('ViewIterator')
            ->disableOriginalConstructor()
            ->setMethods(array('dummy'))
            ->getMock();

        $fields = array();
        $this->sf->setModule('TheModule');
        $this->sf->iterateViewField($it, array(
            'name' => 'full_name',
        ), function ($field) use (&$fields) {
            $fields[] = $field;
        });

        $this->assertEquals(array(
            array(
                'name' => 'foo',
            ),
            array(
                'name' => 'bar',
            ),
        ), $fields);
    }
}
