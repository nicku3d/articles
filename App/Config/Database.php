<?php

namespace App\Config;

class Database {

    /**
     * @return array
     * @throws \Exception
     */
    public static function loadConfig() : array
    {
        $dbConfig = require_once 'db-config.php';
        if (!$dbConfig['host']
            || !$dbConfig['user']
            || !$dbConfig['password']
            || !$dbConfig['db']) {
            throw new \Exception();
        }
        return $dbConfig;
    }
}
