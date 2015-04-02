<?php

require_once 'modules/Users/authentication/SugarAuthenticate/SugarAuthenticate.php';
require_once 'modules/Users/authentication/SugarAuthenticate/SugarAuthenticateUser.php';

class SugarAuthenticateTest extends Sugar_PHPUnit_Framework_TestCase {

    /**
     * @var SugarAuthenticate
     */
    protected $authenticate;

    protected function setUp()
    {
        $this->authenticate = $this->getMockBuilder('SugarAuthenticate')
            ->setMethods(array('updateUserLastLogin', 'postLoginAuthenticate'))
            ->disableOriginalConstructor()
            ->getMock();

        $authUser = $this->getMock('SugarAuthenticateUser');
        $authUser->expects($this->once())
            ->method('loadUserOnLogin')
            ->will($this->returnValue(true));

        $this->authenticate->userAuthenticate = $authUser;
    }

    /**
     * @coversNothing
     * @group BR-1721
     */
    public function testLoginAuthenticateTriggersUpdateUserLastLogin()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $this->authenticate->expects($this->once())
            ->method('updateUserLastLogin');
        $this->authenticate->loginAuthenticate($user->user_name, '');
    }

    protected function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }
}
