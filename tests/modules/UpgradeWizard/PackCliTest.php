<?php

require_once __DIR__ . '/../../../modules/UpgradeWizard/pack_cli.php';

class PackCliTest extends PHPUnit_Framework_TestCase
{

    public function packUpgradeWizardCliProvider()
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
     * @dataProvider packUpgradeWizardCliProvider
     * @param $params
     * @param $expect
     */
    public function testPackUpgradeWizardCli($params, $expect)
    {
        $zip = $this->getMock('ZipArchive');
        $versionFile = __DIR__ . '/../../../modules/UpgradeWizard/version.json';
        $zip->expects($this->exactly(6))->method('addFile');
        packUpgradeWizardCli($zip, $params);

        $this->assertEquals(json_encode($expect), file_get_contents($versionFile));
        unlink($versionFile);
    }

    public function testPackCliPhp()
    {
        if (is_windows()) {
            $this->markTestSkipped('Skipping on Windows - PHP_BINDIR bug');
        }
        $result = exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../modules/UpgradeWizard/pack_cli.php');
        $this->assertEquals(
            "Use " . __DIR__ . "/../../../modules/UpgradeWizard/pack_cli.php name (no zip or phar extension) [sugarVersion [buildNumber]]",
            $result
        );
        if (ini_get('phar.readonly')) {
            $this->markTestSkipped('Disable phar.readonly to run this test');
        }
        $zip = tempnam('/tmp', 'test');
        exec(PHP_BINDIR . '/php ' . __DIR__ . '/../../../modules/UpgradeWizard/pack_cli.php ' . $zip);
        $this->assertTrue(file_exists($zip . '.zip'));
        unlink($zip);
    }
}
