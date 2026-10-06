<?php

$dbPath = '/tmp/database.sqlite';
if (!file_exists($dbPath)) {
    if (file_exists(__DIR__ . '/../database/seed.sqlite')) {
        copy(__DIR__ . '/../database/seed.sqlite', $dbPath);
    } else {
        touch($dbPath);
    }
}
putenv("DB_DATABASE={$dbPath}");
$_ENV['DB_DATABASE'] = $dbPath;

// Forward all incoming Vercel requests to Laravel's public/index.php
require __DIR__ . '/../public/index.php';
