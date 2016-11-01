# Laravel Package for testing Schema.org markup or other structured data formats with google structured data testing tool undocumented API.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/padosoft/laravel-google-structured-data-testing-tool.svg?style=flat-square)](https://packagist.org/packages/padosoft/laravel-google-structured-data-testing-tool)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/padosoft/laravel-google-structured-data-testing-tool/master.svg?style=flat-square)](https://travis-ci.org/padosoft/laravel-google-structured-data-testing-tool)
[![Quality Score](https://img.shields.io/scrutinizer/g/padosoft/laravel-google-structured-data-testing-tool.svg?style=flat-square)](https://scrutinizer-ci.com/g/padosoft/laravel-google-structured-data-testing-tool)
[![Total Downloads](https://img.shields.io/packagist/dt/padosoft/laravel-google-structured-data-testing-tool.svg?style=flat-square)](https://packagist.org/packages/padosoft/laravel-google-structured-data-testing-tool)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/3a39da13-6f5f-4041-9700-81e8c1f2e387.svg?style=flat-square)](https://insight.sensiolabs.com/projects/3a39da13-6f5f-4041-9700-81e8c1f2e387)

This package provides a series of commands to automate and bulk check of Schema.org markup or other structured data formats with google structured data testing tool undocumented API that I found.
Google already provides a good tool for testing markup data using the Search Console in Webmaster Tools.
But how can I test markup data if you want to programmatically test your web site urls or a bulk set of urls?
You can't do this with google wmt.
This package try to resolve these problems. 

## WARNING
Note: This is an undocumented Google API. I found it when I interacted with Google’s Structured Data Testing Tool and Google Chrome debug console, 
 thus there is no warranty that the API remains public or that the API changes make the package unusable.

Table of Contents
=================

   * [Laravel Package for testing Schema.org markup or other structured data formats with google structured data testing tool undocumented API.](#laravel-package-for-testing-schemaorg-markup-or-other-structured-data-formats-with-google-structured-data-testing-tool-undocumented-api)
      * [WARNING](#warning)
      * [Requires](#requires)
      * [Installation](#installation)
      * [USAGE](#usage)
         * [EXAMPLE:](#example)
      * [SCHEDULE COMMAND](#schedule-command)
      * [SCREENSHOOTS](#screenshoots)
      * [Change log](#change-log)
      * [Testing](#testing)
      * [Contributing](#contributing)
      * [Security](#security)
      * [Credits](#credits)
      * [About Padosoft](#about-padosoft)
      * [License](#license)

##Requires
  
- php: >=7.0.0
- illuminate/support: ^5.0
- illuminate/http: ^5.0
- padosoft/support": ^1.9
  
## Installation

You can install the package via composer:
``` bash
$ composer require padosoft/laravel-google-structured-data-testing-tool
```
You must install this service provider.

``` php
// config/app.php
'provider' => [
    ...
    Padosoft\Laravel\Google\StructuredDataTestingTool\GoogleStructuredDataTestToolServiceProvider::class,
    ...
];
```
You don't need to register the command in app/Console/Kernel.php, because it provides by GoogleStructuredDataTestToolServiceProvider register() method.

You can publish the config file of this package with this command:
``` bash
php artisan vendor:publish --provider="Padosoft\Laravel\Google\StructuredDataTestingTool\GoogleStructuredDataTestToolServiceProvider"
```
The following config file will be published in `config/laravel-google-structured-data-testing-tool.php`
``` php
return array(
    'mailSubjectSuccess' => env(
        'STRUCTURED_DATA_TESTING_TOOL_SUBJECT_SUCCESS',
        '[google-structured-data-testing-tool]: Ok - markup data is ok.'
    ),
    'mailSubjetcAlarm' => env(
        'STRUCTURED_DATA_TESTING_TOOL_SUBJECT_ALARM',
        '[google-structured-data-testing-tool]: Alarm - markup data error detected.'
    ),
    'mailFrom' => env('STRUCTURED_DATA_TESTING_TOOL_MESSAGE_FROM', 'info@example.com'),
    'mailFromName' => env('STRUCTURED_DATA_TESTING_TOOL_MESSAGE_FROM_NAME', 'Info Example'),
    'mailViewName' => env('STRUCTURED_DATA_TESTING_TOOL_MAIL_VIEW_NAME', 'laravel-google-structured-data-testing-tool::mail'),
    'logFilePath' => env('STRUCTURED_DATA_TESTING_TOOL_LOG_FILE_PATH', storage_path() . '/logs/laravel-google-structured-data-testing-tool.log')
);
```

In your app config folder you can copy from src/config/.env.example the settings for yours .env file used in laravel-google-structured-data-testing-tool.php.
If you use mathiasgrimm/laravel-env-validator 
in src/config folder you'll find an example for validate the env settings. 


## USAGE

When the installation is done you can easily run command to print help:
```bash
php artisan google-markup:test https://www.padosoft.com
```

The `google-markup:test` command check the structured data and schema.org markup in the given site https://www.padosoft.com

You can also pass the path of url txt (a file with one url per line) as an argument:
`php google-markup:test /path/to/my/url.txt`
so you can check multiple site/url (bulk) in one command!

By default, the command displays the result in console, but you can also
send an html email by using the `--mail`option:
```bash
php google-markup:test https://www.padosoft.com --mail=mymail@mydomain.me
```
### EXAMPLE:

Here is a basic example to check composer.lock into these dir:
```bash
php artisan google-markup:test https://www.padosoft.com
```
Here is an example to send output report to mail:
```bash
php artisan google-markup:test https://www.padosoft.com --mail=mymail@mydomain
```
Here is an example to ignore two urls for markup error (if command found any markup error into these dir, write it into output but the email subject isn't set to ALERT):
```bash
php artisan google-markup:test /path/to/my/url.txt --mail=mymail@mydomain --whitelist="https://www.padosoft.com,https://blog.padosoft.it"
```

## SCHEDULE COMMAND

You can schedule a daily (or weekly etc..) report easly, by adding this line into `schedule` method in `app/Console/Kernel.php` :
```php
// app/console/Kernel.php

protected function schedule(Schedule $schedule)
{
    ...
	$schedule->command('google-markup:test "/path/to/my/url.txt" --mail=mymail@mydomain')
            ->daily()
            ->withoutOverlapping()
            ->sendOutputTo(Config::get('laravel-google-structured-data-testing-tool.logFilePath'));
}
```

## SCREENSHOOTS

OUTPUT CONSOLE WITH ONE URL TEST:
![screenshoot](https://raw.githubusercontent.com/padosoft/laravel-google-structured-data-testing-tool/master/resources/img/url-ok.png)

OUTPUT CONSOLE BULK CHECK OK:
![screenshoot](https://raw.githubusercontent.com/padosoft/laravel-google-structured-data-testing-tool/master/resources/img/bulk-ok.png)

OUTPUT CONSOLE BULK CHECK WITH ERRORS:
![screenshoot](https://raw.githubusercontent.com/padosoft/laravel-google-structured-data-testing-tool/master/resources/img/bulk-with-errors.png)

EMAIL VIEW:
![screenshoot](https://raw.githubusercontent.com/padosoft/laravel-google-structured-data-testing-tool/master/resources/img/email-ok.png)

EMAIL VIEW WITH ERRORS:
![screenshoot](https://raw.githubusercontent.com/padosoft/laravel-google-structured-data-testing-tool/master/resources/img/email-with-error.png)


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email instead of using the issue tracker.

## Credits
- [Lorenzo Padovani](https://github.com/lopadova)
- [All Contributors](../../contributors)

## About Padosoft
Padosoft (https://www.padosoft.com) is a software house based in Florence, Italy. Specialized in E-commerce and web sites.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
