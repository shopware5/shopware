#!/usr/bin/env php
<?php
// ./ApplyDeltas.php --username="root" --password="example" --host="localhost" --dbname="example-db"

$shopPath = realpath(__DIR__ . '/../');

$longopts  = array(
    "username:",
    "password:",
    "host:",
    "dbname:",
);
$dbConfig = getopt('', $longopts);

if (empty($dbConfig)) {
    if (file_exists($shopPath . '/config.php')) {
        $config = require $shopPath . '/config.php';
    } elseif (file_exists($shopPath . '/engine/Shopware/Configs/Custom.php')) {
        $config = require $shopPath . '/engine/Shopware/Configs/Custom.php';
    } else {
        die('Could not find shopware config');
    }

    $dbConfig = $config['db'];
}

try {
    $conn = new PDO('mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['dbname'], $dbConfig['username'], $dbConfig['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo 'Could not connect to database: ' . $e->getMessage();
    exit(1);
}

require $shopPath . '/engine/Shopware/Components/Migrations/AbstractMigration.php';
require $shopPath . '/engine/Shopware/Components/Migrations/Manager.php';

$migrationManger = new Shopware\Components\Migrations\Manager($conn, $shopPath . '/_sql/migrations');
$migrationManger->run();

exit(0);
