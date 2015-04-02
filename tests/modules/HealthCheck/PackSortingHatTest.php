<?php

require_once __DIR__ . '/../../../modules/HealthCheck/pack_sortinghat.php';

class PackSortingHatTest extends PHPUnit_Framework_TestCase
{

    public function healthCheckPackProvider()
    {
        return array(
            array(
                array(
                    'version' => '1.2.3.4'
                ),
                array(
                    'version' => '1.2.3.4',
                    'build' => '998'
                ),
            ),
            array(
                array(),
                array(
                    'version' => '7.5.0.0',
                    'build' => '998'
                ),
            ),
            array(
                array(
                    'build' => '1.2.3.4'
                ),
                array(
                    'version' => '7.5.0.0',
                    'build' => '1.2.3.4'
                ),
            )
        );
    }

    /**
     * @dataProvider healthCheckPackProvider
     * @param $params
     * @param $expect
     */
    public function testHealthCheckPack($params, $expect)
    {
        $zip = $this->getMock('ZipArchive');
        $versionFile = __DIR__ . '/../../../modules/HealthCheck/Scanner/version.json';
        $zip->expects($this->exactly(6))->method('addFile');
        packSortingHat($zip, $params);

        $this->assertEquals(json_encode($expect), file_get_contents($versionFile));
        unlink($versionFile);
    }

    public function testPackSortingHatPhp()
    {
        $result = exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../modules/HealthCheck/pack_sortinghat.php');
        $this->assertEquals(
            "Use " . __DIR__ . "/../../../modules/HealthCheck/pack_sortinghat.php healthcheck.phar [sugarVersion [buildNumber]]",
            $result
        );
        $zip = tempnam('/tmp', 'phar') . '.phar';
        exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../modules/HealthCheck/pack_sortinghat.php ' . $zip);
        $this->assertTrue(file_exists($zip));
        unlink($zip);
    }
}