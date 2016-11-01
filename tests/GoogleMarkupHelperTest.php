<?php

namespace Padosoft\Laravel\Google\StructuredDataTestingTool\Test;

use \Mockery as m;
use Orchestra\Testbench\TestCase as Orchestra;
use \Illuminate\Support\Facades\File;
use \Padosoft\Laravel\Google\StructuredDataTestingTool\GoogleMarkupHelper;

class GoogleMarkupHelperTest extends Orchestra
{
    protected $mockCommand;

    /**
     *
     */

    public function setUp()
    {
        $this->mockCommand = m::mock('Illuminate\Console\Command');
        $this->mockCommand->shouldReceive('option')->with('verbose')->andReturn(true);
        $this->mockCommand->shouldReceive('line');
        $this->mockCommand->shouldReceive('error');
        $this->mockCommand->shouldReceive('info');
        $this->mockCommand->shouldReceive('comment');

        parent::setUp();
    }

    public function tearDown() {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function checkErrorTest()
    {
        $response = json_decode(File::get(__DIR__.'/test_file/errors.json'),true);
        $google = new GoogleMarkupHelper($this->mockCommand, []);

        $result = $google->checkError($response['errors'][0], true);
        $this->assertEquals(1, count($result));
        $this->assertEquals(5, count($result[0]));
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('type', $result[0]);
        $this->assertArrayHasKey('errors', $result[0]);
        $this->assertArrayHasKey('warnings', $result[0]);
        $this->assertArrayHasKey('isOk', $result[0]);
    }

    /**
     * @test
     */
    public function checkResponseTestOK()
    {
        $response = json_decode(File::get(__DIR__.'/test_file/response-no-error.json'),true);
        $this->assertTrue(count($response)>0);
        $this->assertArrayHasKey('numObjects', $response);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('totalNumErrors', $response);
        $this->assertArrayHasKey('totalNumWarnings', $response);

        $google = new GoogleMarkupHelper($this->mockCommand, []);
        $result = $google->checkResponse($response);
        $this->assertTrue($result);

        $result = $google->tableEntities;
        $this->assertTrue(count($result)>0);
        $this->assertEquals(1, count($result));
        $this->assertEquals(5, count($result[0]));
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('type', $result[0]);
        $this->assertEquals('', $result[0]['type']);
        $this->assertArrayHasKey('errors', $result[0]);
        $this->assertEquals(0, $result[0]['errors']);
        $this->assertArrayHasKey('warnings', $result[0]);
        $this->assertEquals(0, $result[0]['warnings']);
        $this->assertArrayHasKey('isOk', $result[0]);
        $this->assertEquals(true, $result[0]['isOk']);
    }

    /**
     * @test
     */
    public function checkResponseTestKO()
    {
        $response = json_decode(File::get(__DIR__.'/test_file/response-error.json'),true);
        $this->assertTrue(count($response)>0);
        $this->assertArrayHasKey('numObjects', $response);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('totalNumErrors', $response);
        $this->assertArrayHasKey('totalNumWarnings', $response);

        $google = new GoogleMarkupHelper($this->mockCommand, []);
        $result = $google->checkResponse($response);
        $this->assertFalse($result);

        $result = $google->tableEntities;
        $this->assertTrue(count($result)>0);
        $this->assertCount(3, $result);
        $this->assertCount(5, $result[0]);
        $this->assertCount(5, $result[1]);
        $this->assertArrayHasKey('name', $result[0]);
        $this->assertArrayHasKey('type', $result[0]);
        $this->assertArrayHasKey('errors', $result[0]);
        $this->assertEquals(2, $result[0]['errors']);
        $this->assertArrayHasKey('warnings', $result[0]);
        $this->assertEquals(0, $result[0]['warnings']);
        $this->assertArrayHasKey('isOk', $result[0]);
        $this->assertEquals(false, $result[0]['isOk']);
        $this->assertArrayHasKey('name', $result[1]);
        $this->assertArrayHasKey('type', $result[1]);
        $this->assertNotEmpty($result[1]['type']);
        $this->assertArrayHasKey('errors', $result[1]);
        $this->assertNotEmpty($result[1]['errors']);
        $this->assertArrayHasKey('warnings', $result[1]);
        $this->assertNotEmpty($result[1]['warnings']);
        $this->assertArrayHasKey('isOk', $result[1]);
        $this->assertEquals(false, $result[1]['isOk']);
    }
}
