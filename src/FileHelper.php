<?php

namespace Padosoft\Laravel\Google\StructuredDataTestingTool;


class FileHelper
{
    public static function adjustPath($path)
    {

        if ($path == '') {
            return array();
        }

        $p = explode(",", str_replace('\\', '/', $path));

        $pathList = array_map(function ($item) {
            return str_finish($item, '/');
        },
            $p
        );

        return $pathList;
    }
}
