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

require_once "modules/Mailer/SMTPProxy.php";

/**
 * @group email
 * @group mailer
 */
class SMTPProxyTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $logger;

    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
        $this->logger   = $GLOBALS["log"]; // save the original logger
    }

    public function tearDown()
    {
        $GLOBALS["log"] = $this->logger; // restore the original logger
        SugarTestHelper::tearDown();
    }

    public function testHello_ConnectedReturnsTrue_SendHelloReturnsTrue_HandleErrorDoesNotLogAnyErrors()
    {
        $GLOBALS["log"] = new SugarMockLogger();

        $mockSmtpProxy = $this->getMock("SMTPProxy", array("Connected", "SendHello"));
        $mockSmtpProxy->expects($this->any())
                      ->method("Connected")
                      ->will($this->returnValue(true));
        $mockSmtpProxy->expects($this->any())
                      ->method("SendHello")
                      ->will($this->returnValue(true));

        $actual = $mockSmtpProxy->Hello();
        $this->assertTrue($actual, "Hello should have run to completion without error.");

        $expected = 0;
        $actual   = $GLOBALS["log"]->getMessageCount();
        $this->assertEquals($expected, $actual, "The logger should not have any errors to log.");
    }

    public function testHello_ConnectedReturnsFalse_HelloProducesAnErrorWithoutAnErrorCode_HandleErrorLogsTheErrorWithLevelWarn()
    {
        // SMTPProxy::handleError should log a warning
        $GLOBALS["log"] = $this->getMock("SugarMockLogger", array("__call"));
        $GLOBALS["log"]->expects($this->once())
                       ->method("__call")
                       ->with($this->equalTo("warn"));

        $mockSmtpProxy = $this->getMock("SMTPProxy", array("Connected"));
        $mockSmtpProxy->expects($this->any())
                      ->method("Connected")
                      ->will($this->returnValue(false));

        $actual = $mockSmtpProxy->Hello();
        $this->assertFalse($actual, "Connected returned false so Hello should return false.");
    }

    public function testHello_ConnectedReturnsTrue_SendHelloProducesAnErrorWithAnErrorCode_HandleErrorLogsTheErrorWithLevelFatal()
    {
        // SMTPProxy::handleError should log a fatal 'error'
        $GLOBALS["log"] = $this->getMock("SugarMockLogger", array("__call"));
        $GLOBALS["log"]->expects($this->once())
                       ->method("__call")
                       ->with($this->equalTo("fatal"));

        $mockSmtpProxy = $this->getMock(
            "SMTPProxy",
            array(
                 "Connected",
                 "client_send",
                 "get_lines",
            )
        );
        $mockSmtpProxy->expects($this->any())
                      ->method("Connected")
                      ->will($this->returnValue(true));
        $mockSmtpProxy->expects($this->any())
                      ->method("client_send")
                      ->will($this->returnValue(true));
        $mockSmtpProxy->expects($this->any())
                      ->method("get_lines")
                      ->will($this->returnValue("501 Syntax error in parameters or arguments"));

        $actual = $mockSmtpProxy->Hello();
        $this->assertFalse($actual, "SendHello returned false so Hello should return false.");
    }
}
