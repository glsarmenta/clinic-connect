<?php

$dbPath = '/tmp/database.sqlite';
if (! file_exists($dbPath)) {
    if (file_exists(__DIR__.'/../database/seed.sqlite')) {
        copy(__DIR__.'/../database/seed.sqlite', $dbPath);
    } else {
        touch($dbPath);
    }
}
putenv("DB_DATABASE={$dbPath}");
$_ENV['DB_DATABASE'] = $dbPath;

if ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')) {
    $_SERVER['HTTPS'] = 'on';
}

// Forward all incoming Vercel requests to Laravel's public/index.php
require __DIR__.'/../public/index.php';
