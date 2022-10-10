<?php

namespace App\Config;

class Database {

    /**
     * @param string $filename config file in App/Config directory
     *
     * @return array db config data
     *
     * @throws \Exception
     */
    public static function loadConfig(string $filename = 'db-config.php') : array
    {
        if (!defined('__ROOT__')) {
            define('__ROOT__', dirname(dirname(__FILE__)));
        }
        $dbConfig = require __ROOT__ . '/Config/' . basename($filename);
        if (!$dbConfig['host']
            || !$dbConfig['user']
            || !$dbConfig['password']
            || !$dbConfig['db']) {
            throw new \Exception();
        }
        return $dbConfig;
    }
}
