<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../model/account.php';

$username = getenv('ACADEMIC_ADMIN_USERNAME');
$password = getenv('ACADEMIC_ADMIN_PASSWORD');
$useLocalDefaults = $username === false && $password === false;
if ($useLocalDefaults) {
    $username = 'admin';
    $password = '123';
    fwrite(STDERR, "WARNING: Creating the requested local-only admin account (admin / 123). Do not use this password on a public or production server.\n");
}

if (!is_string($username)
    || !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)
    || !is_string($password)
    || (!$useLocalDefaults && strlen($password) < 10)
    || strlen($password) > 72) {
    fwrite(STDERR, "Set both ACADEMIC_ADMIN_USERNAME (3-50 valid characters) and ACADEMIC_ADMIN_PASSWORD (10-72 characters), or leave both unset for the local-only admin / 123 account.\n");
    exit(1);
}

$connection = Database::connection();
$adminCount = (int)$connection->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'")->fetch_assoc()['total'];
if ($adminCount > 0) {
    fwrite(STDERR, "An admin account already exists; no account was created.\n");
    exit(1);
}

$account = new Account();
if ($account->usernameExists($username)) {
    fwrite(STDERR, "That username is already in use.\n");
    exit(1);
}

$account->create($username, $password, 'admin', null);
fwrite(STDOUT, "Initial admin account created.\n");
