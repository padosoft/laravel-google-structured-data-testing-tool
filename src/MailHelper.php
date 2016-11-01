<?php

namespace Padosoft\Laravel\Google\StructuredDataTestingTool;

use Config;
use Validator;
use Illuminate\Console\Command;
use Mail;

class MailHelper
{

    protected $command;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * MailHelper constructor.
     * @param Command $objcommand
     */
    public function __construct(Command $objcommand)
    {
        $this->command = $objcommand;
    }

    /**
     * @param $tuttoOk
     * @param $mail
     * @param $vul
     */
    public function sendEmail($tuttoOk, $mail, $vul)
    {
        $soggetto = Config::get('laravel-google-structured-data-testing-tool.mailSubjectSuccess');

        if (!$tuttoOk) {
            $soggetto = Config::get('laravel-google-structured-data-testing-tool.mailSubjetcAlarm');
        }

        $validator = Validator::make(['email' => $mail], [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            $this->command->error('No valid email passed: ' . $mail . '. Mail will not be sent.');
            return;
        }
        $this->command->line('Send email to <info>' . $mail . '</info>');

        Mail::send(
            Config::get('laravel-google-structured-data-testing-tool.mailViewName'),
            ['vul' => $vul],
            function ($message) use ($mail, $soggetto) {
                $message->from(
                    Config::get('laravel-google-structured-data-testing-tool.mailFrom'),
                    Config::get('laravel-google-structured-data-testing-tool.mailFromName')
                );
                $message->to($mail, $mail);
                $message->subject($soggetto);
            }
        );


        $this->command->line('email sent.');

    }
}
