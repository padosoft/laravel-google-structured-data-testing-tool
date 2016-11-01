<?php
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
