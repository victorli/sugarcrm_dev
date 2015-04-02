<?php

require_once('include/SugarQuery/Builder/Field/Condition.php');

class SugarQuery_Builder_Field_ConditionTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Test asserts result of quoteValue with defferent defs
     *
     * @dataProvider getData
     *
     * @param array $def
     * @param string $value
     * @param string $expected
     */
    public function testQuoteValue($def, $value, $expected)
    {
        $query = new SugarQuery();
        $condition = new SugarQuery_Builder_Field_Condition('id', $query);
        $condition->def = $def;
        $actual = $condition->quoteValue($value);
        $this->assertEquals($expected, $actual);
    }

    public function getData()
    {
        return array(
            array(
                array(),
                '0',
                $GLOBALS['db']->quoted('0'),
            ),
            array(
                array(
                    'type' => 'int',
                ),
                '',
                '0',
            ),
            array(
                array(
                    'type' => 'int',
                ),
                0,
                '0',
            ),
            array(
                array(
                    'type' => 'int',
                ),
                '0',
                '0',
            ),
            array(
                array(
                    'type' => 'bool',
                ),
                '',
                '0',
            ),
            array(
                array(
                    'type' => 'bool',
                ),
                0,
                '0',
            ),
            array(
                array(
                    'type' => 'bool',
                ),
                '0',
                '0',
            ),
            array(
                array(
                    'type' => 'varchar',
                ),
                '',
                $GLOBALS['db']->quoted(''),
            ),
            array(
                array(
                    'type' => 'varchar',
                ),
                '0',
                $GLOBALS['db']->quoted('0'),
            ),
        );
    }
}
