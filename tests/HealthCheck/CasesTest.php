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

require_once 'HealthCheckScanner.php';

class HealthCheckCasesTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $currentDirectory = '';
    protected $currentPath = '';
    protected $cachedPath = '';

    /** @var HealthCheckScannerCasesTestMock */
    protected $scanner = null;

    public function setUp()
    {
        parent::setUp();
        $this->currentDirectory = getcwd();
        $this->currentPath = ini_get('include_path');
        do {
            $this->cachedPath = sugar_cached(md5(microtime(true)));
        } while (is_dir($this->cachedPath));
        sugar_mkdir($this->cachedPath);
    }

    public function tearDown()
    {
        chdir($this->currentDirectory);
        $this->currentDirectory = '';

        rmdir_recursive($this->cachedPath);
        $this->cachedPath = '';

        ini_set('include_path', $this->currentPath);
        $this->currentPath = '';

        if ($this->scanner instanceof HealthCheckScannerCasesTestMock) {
            $this->scanner->tearDown();
        }

        parent::tearDown();
    }

    /**
     * Test uses temp directory ($this->cachedPath) for files which need fo test.
     * Also it changes include_path and current directory to the temp directory, so
     * HealthCheckScanner will try to load test files first and only if they're not present it
     * loads sugar's files.
     *
     * @dataProvider getCases
     */
    public function testCase($code, $case)
    {
        if (!is_dir(__DIR__ . '/cases/' . $case)) {
            $this->markTestIncomplete('HealthCheck code ' . $code . ' case ' . $case . ' is not covered');
        }

        $this->scanner = $this->getScanner($case);
        if ($this->scanner->skip) {
            $this->markTestIncomplete('HealthCheck code ' . $code . ' case ' . $case . ' is skipped by itself');
        }

        if (is_dir(__DIR__ . '/cases/' . $case . '/sugarcrm')) {
            copy_recursive(__DIR__ . '/cases/' . $case . '/sugarcrm', $this->cachedPath);
        }
        ini_set('include_path', realpath($this->cachedPath) . PATH_SEPARATOR . ini_get('include_path'));
        chdir($this->cachedPath);

        $this->scanner->scan();

        $detectedStatuses = array();
        foreach ($this->scanner->getStatusLog() as $bucket) {
            foreach ($bucket as $log) {
                $detectedStatuses[] = $log['code'];
            }
        }
        $detectedStatuses = array_unique($detectedStatuses);
        if ($this->scanner->not) {
            $this->assertNotContains($code, $detectedStatuses, 'Requested status is not detected');
        } else {
            $this->assertContains($code, $detectedStatuses, 'Requested status is not detected');
        }
    }

    /**
     * @param $case
     * @return HealthCheckScannerCasesTestMock
     */
    protected function getScanner($case)
    {
        $scanner = 'HealthCheckScannerCasesTestMock';
        if (is_file(__DIR__ . '/cases/' . $case . '/HealthCheckScanner.php')) {
            require_once __DIR__ . '/cases/' . $case . '/HealthCheckScanner.php';
            if (class_exists('S_' . $case . '_' . $scanner)) {
                $scanner = 'S_' . $case . '_' . $scanner;
            }
        }

        return new $scanner();
    }

    public static function getCases()
    {
        $cases = array();

        foreach (SugarTestReflection::getProtectedValue(new HealthCheckScannerMeta(), 'meta') as $code => $data) {
            $iterator = new DirectoryIterator(__DIR__ . '/cases');
            /** @var DirectoryIterator $pointer */
            $isUpdated = false;
            foreach ($iterator as $pointer) {
                if (!$pointer->isDir() || $pointer->isDot()) {
                    continue;
                }
                if (!preg_match('/^\d+(_[\w\d]+)?$/', $pointer->getFilename())) {
                    continue;
                }
                if ($pointer->getFilename() != $code && substr($pointer->getFilename(), 0, strlen($code) + 1) != $code . '_') {
                    continue;
                }
                $cases['CASE_' . $pointer->getFilename()] = array($code, $pointer->getFilename());
                $isUpdated = true;
            }
            if (!$isUpdated) {
                $cases['CASE_' . $code] = array($code, $code);
            }
        }

        return $cases;
    }
}
