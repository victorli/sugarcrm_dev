<?php

/**
 * @ticket 56259
 * @ticket PAT-580
 */
class Bug56259Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function testUserACLs()
    {
        $result = $GLOBALS['current_user']->get_list('', '', 0, 100, -1, 0);
        foreach($result['list'] as $bean) {
            if($bean->id == $GLOBALS['current_user']->id) continue;
            $bean->ACLFilterFields();
            $this->assertEmpty($bean->user_hash, "User hash not hidden");
        }
    }
}