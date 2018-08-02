<?php
namespace Padosoft\Laravel\Google\StructuredDataTestingTool;

use Illuminate\Support\ServiceProvider;

class GoogleStructuredDataTestToolServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/laravel-google-structured-data-testing-tool.php' => config_path('laravel-google-structured-data-testing-tool.php'),
        ], 'config');

        $this->loadViewsFrom(__DIR__ . '/views', 'laravel-google-structured-data-testing-tool');

        $this->publishes([
            __DIR__ . '/views' => base_path('resources/views/vendor/laravel-google-structured-data-testing-tool'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.google-markup:test',
            function () {
                return new GoogleStructuredDataTestTool();
            }
        );

        $this->commands('command.google-markup:test');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['command.google-markup:test'];
    }

    /**
     * @param $app
     * @return integer
     */
    public function dummy($app)
    {
        $i=0;
        if($app){
            return $i;
        }
        return $i++;
    }
}
