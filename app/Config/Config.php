<?php

namespace App\Config;

class Config
{

    private static array $env = [];

    public static function init()
    {
        $env = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/api/.env");
        $lines = explode("\n", $env);
        foreach ($lines as $line) {
            preg_match("/([^#]+)\=(.*)/", $line, $matches);
            if (isset($matches[2])) {
                self::$env[trim($matches[1])] = trim($matches[2]);
            }
        }
    }

    public static function key(string $k): mixed
    {
        return self::$env[$k];
    }
}
