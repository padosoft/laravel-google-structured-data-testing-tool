<?php

namespace Padosoft\Laravel\Google\StructuredDataTestingTool\Test;

use Padosoft\Laravel\Google\StructuredDataTestingTool\FileHelper;

class FileHelperTest extends  \PHPUnit_Framework_TestCase
{
    protected $fileHelper;

    public function setUp()
    {
        $this->fileHelper = new FileHelper();
        parent::setUp();
    }

    /**
     * @test
     */
    public function testAdjustPath()
    {
        $arr = $this->fileHelper->adjustPath('');
        $this->assertTrue(is_array($arr) && count($arr)==0);

        $arr = $this->fileHelper->adjustPath('uno/due/tre/');
        $this->assertTrue(is_array($arr) && count($arr)==1 && $arr[0]=='uno/due/tre/');

        $arr = $this->fileHelper->adjustPath('uno/due/tre');
        $this->assertTrue(is_array($arr) && count($arr)==1);
        $this->assertTrue($arr[0]=='uno/due/tre/');

        $arr = $this->fileHelper->adjustPath('uno/due/tre,quattro/cinque/sei/');
        $this->assertTrue(is_array($arr) && count($arr)==2);
        $this->assertTrue($arr[0]=='uno/due/tre/');
        $this->assertTrue($arr[1]=='quattro/cinque/sei/');
    }

}
