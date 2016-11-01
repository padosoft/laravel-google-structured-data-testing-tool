<?php

namespace Padosoft\Laravel\Google\StructuredDataTestingTool;

use Config;
use Illuminate\Console\Command;

class GoogleStructuredDataTestTool extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-markup:test
                            {path? : path where find url.txt, if you want to test url direct, you can use directly url that start with http}
                            {--M|mail= : If you want send result to email}
                            {--N|nomailok=false : True if you want send result to email only for alarm, false is default}
                            {--w|whitelist= : If you want exclude from alarm some url, divide by ","}
                            {verbosity=false : If you want more verbosity log}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = <<<EOF
The <info>google-markup:test</info> command looks in url.txt file in the given path
and foreach url find in this file, check against google structured data testing tool API:
<info>php artisan google-markup:test</info>
If you omit path argument, command look into url defined in app config.
You can also pass an url in the path as an argument:
<info>php artisan google-markup:test https://www.padosoft.com</info>
By default, the command displays the result in console, but you can also
send an html email by using the <info>--mail</info> option:
<info>php artisan /path/to/my/url.txt --mail=mymail@mydomain.me</info>
If you want to receive an email only for alarm (find markup errors) using the <info>--nomailok</info> option:
<info>php artisan /path/to/my/url.txt --mail=mymail@mydomain.me --nomailok=true</info>
If you want to exclude some url from alert using the <info>--whitelist</info> option:
<info>php artisan /path/to/my/url.txt --whitelist=https://www.padosoft.com,https://blog.padosoft.com</info>
If you want more verbosity log append --verbosity=true
EOF;

    /**
     * @var array
     */
    protected $headersTableConsole = ['name', 'type', 'errors', 'warnings', 'isOk'];

    /**
     * @var array
     */
    protected $tableEntities = [];

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->hardWork($this->argument(), $this->option());
    }

    /**
     * @param $argument
     * @param $option
     */
    private function hardWork($argument, $option)
    {
        $path = $argument['path'];
        $this->line('path: <info>' . $path . '</info>');
        $this->line('Check url/file...');
        $urls = $this->findUrls($path, $this);

        $this->tableEntities = [];
        $tuttoOk = true;
        $numUrl = 0;

        $whitelist = FileHelper::adjustPath($option['whitelist']);

        foreach ($urls as $url) {

            $this->line("Analizing <info>" . ($numUrl + 1) . "</info> di <info>" . count($urls) . "</info>");

            if(!$this->checkStructuredData($url, $whitelist)){
                $tuttoOk = false;
            }

            $numUrl++;
        }

        $this->notifyResult($option['mail'], $option['nomailok'], $tuttoOk);

    }

    /**
     * @param $mail
     * @param $nomailok
     * @param boolean $tuttoOk
     */
    private function notifyResult($mail, $nomailok, $tuttoOk)
    {
        //print to console
        $this->table($this->headersTableConsole, $this->tableEntities);

        //send email
        if (!$tuttoOk || $nomailok == '' || strtolower($nomailok) != 'true') {
            $this->sendEmail($mail, $tuttoOk);
        }

        $this->notify($tuttoOk);
    }

    /**
     * @param boolean $result
     */
    private function notify($result)
    {
        if ($result) {
            $this->notifyOK();
            return;
        }

        $this->notifyKO();
    }

    /**
     *
     */
    private function notifyOK()
    {
        $esito = Config::get('laravel-google-structured-data-testing-tool.mailSubjectSuccess');
        $this->line('<info>'.$esito.'</info>');
        $this->line('');
    }

    /**
     *
     */
    private function notifyKO()
    {
        $esito = Config::get('laravel-google-structured-data-testing-tool.mailSubjetcAlarm');
        $this->error($esito);
        $this->line('');
    }

    /**
     * @param $mail
     * @param boolean $tuttoOk
     */
    private function sendEmail($mail, $tuttoOk)
    {
        if ($mail != '') {
            $email = new MailHelper($this);
            $email->sendEmail($tuttoOk, $mail, $this->tableEntities);
        }
    }

    /**
     *
     * @param $url
     * @return array with a valid url or empty array
     */
    private function getUrl($url)
    {
        $urls = array();

        if (isUrl($url)) {
            $urls[] = $url;
        }

        return $urls;
    }

    /**
     *
     * @param $path
     * @param \Illuminate\Console\Command $cmd
     * @return array of valid url or empty array
     */
    private function getUrlsByPath($path, \Illuminate\Console\Command $cmd)
    {
        $urls = array();

        if (!file_exists($path)) {
            return $urls;
        }

        $cmd->line('Find file ' . $path);

        $urls_tmp = explode("\n", file_get_contents($path));
        $cmd->line('Found <info>' . count($urls_tmp). '</info> entries in file.');

        return array_filter($urls_tmp, function ($url) use ($cmd) {
            if (isUrl(trim($url))) {
                return true;
            } else {
                $cmd->error('ERROR: url \'' . trim($url) . '\' is NOT a valid url!');
                return false;
            }
        });
    }

    /**
     *
     * @param $path
     * @param \Illuminate\Console\Command $cmd
     * @return array of valid url
     */
    private function findUrls($path, \Illuminate\Console\Command $cmd)
    {
        $urls = $this->getUrl($path);
        if (count($urls) > 0) {
            $cmd->line('Find url: <comment>' . $path . '</comment>');
            return $urls;
        }

        $urls = $this->getUrlsByPath($path, $cmd);
        if (count($urls) > 0) {
            $cmd->line('Find <info>' . count($urls) . '</info> valid url in ' . $path);
            return $urls;
        }

        $cmd->error('File url ' . $path . ' not found and not a valid url!');
        return $urls;
    }

    /**
     * @param $url
     * @param $whitelist
     * @return bool
     */
    private function checkStructuredData($url, $whitelist)
    {
        $this->line("Analizing: $url ...");

        $gtest = new GoogleMarkupHelper($this, $whitelist);
        $response = $gtest->checkUrl($url);

        if (($response === null) || !is_array($response)) {
            $this->error("Error! Response not vaild or null.");
            $this->line("Response:");
            $this->line(get_var_dump_output($response));
            return false;
        }
        if (count($response) == 0) {
            return false;
        }

        $tuttoOk = $gtest->checkResponse($response);

        $this->tableEntities = array_merge($this->tableEntities, $gtest->tableEntities);

        return $tuttoOk;
    }

}

