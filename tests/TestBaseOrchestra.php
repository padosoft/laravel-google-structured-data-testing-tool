<?php

namespace Padosoft\Laravel\Google\StructuredDataTestingTool\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use Padosoft\Test\traits\ReflectionTestable;

/**
 * Class TestBaseOrchestra for Laravel Test
 * @package Padosoft\Laravel\Google\StructuredDataTestingTool\Test
 */
class TestBaseOrchestra extends Orchestra
{
    use ReflectionTestable;

    /**
     * @var string
     */
    public $configBaseDir = __DIR__ . '/../src/config/';

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->setAllPackageConfig();
    }

    /**
     *
     */
    protected function setAllPackageConfig()
    {
        if ($this->configBaseDir == '' || !is_dir($this->configBaseDir)) {
            return;
        }
        foreach (glob($this->configBaseDir . "*.php") as $filename) {
            $this->setPackageConfig($filename);
        }
    }

    /**
     * @param $filename
     * @return bool
     */
    protected function setPackageConfig($filename)
    {
        if (!file_exists($filename)) {
            return false;
        }
        $cfg = require $filename;

        $filenameWithOutExt = substr($filename, strrpos($filename, '/') + 1, -4);

        foreach ($cfg as $key => $value) {
            config([$filenameWithOutExt . '.' . $key => $value]);
        }
    }
}
