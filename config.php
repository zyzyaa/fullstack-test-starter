<?php

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'test');
define('DB_USER', 'zyzyaa');
define('DB_PASS', 'Scand!webTestShop');

try
{
    $pdo = new PDO
    (
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}
catch (PDOException $e)
{
    die("Database connection failed: " . $e->getMessage());
}
