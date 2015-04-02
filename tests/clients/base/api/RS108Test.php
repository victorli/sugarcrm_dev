<?php

require_once 'clients/base/api/FileTempApi.php';

/**
 * RS-108
 * Prepare FileTemp Api
 */
class RS108Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var string */
    protected $file = '';

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->service = SugarTestRestUtilities::getRestServiceMock();

        SugarAutoLoader::ensureDir(UploadStream::path("upload://tmp/"));
        $this->file = UploadStream::path("upload://tmp/") . create_guid();
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile($this->file);
        $img = imagecreate(1, 1);
        imagecolorallocate($img, 0, 0, 0);
        imagepng($img, $this->file);
        imagedestroy($img);
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * 3rd argument of saveFilePost method should be true for Temp File
     */
    public function testSaveTempImagePost()
    {
        $api = $this->getMock('FileTempApi', array('saveFilePost'));
        $api->expects($this->once())->method('saveFilePost')->with($this->anything(), $this->anything(), $this->equalTo(true));
        $api->saveTempImagePost($this->service, array());
    }

    /**
     * On success fileResponse method of RestService should be called with argument which is equal to file path
     */
    public function testGetTempImage()
    {
        $service = $this->getMock('RestService', array('fileResponse'));
        $service->expects($this->once())->method('fileResponse')->with($this->equalTo($this->file));
        $api = new FileTempApi();
        $api->getTempImage($service, array(
                'module' => 'Users',
                'record' => $GLOBALS['current_user']->id,
                'field' => 'image',
                'temp_id' => basename($this->file),
            ));
    }

    /**
     * We should get exception if field isn't passed
     *
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testGetTempImageWithoutField()
    {
        $api = new FileTempApi();
        $api->getTempImage($this->service, array());
    }

    /**
     * We should get exception if file doesn't exist
     *
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testGetTempImageWithoutTempId()
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
        $api = new FileTempApi();
        $api->getTempImage($this->service, array(
                'module' => 'Users',
                'record' => $GLOBALS['current_user']->id,
                'field' => 'image',
                'temp_id' => basename($this->file),
            ));
    }
}
