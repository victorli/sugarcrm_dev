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


/**
 * @covers SugarCleaner
 */
class SugarCleanerTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider cleanHtmlProvider
     */
    public function testCleanHtml($args, $expected)
    {
        $actual = SugarCleaner::cleanHtml($args[0], $args[1]);

        $this->assertEquals($expected, $actual, 'Html did not get cleaned as expected');

    }

    public static function cleanHtmlProvider()
    {

        return array(
            /* script tags should be removed */
            array(
                array(" World &lt;script&gt;alert('Hello');&lt;/script&gt;", true),
                " World "
            ),
            /* double encoded script tags should be removed */
            array(
                array("Hello &amp;lt;script&amp;gt;alert(&#039;Hi&#039;);&amp;lt;/script&amp;gt; World", true),
                "Hello  World"
            ),
            /* Non harmful tags like bold tags should be allowed */
            array(
                array("&lt;b&gt;Hello&lt;/b&gt;", true),
                "&lt;b&gt;Hello&lt;/b&gt;"
            ),
            /* Normal text without html should pass through unchanged */
            array(
                array("Hello", true),
                "Hello"
            ),
            /* Try one test with false i.e., Not entity encoded */
            array(
                array("<b>Hello</b>", false),
                "<b>Hello</b>"
            ),
        );
    }
}
