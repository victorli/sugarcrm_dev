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

require_once 'include/utils/file_utils.php';

class FileUtilsTests extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_testFileWithExt   = 'upload/sugartestfile.txt';
    protected $_testFileNoExt     = 'upload/noextfile';
    protected $_testFileNotExists = 'thisfilenamedoesnotexist.doc';

    public function setUp()
    {
        sugar_file_put_contents($this->_testFileWithExt, create_guid());
        sugar_file_put_contents($this->_testFileNoExt, create_guid());
    }

    public function tearDown()
    {
        unlink($this->_testFileWithExt);
        unlink($this->_testFileNoExt);
    }

    public function testIsMimeDetectableByFinfo()
    {
        $expected = function_exists('finfo_open') && function_exists('finfo_file') && function_exists('finfo_close');
        $actual   = mime_is_detectable_by_finfo();
        $this->assertEquals($expected, $actual, "FInfo check failed for mime detection");
    }

    public function testIsMimeDetectable()
    {
        $expected = (function_exists('finfo_open') && function_exists('finfo_file') && function_exists('finfo_close'))
                    ||
                    function_exists('mime_content_type') || function_exists('ext2mime');
        $actual = mime_is_detectable();
        $this->assertEquals($expected, $actual, "Check failed for mime detection");
    }

    public function testEmail2GetMime()
    {
        require_once 'modules/Emails/Email.php';
        $email = new Email();
        $expected = $email->email2GetMime($this->_testFileWithExt);
        $actual = $this->_getDefaultMimeType();
        $this->assertEquals($expected, $actual, "Email bean returned $actual but was expected $expected");
    }

    public function testDownloadFileGetMimeType()
    {
        require_once 'include/download_file.php';
        $dl = new DownloadFile();

        // Assert #1 file with extension
        $expected = $this->_getDefaultMimeType();
        $actual   = $dl->getMimeType($this->_testFileWithExt);
        $this->assertEquals($expected, $actual, "Download File mime getter with extension returned $actual but expected $expected");

        // Assert #2 file with no extension
        $actual = $dl->getMimeType($this->_testFileNoExt);
        $this->assertEquals($expected, $actual, "Download File mime getter without extension returned $actual but expected $expected");

        // Assert #3 nonexistent file
        $condition = $dl->getMimeType($this->_testFileNotExists);
        $this->assertFalse($condition, "Nonexistent file mime getter expected (bool) FALSE but returned $condition");
    }

    public function testUploadFileGetSoapMime()
    {
        require_once 'include/upload_file.php';
        $ul = new UploadFile();

        // Assert #1 file with extension
        $expected = $this->_getDefaultMimeType();
        $actual   = $ul->getMimeSoap($this->_testFileWithExt);
        $this->assertEquals($expected, $actual, "Upload File SOAP mime getter with extension returned $actual but expected $expected");

        // Assert #2 file with no extension
        $actual = $ul->getMimeSoap($this->_testFileNoExt);
        $this->assertEquals($expected, $actual, "Upload File SOAP mime getter without extension returned $actual but expected $expected");

        // Assert #3 nonexistent file
        $actual = $ul->getMimeSoap($this->_testFileNotExists);
        $this->assertEquals('application/octet-stream', $actual,  "Nonexistent Upload File SOAP mime getter expected 'application/octet-stream' but returned $actual");
    }

    public function testUploadFileGetMime()
    {
        require_once 'include/upload_file.php';
        $ul = new UploadFile();

        // Assert #1 - file with extension and type set
        $files = array('name' => $this->_testFileWithExt, 'type' => 'text/plain');
        $actual = $ul->getMime($files);
        $this->assertEquals('text/plain', $actual, "Upload File Get Mime should have returned 'text/plain' but returned $actual");

        // Assert #2 - file without extension and type set to octet-stream
        $files = array('name' => $this->_testFileNoExt, 'type' => 'application/octet-stream', 'tmp_name' => $this->_testFileNoExt);
        $actual = $ul->getMime($files);
        $expected = $this->_getDefaultMimeType();
        $this->assertEquals($expected, $actual, "Upload File Get Mime on file with no extension should have returned $expected but returneded $actual");

        // Assert #3 - nonexistent file
        $files = array('name' => $this->_testFileNotExists, 'type' => 'application/octet-stream', 'tmp_name' => $this->_testFileNotExists);
        $actual = $ul->getMime($files);
        $this->assertEquals('application/octet-stream', $actual, "Upload File Get Mime on nonexistent file should have returned 'application/octet-stream' but returned $actual");
    }

    protected function _getDefaultMimeType()
    {
        $mime = 'text/plain';

        if (!mime_is_detectable())
        {
            $mime = 'application/octet-stream';
        }

        return $mime;
    }
}