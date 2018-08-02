<?php

namespace Padosoft\Laravel\Google\StructuredDataTestingTool\Test;

use Padosoft\Laravel\Google\StructuredDataTestingTool\Test\TestBaseOrchestra;
use Padosoft\Laravel\Google\StructuredDataTestingTool\GoogleStructuredDataTestTool;
use Illuminate\Support\Facades\Artisan;
use \Mockery as m;

class GoogleStructuredDataTestToolTest extends TestBaseOrchestra
{
    protected $googleStructuredDataTestTool;
    protected $mockCommand;

    public function setUp()
    {
        parent::setUp();

        $this->mockCommand = m::mock('Illuminate\Console\Command');
        $this->mockCommand->shouldReceive('option')->with('verbose')->andReturn(true);
        $this->mockCommand->shouldReceive('line');
        $this->mockCommand->shouldReceive('error');
        $this->mockCommand->shouldReceive('info');
        $this->mockCommand->shouldReceive('comment');

        $this->googleStructuredDataTestTool = new GoogleStructuredDataTestTool();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    public function getPackageProviders($app)
    {
        return [\Padosoft\Laravel\Google\StructuredDataTestingTool\GoogleStructuredDataTestToolServiceProvider::class];
    }

    /** @test */
    public function testHardWorkKO()
    {
        //$this->artisan('google-markup:test',['path'=>'sdsfsdfsdfsd','--mail'=>'helpdesk@padosoft.com']);
        Artisan::call('google-markup:test', ['path' => 'sdsfsdfsdfsd', '--mail' => 'helpdesk@padosoft.com']);
        $output = Artisan::output();
        $this->assertContains('File url sdsfsdfsdfsd not found and not a valid url!', $output);
    }

    /** @test */
    public function testHardWorkOKUrl()
    {
        Artisan::call('google-markup:test', [
            'path' => 'https://www.padosoft.com',
            '--mail' => 'helpdesk@padosoft.com',
            '--whitelist' => 'pippo,paperino'
        ]);
        $output = Artisan::output();
        $this->assertContains('warnings', $output);
    }

    /** @test */
    public function testHardWorkOKFileUrl()
    {
        Artisan::call('google-markup:test', [
            'path' => __DIR__ . '\test_file\url.txt',
            '--mail' => 'helpdesk@padosoft.com',
            '--whitelist' => 'pippo,paperino'
        ]);
        $output = Artisan::output();
        $this->assertNotContains('MISSING_REQUIRED_FIELD', $output);
    }

    /** @test */
    public function testHardWorkNoMail()
    {
        Artisan::call('google-markup:test', [
            'path' => __DIR__ . '\test_file\url_no_data.txt',
            '--mail' => 'helpdesk@padosoft.com',
            '--nomailok' => 'true',
            '--whitelist' => 'paperino'
        ]);
        $output = Artisan::output();
        $this->assertNotRegExp('/email sent/', $output);

    }

    /** @test */
    public function testHardWorkYesMail()
    {
        Artisan::call('google-markup:test', [
            'path' => __DIR__ . '\test_file\url.txt',
            '--mail' => 'helpdesk@padosoft.com',
            '--nomailok' => 'false',
            '--whitelist' => 'paperino'
        ]);
        $output = Artisan::output();
        $this->assertRegExp('/email sent/', $output);

    }

    /** @test */
    public function testHardWorkNoMailOk_ButNoOk()
    {
        Artisan::call('google-markup:test', [
            'path' => __DIR__ . '\test_file\url_no_data.txt',
            '--mail' => 'helpdesk@padosoft.com',
            '--nomailok' => 'true',
            '--whitelist' => 'paperino'
        ]);
        $output = Artisan::output();
        $this->assertNotContains('MISSING_REQUIRED_FIELD', $output);
        $this->assertNotRegExp('/email sent/', $output);
    }

    /**
     * @test
     */
    public function findUrlsTest()
    {
        $urls = $this->invokeMethod($this->googleStructuredDataTestTool, 'findUrls', ['sdadadada', $this->mockCommand]);
        $this->assertTrue(is_array($urls));
        $this->assertTrue(count($urls) < 1);

        $urls = $this->invokeMethod($this->googleStructuredDataTestTool, 'findUrls',
            ['https://www.padosoft.com', $this->mockCommand]);
        $this->assertTrue(is_array($urls));
        $this->assertTrue(count($urls) == 1);

        $urls = $this->invokeMethod($this->googleStructuredDataTestTool, 'findUrls',
            [__DIR__ . '/test_file/url.txt', $this->mockCommand]);
        $this->assertTrue(is_array($urls));
        $this->assertTrue(count($urls) == 2);
    }

}
