<?php

function currentUser(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user'])
        ? $_SESSION['user']
        : null;
}

function isAdmin(): bool
{
    return (currentUser()['role'] ?? null) === 'admin';
}

function denyAccess(): void
{
    http_response_code(403);
    echo 'Anda tidak memiliki izin untuk melakukan tindakan ini.';
    exit;
}

function encodeUrlPath(string $path): string
{
    return implode('/', array_map(
        static fn(string $segment): string => rawurlencode(rawurldecode($segment)),
        explode('/', $path)
    ));
}
