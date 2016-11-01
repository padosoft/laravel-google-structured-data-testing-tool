<?php

namespace Padosoft\Laravel\Google\StructuredDataTestingTool;

use Illuminate\Console\Command;

class GoogleMarkupHelper
{
    protected $command;

    protected $url;

    protected $whitelist;

    public $tableEntities = [];

    /**
     * constructor.
     * @param Command $objcommand
     * @param $whitelist
     */
    public function __construct(Command $objcommand, $whitelist)
    {
        $this->command = $objcommand;
        $this->whitelist = $whitelist;
    }

    /**
     *
     * Send Request to sensiolab and return array of sensiolab vulnerabilities.
     * Empty array if here is no vulnerabilities.
     *
     * @param $url path to composer.lock file.
     *
     * @return array
     */
    public function checkUrl($url)
    {
        $this->url = $url;

        $optArray = array(
            CURLOPT_URL => "https://search.google.com/structured-data/testing-tool/validate",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "url=" . urlencode($url),
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded;charset=UTF-8",
                "origin: https://search.google.com",
                "postman-token: 7ee787bf-1fe1-30c6-0b76-3dce8926bd5b",
                "referer: https://search.google.com/structured-data/testing-tool/u/0/?hl=it",
                "user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36"
            ),
        );

        $response = $this->sendRequest($optArray);
        if ($response === false) {
            return false;
        }

        return $this->parseResponse($response);
    }

    /**
     * @param $optArray
     * @return bool|mixed
     */
    public function sendRequest($optArray)
    {
        $curl = curl_init();

        curl_setopt_array($curl, $optArray);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $information = curl_getinfo($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $colorTag = $this->getColorTagForStatusCode($http_code);
        $this->command->line("HTTP StatusCode: <{$colorTag}>" . $http_code . "</{$colorTag}>");

        if ($err || $http_code != 200) {
            $this->command->error("cURL Error #:" . $err);
            $this->command->line("cURL info: " . print_r($information, true));
            $this->command->line("Response: " . get_var_dump_output($response));
            return false;
        } elseif ($this->command->option('verbose')==true) {
            $this->command->line("cURL opt array:");
            $this->command->line(get_var_dump_output($optArray));
            $this->command->line("cURL info: " . print_r($information, true));
            $this->command->line("Response: " . get_var_dump_output($response));
        }

        return $response;
    }

    /**
     * @param $error
     * @param bool $tuttoOk
     * @return array
     */
    public function checkError($error, $tuttoOk)
    {
        $tableEntities = array();

        $arr = [
            'name' => '',
            'type' => var_export($error['ownerSet'], true),
            'errors' => var_export($error['errorType'], true),
            'warnings' => var_export($error['args'], true),
            'isOk' => $tuttoOk,
        ];
        $tableEntities[] = $arr;

        return $tableEntities;
    }

    /**
     * @param $response
     * @return array
     */
    public function checkResponse($response)
    {
        $tuttoOk = true;

        if ($response['numObjects'] == 0) {
            $this->command->info("No structured data Found");
        }
        if ($response['totalNumErrors'] > 0) {
            $this->command->error("Found " . $response['totalNumErrors'] . " error");
            $tuttoOk = false;
        }
        if ($response['totalNumWarnings'] > 0) {
            $this->command->warn("Found " . $response['totalNumWarnings'] . " warnings");
            $tuttoOk = false;
        }
        if ($response['totalNumErrors'] == 0 && $response['totalNumWarnings'] == 0) {
            $this->command->info("No Errors or Warnings Found");
        }

        if (in_array(rtrim($this->url), $this->whitelist)) {
            $tuttoOk = true;
        }

        $colorTag = $this->getColorTagForResponse($response);

        $this->tableEntities [] = [
            'name' => "<{$colorTag}>" . $this->url . "</{$colorTag}>",
            'type' => $response['numObjects'] == 0 ? 'No structured data Found' : '',
            'errors' => $response['totalNumErrors'],
            'warnings' => $response['totalNumWarnings'],
            'isOk' => $tuttoOk,
        ];

        if(!array_key_exists_safe($response, 'errors')) {
            return $tuttoOk;
        }

        foreach ($response['errors'] as $error) {
            $this->tableEntities = array_merge($this->tableEntities, $this->checkError($error, $tuttoOk));
        }

        return $tuttoOk;
    }

    /**
     * Get the color tag for the given status code.
     *
     * @param string $code
     *
     * @return string
     *
     * @see https://github.com/spatie/http-status-check/blob/master/src/CrawlLogger.php#L96
     */
    protected function getColorTagForStatusCode($code)
    {
        if (starts_with($code, '2')) {
            return 'info';
        }
        if (starts_with($code, '3')) {
            return 'comment';
        }
        return 'error';
    }

    /**
     * Get the color tag for the given response.
     *
     * @param string $response
     *
     * @return string
     *
     */
    protected function getColorTagForResponse($response)
    {
        if ($response['totalNumErrors'] > 0) {
            return 'error';
        }
        if ($response['totalNumWarnings'] > 0 || $response['numObjects'] == 0) {
            return 'comment';
        }
        return 'info';
    }

    /**
     * @param $response
     * @return mixed|string
     */
    protected function parseResponse($response)
    {
        //sometimes response return with strange start chars
        if (!starts_with(trim($response), "{")) {
            $response = substr($response, 4);
        }

        //strip html sometimes invalidate json
        $response = str_replace(["\r", "\n"], "", $response);
        $re = '/"html":"(.*),"errors"/';
        $subst = '"html":"","errors"';
        $response = preg_replace($re, $subst, $response);
        $re = '/"html": "(.*),"errors"/';
        $subst = '"html":"","errors"';
        $response = preg_replace($re, $subst, $response);

        $response = json_decode($response, true);

        return $response;
    }
}
