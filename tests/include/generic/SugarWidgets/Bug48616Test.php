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

require_once "include/generic/LayoutManager.php";
require_once "include/generic/SugarWidgets/SugarWidgetFielddatetime.php";

class Bug48616Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('timedate');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Check if expandDate works properly when 'Today' macro is passed
     * instead of a date string
     */
    public function testExpandDateToday()
    {
        global $timedate;
        $widget = new SugarWidgetFieldDateTime48616Mock(new LayoutManager());

        $result = $widget->expandDate('Today');

        $this->assertContains(
            $timedate->asDbDate($timedate->getNow(true)),
            $result,
            "'Today' macro was not processed properly by expandDate()"
        );
    }
}

class SugarWidgetFieldDateTime48616Mock extends SugarWidgetFieldDateTime
{
    public function expandDate($date)
    {
        return parent::expandDate($date);
    }
}
