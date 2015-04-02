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

use Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash;
use Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\Element;

/**
 * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash
 * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\Element
 */
class OrderedHashTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var OrderedHash
     */
    protected $hash;

    public function setUp()
    {
        parent::setUp();

        $hash = array(
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
            'stephanie' => 'Stephanie',
            'sara' => 'Sara',
            'sue' => 'Sue',
        );
        $this->hash = new OrderedHash($hash);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::add
     */
    public function testAdd_HashIsEmpty_BeforeIsNull_ElementIsInsertedAsBothTheHeadAndTheTail()
    {
        $key = 'stacy';
        $hash = new OrderedHash();
        $hash->add(null, $key, 'Stacy');

        $this->assertEquals($key, $hash->bottom()->getKey(), 'Stacy should be the head');
        $this->assertEquals($key, $hash->top()->getKey(), 'Stacy should be the tail');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::add
     */
    public function testAdd_BeforeIsAnElement_ElementIsInsertedAfterBefore()
    {
        $key = 'stacy';
        $before = $this->hash->bottom();
        $after = $before->getAfter();
        $this->hash->add($before, $key, 'Stacy');

        $this->assertEquals($key, $before->getAfter()->getKey(), 'Stacy should come after the head');
        $this->assertEquals(
            $key,
            $after->getBefore()->getKey(),
            'Stacy should come between the head and the element that used to follow the head'
        );
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::add
     * @expectedException OutOfRangeException
     */
    public function testAdd_KeyIsNotAStringOrInteger_ThrowsException()
    {
        $this->hash->add($this->hash->top(), false, 'Foo');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::add
     * @expectedException RuntimeException
     */
    public function testAdd_KeyIsNotUnique_ThrowsException()
    {
        $this->hash->add($this->hash->top(), 'sally', 'Foo');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::bottom
     */
    public function testBottom_ReturnsTheFirstElementInTheHash()
    {
        $hash = $this->hash->toArray();
        $key = array_shift(array_keys($hash));
        $value = array_shift(array_values($hash));
        $head = $this->hash->bottom();

        $this->assertEquals($key, $head->getKey());
        $this->assertEquals($value, $head->getValue());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::count
     */
    public function testCount_ReturnsTheNumberOfElementsInTheHash()
    {
        $this->assertCount(6, $this->hash, 'OrderedHash should implement the Countable interface');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::isEmpty
     */
    public function testIsEmpty_ReturnsTrue()
    {
        $hash = new OrderedHash();

        $this->assertTrue($hash->isEmpty());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::isEmpty
     */
    public function testIsEmpty_ReturnsFalse()
    {
        $this->assertFalse($this->hash->isEmpty());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::key
     */
    public function testKey_CurrentIsTheHead_ReturnsTheValueOfHead()
    {
        $this->hash->rewind();

        $this->assertEquals($this->hash->bottom()->getKey(), $this->hash->key());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::key
     */
    public function testKey_CurrentIsNull_ReturnsNull()
    {
        $this->hash->rewind();
        $this->hash->prev();

        $this->assertNull($this->hash->key());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::move
     */
    public function testMove_KeyIsNotFound_NoChangesAreMade()
    {
        $expected = json_encode($this->hash->toArray());

        $this->hash->move('foo', $this->hash->bottom());

        $actual = json_encode($this->hash->toArray());
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::move
     */
    public function testMove_MovesTheHeadToTheMiddle()
    {
        $element = $this->hash->bottom();
        $before = $this->hash['stephanie'];
        $this->hash->move($element->getKey(), $before);

        $this->assertEquals($element->getKey(), $before->getAfter()->getKey());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::move
     */
    public function testMove_MovesTheHeadToTheEnd()
    {
        $element = $this->hash->bottom();
        $after = $element->getAfter();
        $this->hash->move($element->getKey(), $this->hash->top());

        $this->assertEquals($this->hash->top()->getKey(), $element->getKey(), 'The former head should be the tail');
        $this->assertEquals(
            $after->getKey(),
            $this->hash->bottom()->getKey(),
            'The element following the former head should be the head'
        );
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::move
     */
    public function testMove_MovesTheTailToTheMiddle()
    {
        $element = $this->hash->top();
        $before = $this->hash['sally'];
        $this->hash->move($element->getKey(), $before);

        $this->assertEquals($element->getKey(), $before->getAfter()->getKey());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::move
     */
    public function testMove_MovesTheTailToTheBeginning()
    {
        $element = $this->hash->top();
        $before = $element->getBefore();
        $this->hash->move($element->getKey(), null);

        $this->assertEquals($this->hash->bottom()->getKey(), $element->getKey(), 'The former tail should be the head');
        $this->assertEquals(
            $before->getKey(),
            $this->hash->top()->getKey(),
            'The element preceding the former tail should be the tail'
        );
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::move
     */
    public function testMove_MovesAnInteriorElementToTheBeginning()
    {
        $key = 'sally';
        $this->hash->move($key, null);

        $this->assertEquals($key, $this->hash->bottom()->getKey());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::move
     */
    public function testMove_MovesAnInteriorElementToAnotherInteriorLocation()
    {
        $key = 'sara';
        $before = $this->hash['suzy'];
        $this->hash->move($key, $before);

        $this->assertEquals($key, $before->getAfter()->getKey());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::move
     */
    public function testMove_MovesAnInteriorElementToTheEnd()
    {
        $key = 'sally';
        $this->hash->move($key, $this->hash->top());

        $this->assertEquals($key, $this->hash->top()->getKey());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::move
     */
    public function testMove_BeforeIsAnElementNotFoundInTheHashButItsKeyIsFoundInTheHash_ElementIsMovedToFollowTheElementWithThatKey()
    {
        $key = 'sally';
        $element = $this->hash->top();
        $before = new Element($key, 'Sally In A Different Hash');
        $this->hash->move($element->getKey(), $before);

        $this->assertEquals($element->getKey(), $this->hash[$key]->getAfter()->getKey());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::move
     * @expectedException OutOfRangeException
     */
    public function testMove_KeyIsNotAStringOrInteger_ThrowsException()
    {
        $this->hash->move(true, $this->hash->top());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::rewind
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::valid
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::current
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::next
     */
    public function testNext_IteratesFromHeadToTail()
    {
        $hash = $this->hash->toArray();
        $keys = array_keys($hash);
        $values = array_values($hash);

        $this->hash->rewind();

        while ($this->hash->valid()) {
            $key = array_shift($keys);
            $value = array_shift($values);
            $current = $this->hash->current();

            $this->assertEquals($key, $current->getKey());
            $this->assertEquals($value, $current->getValue());

            $this->hash->next();
        }

        $this->assertNull($this->hash->current(), 'Null should be returned when there are no more elements');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetExists
     */
    public function testOffsetExists_ReturnsFalse()
    {
        $this->assertFalse(isset($this->hash['foo']));
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetExists
     */
    public function testOffsetExists_ReturnsTrue()
    {
        $this->assertTrue(isset($this->hash['suzy']));
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetGet
     * @expectedException OutOfRangeException
     */
    public function testOffsetGet_KeyIsNotAStringOrInteger_ThrowsException()
    {
        $element = $this->hash[5.5];
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetGet
     */
    public function testOffsetGet_KeyIsNotInTheHash_ReturnsNull()
    {
        $this->assertNull($this->hash['foo']);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetGet
     */
    public function testOffsetGet_KeyIsInTheHash_ReturnsTheElement()
    {
        $this->assertNotNull($this->hash['suzy']);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetSet
     * @expectedException OutOfRangeException
     */
    public function testOffsetSet_KeyIsNotAStringOrInteger_ThrowsException()
    {
        $this->hash[5.5] = 'Foo';
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetSet
     */
    public function testOffsetSet_KeyIsNotInTheHash_ElementIsInsertedAtTheEnd()
    {
        $key = 'stacy';
        $this->hash[$key] = 'Stacy';

        $this->assertEquals($key, $this->hash->top()->getKey());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetSet
     */
    public function testOffsetSet_KeyIsInTheHash_UpdatesTheValueForTheElement()
    {
        $key = 'suzy';
        $this->hash[$key] = 'Suzanne';

        $this->assertEquals('Suzanne', $this->hash[$key]->getValue(), 'Suzy should have become Suzanne');
        $this->assertNotEquals($key, $this->hash->top()->getKey(), 'Suzy should not have moved to the end');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetUnset
     * @expectedException OutOfRangeException
     */
    public function testOffsetUnset_KeyIsNotAStringOrInteger_ThrowsException()
    {
        unset($this->hash[5.5]);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetUnset
     */
    public function testOffsetUnset_KeyIsNotFound_NothingIsRemoved()
    {
        $expected = json_encode($this->hash->toArray());

        unset($this->hash['foo']);

        $actual = json_encode($this->hash->toArray());
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetUnset
     */
    public function testOffsetUnset_RemovesAnInteriorElement()
    {
        $key = 'sally';
        $element = $this->hash[$key];
        $before = $element->getBefore();
        $after = $element->getAfter();
        unset($this->hash[$key]);

        $this->assertNull($this->hash[$key], 'The removed element should not be found');
        $this->assertEquals(
            $before->getKey(),
            $after->getBefore()->getKey(),
            'The element that followed the removed element should be linked to the element that preceded the removed element'
        );
        $this->assertEquals(
            $after->getKey(),
            $before->getAfter()->getKey(),
            'The element that preceded the removed element should be linked to the element that followed the removed element'
        );
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetUnset
     */
    public function testOffsetUnset_OnlyOneElementExistsAndItIsRemoved_TheHashIsEmpty()
    {
        $key = 'stacy';
        $hash = new OrderedHash();
        $hash->push($key, 'Stacy');
        unset($hash[$key]);

        $this->assertTrue($hash->isEmpty(), 'There should not be any elements in the hash');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetUnset
     */
    public function testOffsetUnset_TheCurrentElementIsRemoved_CurrentBecomesTheNextElement()
    {
        $key = 'sally';
        $element = $this->hash[$key];
        $after = $element->getAfter();

        $this->hash->rewind();

        while ($this->hash->valid()) {
            $current = $this->hash->current();

            if ($current->getKey() === $key) {
                // stop when sally is reached
                break;
            }

            $this->hash->next();
        }

        unset($this->hash[$key]);

        $this->assertEquals($after->getKey(), $this->hash->current()->getKey());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::pop
     * @expectedException RuntimeException
     */
    public function testPop_TheHashIsEmpty_ThrowsException()
    {
        $hash = new OrderedHash();

        $element = $hash->pop();
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::pop
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetUnset
     */
    public function testPop_RemovesTheTail()
    {
        $tail = $this->hash->top();
        $key = $tail->getKey();
        $before = $tail->getBefore();
        $element = $this->hash->pop();

        $this->assertNull($this->hash[$key], 'The popped element should not be found');
        $this->assertEquals(
            $before->getKey(),
            $this->hash->top()->getKey(),
            'The element that preceded the former tail should be the tail'
        );
        $this->assertEquals($key, $element->getKey(), 'The popped element should have been returned');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::fastForward
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::valid
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::current
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::prev
     */
    public function testPrev_IteratesFromTailToHead()
    {
        $hash = $this->hash->toArray();
        $keys = array_reverse(array_keys($hash));
        $values = array_reverse(array_values($hash));

        $this->hash->fastForward();

        while ($this->hash->valid()) {
            $key = array_shift($keys);
            $value = array_shift($values);
            $current = $this->hash->current();

            $this->assertEquals($key, $current->getKey());
            $this->assertEquals($value, $current->getValue());

            $this->hash->prev();
        }

        $this->assertNull($this->hash->current(), 'Null should be returned when there are no more elements');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::push
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::add
     */
    public function testPush_ElementIsInsertedAtTheEnd()
    {
        $key = 'stacy';
        $before = $this->hash->top();
        $this->hash->push($key, 'Stacy');

        $this->assertEquals($key, $before->getAfter()->getKey(), 'Stacy should come after the former tail');
        $this->assertEquals($key, $this->hash->top()->getKey(), 'Stacy should be the tail');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::shift
     * @expectedException RuntimeException
     */
    public function testShift_TheHashIsEmpty_ThrowsException()
    {
        $hash = new OrderedHash();

        $element = $hash->shift();
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::shift
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::offsetUnset
     */
    public function testShift_RemovesTheHead()
    {
        $head = $this->hash->bottom();
        $key = $head->getKey();
        $after = $head->getAfter();
        $element = $this->hash->shift();

        $this->assertNull($this->hash[$key], 'The unshifted element should not be found');
        $this->assertEquals(
            $after->getKey(),
            $this->hash->bottom()->getKey(),
            'The element that followed the former head should be the head'
        );
        $this->assertEquals($key, $element->getKey(), 'The unshifted element should have been returned');
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::toArray
     */
    public function testToArray_ReturnsAnArrayOfKeyValuePairsInTheOrderPrescribedByTheLinkedList()
    {
        $expected = array(
            'susan' => 'Susan',
            'suzy' => 'Suzy',
            'sally' => 'Sally',
            'stephanie' => 'Stephanie',
            'sara' => 'Sara',
            'sue' => 'Sue',
        );
        $actual = $this->hash->toArray();
        $this->assertEquals(json_encode($expected), json_encode($actual));
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::top
     */
    public function testTop_ReturnsTheLastElementInTheHash()
    {
        $hash = $this->hash->toArray();
        $key = array_pop(array_keys($hash));
        $value = array_pop(array_values($hash));
        $tail = $this->hash->top();

        $this->assertEquals($key, $tail->getKey());
        $this->assertEquals($value, $tail->getValue());
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::unshift
     * @covers Sugarcrm\Sugarcrm\Util\Arrays\OrderedHash\OrderedHash::add
     */
    public function testUnshift_ElementIsInsertedAtTheBeginning()
    {
        $key = 'stacy';
        $after = $this->hash->bottom();
        $this->hash->unshift($key, 'Stacy');

        $this->assertEquals($key, $after->getBefore()->getKey(), 'Stacy should come before the former head');
        $this->assertEquals($key, $this->hash->bottom()->getKey(), 'Stacy should be the head');
    }
}
