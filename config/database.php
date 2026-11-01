<?php

$environmentValue = static function (string $name, string $default): string {
    $value = getenv($name);

    return $value === false || $value === '' ? $default : $value;
};

return [
    'host' => $environmentValue('DB_HOST', '127.0.0.1'),
    'port' => (int)$environmentValue('DB_PORT', '3306'),
    'database' => $environmentValue('DB_NAME', 'akademik_mvc'),
    'username' => $environmentValue('DB_USERNAME', 'root'),
    'password' => $environmentValue('DB_PASSWORD', ''),
];
