<?php

require_once 'include/SugarQuery/Compiler/SQL.php';

class SugarQuery_Compiler_SQLTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @param SugarBean $bean
     * @param array $fields
     *
     * @dataProvider getData
     */
    public function testCompileSelect($bean, $fields)
    {
        $compiler = new SugarQuery_Compiler_SQL($GLOBALS['db']);
        $query = new SugarQuery();
        $query->from(new Contact());
        $query->select($fields);
        $rc = new ReflectionObject($compiler);

        $sugarQuery = $rc->getProperty('sugar_query');
        $sugarQuery->setAccessible(true);
        $sugarQuery->setValue($compiler, new SugarQuery());

        $compileFrom = $rc->getMethod('compileFrom');
        $compileFrom->setAccessible(true);
        $compileFrom->invokeArgs($compiler, array($bean));

        $compileSelect = $rc->getMethod('compileSelect');
        $compileSelect->setAccessible(true);
        $result = $compileSelect->invokeArgs($compiler, array($query->select));
        $result = explode(',', $result);
        $actual = array();
        foreach ($result as $field) {
            $field = explode(' ', trim($field));
            $field = end($field);
            $this->assertNotContains($field, $actual);
            $actual[] = $field;
        }
        $this->assertNotEmpty($actual);
    }

    public static function getData()
    {
        return array(
            // contacts.id should be removed because it's selected by contacts.*
            array(
                new Contact(),
                array(
                    array('contacts.id', 'id'),
                    'contacts.id',
                    'contacts.*',
                ),
                'contacts.*',
            ),
            // first_name, last_name, salutation, title from full_name should be ignored because they're already selected
            array(
                new Contact(),
                array(
                    'first_name',
                    'contacts.first_name',
                    'contacts.last_name',
                    'contacts.salutation',
                    'contacts.title',
                    'full_name',
                ),
            ),
            // we should be able select the same field with different aliases
            array(
                new Contact(),
                array(
                    array('first_name', 'a1'),
                    'first_name',
                ),
            ),
            // account.id should be ignored because we already selected id from contact, maybe we need to log error here
            array(
                new Contact(),
                array(
                    'contacts.id',
                    array('accounts.id', 'id'),
                ),
            ),
        );
    }

    /**
     * @dataProvider compileConditionProvider
     */
    public function testCompileCondition($input, $expected)
    {
        $query = new SugarQuery();

        /** @var SugarQuery_Builder_Where $where */
        $where = $this->getMockBuilder('SugarQuery_Builder_Where')
            ->setMethods(array('dummy'))
            ->disableOriginalConstructor()
            ->getMock();
        $where->query = $query;
        $input($where);
        $condition = array_shift($where->conditions);
        $condition->field->table = 't';

        $compiler = new SugarQuery_Compiler_SQL($GLOBALS['db']);
        SugarTestReflection::setProtectedValue($compiler, 'sugar_query', $query);
        $sql = SugarTestReflection::callProtectedMethod($compiler, 'compileCondition', array($condition));
        $sql = trim($sql);

        $this->assertContains($expected, $sql);
    }

    public static function compileConditionProvider()
    {
        return array(
            array(
                function (SugarQuery_Builder_Where $where) {
                    $where->contains('foo', array('bar', 'baz'));
                },
                "(t.foo LIKE 'bar' OR t.foo LIKE 'baz')"
            ),
            array(
                function (SugarQuery_Builder_Where $where) {
                    $where->notContains('foo', array('bar', 'baz'));
                },
                "(t.foo NOT LIKE 'bar' AND t.foo NOT LIKE 'baz' OR t.foo IS NULL)"
            ),
        );
    }

    /**
     * Test addition of order stability column
     *
     * @param array $args Arguments for SugarQuery_Compiler_SQL::applyOrderByStability
     * @param string $expColumn Expected stability column name to be added
     *
     * @covers SugarQuery_Compiler_SQL::applyOrderByStability
     * @group unit
     * @dataProvider dataProviderTestApplyOrderByStability
     */
    public function testApplyOrderByStability($args, $expColumn)
    {
        // SUT
        $compiler = $this->getMockBuilder('SugarQuery_Compiler_SQL')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        // Mock SugarQuery for SUT
        $query = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->getMock();

        SugarTestReflection::setProtectedValue($compiler, 'sugar_query', $query);

        $result = SugarTestReflection::callProtectedMethod(
            $compiler,
            'applyOrderByStability',
            $args
        );

        // Test last element in result
        $added = array_pop($result);
        $this->assertInstanceOf('SugarQuery_Builder_Orderby', $added);
        $this->assertEquals(
            $expColumn,
            $added->column->field,
            'Incorrect column used for order stability'
        );

    }

    public function dataProviderTestApplyOrderByStability()
    {
        $mockOrderBy = $this->getMockBuilder('SugarQuery_Builder_Orderby')
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            array(
                array(
                    array(),
                    'fieldx',
                ),
                'fieldx'
            ),
            array(
                array(
                    array(),
                ),
                'id',
            ),
            array(
                array(
                    array($mockOrderBy),
                    'fieldy',
                ),
                'fieldy',
            ),
        );
    }

    /**
     * Test invocation of `ORDER BY` stability based on db capability
     *
     * @param boolean $orderByStability Apply order stability
     * @param boolean $capability DBManager order_stability capability
     * @param boolean $expectedApply Invocation expectation to apply order stability in `ORDER BY`
     *
     * @covers SugarQuery_Compiler_SQL::compileOrderBy
     * @group unit
     * @dataProvider dataProviderTestCompileOrderByStability
     */
    public function testCompileOrderByStability($orderByStability, $capability, $expectedApply)
    {
        // SUT
        $compiler = $this->getMockBuilder('SugarQuery_Compiler_SQL')
            ->disableOriginalConstructor()
            ->setMethods(array('applyOrderByStability'))
            ->getMock();

        // DBManager Mock
        $db = $this->getMockBuilder('DBManager')
            ->disableOriginalConstructor()
            ->setMethods(array('supports'))
            ->getMockForAbstractClass();

        $db->expects($this->any())
            ->method('supports')
            ->with($this->equalTo('order_stability'))
            ->will($this->returnValue($capability));

        SugarTestReflection::setProtectedValue($compiler, 'db', $db);

        $expected = $expectedApply ? $this->once() : $this->never();
        $compiler->expects($expected)
            ->method('applyOrderByStability')
            ->will($this->returnValue(array()));

        // Execute test call
        SugarTestReflection::callProtectedMethod(
            $compiler,
            'compileOrderBy',
            array(array(), $orderByStability)
        );

    }

    public function dataProviderTestCompileOrderByStability()
    {
        return array(
            array(true, false, true),
            array(true, true, false),
            array(false, false, false),
            array(false, true, false),
        );
    }
}
