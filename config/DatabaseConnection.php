<?php

class Database
{
    private static ?mysqli $connection = null;

    public static function connection(): mysqli
    {
        if (self::$connection === null) {
            $config = require __DIR__ . '/database.php';
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            self::$connection = new mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['database'],
                $config['port']
            );
            self::$connection->set_charset('utf8mb4');
        }

        return self::$connection;
    }
}
