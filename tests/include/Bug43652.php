<?php

require_once('include/externalAPI/Google/ExtAPIGoogle.php');


/**
 * @ticket 43652
 */
class Bug43652Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $fileData1;
    private $extAPI;

    public function setUp()
    {
        //Just need base class but its abstract so we use the google implementation for this test.
        $this->extAPI = new ExtAPIGoogle();
        $this->fileData1 = $GLOBALS['sugar_config']['upload_dir'] . DIRECTORY_SEPARATOR . 'unittest';
        file_put_contents($this->fileData1, "Unit test for mime type");
    }

    public function tearDown()
	{
        unlink($this->fileData1);
	}

    function _fileMimeProvider()
    {
        return array(
            array( array('name' => 'te.st.png','type' => 'img/png'),'img/png'),
            array( array('name' => 'test.jpg','type' => 'img/jpeg'),'img/jpeg'),
            array( array('name' => 'test.out','type' => 'application/octet-stream'),'application/octet-stream'),
            array( array('name' => 'test_again','type' => 'img/png'),'img/png'),
        );
    }

    /**
     * Test the getMime function for the use case where the mime type is already provided.
     *
     * @dataProvider _fileMimeProvider
     */
    public function testUploadFileWithMimeType($file_info, $expectedMime)
    {
        $uf = new UploadFile('');
        $mime = $uf->getMime($file_info);

        $this->assertEquals($expectedMime, $mime);
    }

    /**
     * Test file with no extension but with provided mime-type
     *
     * @return void
     */
    public function testUploadFileWithEmptyFileExtension()
    {
        $file_info = array('name' => 'test', 'type' => 'application/octet-stream', 'tmp_name' => $this->fileData1);
        $expectedMime = $this->extAPI->isMimeDetectionAvailable() ? 'text/plain' : 'application/octet-stream';
        $uf = new UploadFile('');
        $mime = $uf->getMime($file_info);
        $this->assertEquals($expectedMime, $mime);
    }


    /**
     * Test file with no extension and no provided mime-type
     *
     * @return void
     */
    public function testUploadFileWithEmptyFileExtenEmptyMime()
    {
        $file_info = array('name' => 'test','tmp_name' => $this->fileData1);
        $expectedMime = $this->extAPI->isMimeDetectionAvailable() ? 'text/plain' : 'application/octet-stream';
        $uf = new UploadFile('');
        $mime = $uf->getMime($file_info);
        $this->assertEquals($expectedMime, $mime);
    }
}