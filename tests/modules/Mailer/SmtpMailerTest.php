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

require_once "modules/Mailer/SmtpMailer.php";

/**
 * @group email
 * @group mailer
 */
class SmtpMailerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp("current_user");
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }
    
    public function testGetMailTransmissionProtocol_ReturnsSmtp()
    {
        $mailer   = new SmtpMailer(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]));
        $expected = SmtpMailer::MailTransmissionProtocol;
        $actual   = $mailer->getMailTransmissionProtocol();
        self::assertEquals(
            $expected,
            $actual,
            "The SmtpMailer should have {$expected} for its mail transmission protocol"
        );
    }

    public function testClearRecipients_ClearToAndBccButNotCc()
    {
        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "clearRecipientsTo",
                 "clearRecipientsCc",
                 "clearRecipientsBcc"
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->expects(self::once())
            ->method("clearRecipientsTo");

        $mockMailer->expects(self::never())
            ->method("clearRecipientsCc");

        $mockMailer->expects(self::once())
            ->method("clearRecipientsBcc");

        $mockMailer->clearRecipients(true, false, true);
    }

    public function testSend_PHPMailerSmtpConnectThrowsException_ConnectToHostCatchesAndThrowsMailerException()
    {
        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("SmtpConnect"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("SmtpConnect")
            ->will(self::throwException(new phpmailerException()));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        // connectToHost should fail between transferConfigurations and transferHeaders

        $mockMailer->expects(self::never())
            ->method("transferHeaders");

        $mockMailer->expects(self::never())
            ->method("transferRecipients");

        $mockMailer->expects(self::never())
            ->method("transferBody");

        $mockMailer->expects(self::never())
            ->method("transferAttachments");

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_PHPMailerSetFromThrowsException_TransferHeadersThrowsMailerException()
    {
        $packagedEmailHeaders = array(
            EmailHeaders::From => array(
                "foo@bar.com",
                null,
            ),
        );
        $mockEmailHeaders     = self::getMock("EmailHeaders", array("packageHeaders"));

        $mockEmailHeaders->expects(self::once())
            ->method("packageHeaders")
            ->will(self::returnValue($packagedEmailHeaders));

        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("SetFrom"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("SetFrom")
            ->will(self::throwException(new phpmailerException()));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->setHeaders($mockEmailHeaders);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        // transferHeaders should fail between connectToHost and transferRecipients

        $mockMailer->expects(self::never())
            ->method("transferRecipients");

        $mockMailer->expects(self::never())
            ->method("transferBody");

        $mockMailer->expects(self::never())
            ->method("transferAttachments");

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_PHPMailerAddReplyToReturnsFalse_TransferHeadersThrowsMailerException()
    {
        $packagedEmailHeaders = array(
            EmailHeaders::ReplyTo => array(
                "foo@bar.com",
                null,
            ),
        );
        $mockEmailHeaders     = self::getMock("EmailHeaders", array("packageHeaders"));

        $mockEmailHeaders->expects(self::once())
            ->method("packageHeaders")
            ->will(self::returnValue($packagedEmailHeaders));

        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("AddReplyTo"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("AddReplyTo")
            ->will(self::returnValue(false));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->setHeaders($mockEmailHeaders);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        // transferHeaders should fail between connectToHost and transferRecipients

        $mockMailer->expects(self::never())
            ->method("transferRecipients");

        $mockMailer->expects(self::never())
            ->method("transferBody");

        $mockMailer->expects(self::never())
            ->method("transferAttachments");

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_PHPMailerAddAttachmentThrowsException_TransferAttachmentsThrowsMailerException()
    {
        $mockLocale = self::getMock("Localization", array("translateCharset"));
        $mockLocale->expects(self::any())
            ->method("translateCharset")
            ->will(self::returnValue("foobar")); // the filename that Localization::translateCharset will return

        $mailerConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);
        $mailerConfiguration->setLocale($mockLocale);

        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("AddAttachment"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("AddAttachment")
            ->will(self::throwException(new phpmailerException()));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
            ),
            array($mailerConfiguration)
        );

        $attachment = new Attachment("/foo/bar.txt");
        $mockMailer->addAttachment($attachment);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferBody")
            ->will(self::returnValue(true));

        // transferAttachments should fail after transferBody and before PHPMailer's Send is called

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_PHPMailerAddEmbeddedImageReturnsFalse_TransferAttachmentsThrowsMailerException()
    {
        $mockLocale = self::getMock("Localization", array("translateCharset"));
        $mockLocale->expects(self::any())
            ->method("translateCharset")
            ->will(self::returnValue("foobar")); // the filename that Localization::translateCharset will return

        $mailerConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);
        $mailerConfiguration->setLocale($mockLocale);

        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("AddEmbeddedImage"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("AddEmbeddedImage")
            ->will(self::returnValue(false));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
            ),
            array($mailerConfiguration)
        );

        $embeddedImage = new EmbeddedImage("foobar", "/foo/bar.txt");
        $mockMailer->addAttachment($embeddedImage);

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferBody")
            ->will(self::returnValue(true));

        // transferAttachments should fail after transferBody and before PHPMailer's Send is called

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_PHPMailerSendThrowsException_SendCatchesItAndThrowsMailerException()
    {
        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("Send"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("Send")
            ->will(self::throwException(new phpmailerException()));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferHeaders")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferBody")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferAttachments")
            ->will(self::returnValue(true));

        self::setExpectedException("MailerException");
        $mockMailer->send();
    }

    public function testSend_AllMethodCallsAreSuccessful_NoExceptionsThrown()
    {
        $mockPhpMailerProxy = self::getMock("PHPMailerProxy", array("Send"));

        $mockPhpMailerProxy->expects(self::once())
            ->method("Send")
            ->will(self::returnValue(true));

        $mockMailer = self::getMock(
            "SmtpMailer",
            array(
                 "generateMailer",
                 "transferConfigurations",
                 "connectToHost",
                 "transferHeaders",
                 "transferRecipients",
                 "transferBody",
                 "transferAttachments",
            ),
            array(new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]))
        );

        $mockMailer->expects(self::once())
            ->method("generateMailer")
            ->will(self::returnValue($mockPhpMailerProxy));

        $mockMailer->expects(self::once())
            ->method("transferConfigurations")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("connectToHost")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferHeaders")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferRecipients")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferBody")
            ->will(self::returnValue(true));

        $mockMailer->expects(self::once())
            ->method("transferAttachments")
            ->will(self::returnValue(true));

        $mockMailer->send();
    }
}
