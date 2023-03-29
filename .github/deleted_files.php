#!/usr/bin/env php
<?php
$longOpts  = [
    'input:',
];
$parsedArgs = getopt('', $longOpts);

if (!array_key_exists('input', $parsedArgs)) {
    exit('"output" argument is required');
}

$basePath = realpath($parsedArgs['input']);

if (!$basePath) {
    throw new \Exception('Can not find path');
}

define('BASE_DIR', $basePath);

$output = '';
chdir(BASE_DIR);

exec('git log --pretty=format: --name-only | LC_ALL=C sort -u', $output);

// Whitelisted paths are never deleted
$whitelist = array(
    // plugins that are provided by a dummy now

    'engine/Shopware/Plugins/Default/Backend/SwagBepado',
    'engine/Shopware/Plugins/Default/Core/CouchCommerce/',
    'engine/Shopware/Plugins/Default/Backend/HeidelActions/',
    'engine/Shopware/Plugins/Default/Frontend/MoptPaymentPayone',
    'engine/Shopware/Plugins/Default/Frontend/SwagDhl',
    'engine/Shopware/Plugins/Default/Frontend/SwagPaymentKlarna',
    'engine/Shopware/Plugins/Default/Frontend/HeidelPayment/',
    'engine/Shopware/Plugins/Default/Frontend/PaymentSofort/',
    'engine/Shopware/Plugins/Default/Frontend/SofortPayment/',
    'engine/Shopware/Plugins/Default/Frontend/PaymentSkrill/',
    'engine/Shopware/Plugins/Default/Frontend/PigmbhRatePAYPayment/',
    'engine/Shopware/Plugins/Default/Frontend/PigmbhKlarnaPayment/',
    'engine/Shopware/Plugins/Default/Frontend/PiPaymorrowPayment/',
    'engine/Shopware/Plugins/Default/Frontend/SwagPaymentBillsafe/',
    'engine/Shopware/Plugins/Default/Frontend/SwagTrustedShopsExcellence/',
    'engine/Shopware/Plugins/Default/Frontend/SwagPaymentPaypal/',
    'engine/Shopware/Plugins/Default/Frontend/BuiswPaymentPayone',
    'engine/Shopware/Controllers/Frontend/CSRFToken.php',
    'engine/Shopware/Bundle/SearchBundle/Condition/isAvailableCondition.php',

    # Fix for case insensitive file systems
    'engine/Shopware/Components/DependencyInjection/controllers',
    'engine/Library/Zend/Validate/Barcode/Intelligentmail.php',
    'engine/Shopware/Bundle/SitemapBundle/Controllers/Frontend/SitemapIndexXml.php',

    'engine/Shopware/Plugins/Community',
    'engine/Shopware/Plugins/Local',

    'update',

    'config_',
    '_sql/migrations',

    // The .htacces must not be deleted. Changes to this file are handled by \Shopware\Recovery\Update\UpdateHtaccess.
    '.htaccess',
);

$deleteDirs = [];
foreach ($output as $line){
    $line = trim($line);
    if (empty($line)) {
        continue;
    }

    foreach ($whitelist as $whitelistElem) {
        if (strpos($line, $whitelistElem) === 0) {
            continue 2;
        }
    }

    if (file_exists(BASE_DIR . '/' . $line)) {
        continue;
    }

    $deleteDirs[getDeleteDir($line)] = true;
}

/**
 * @param string $filePath
 *
 * @return string
 */
function getDeleteDir($filePath) {
    $checkdir = BASE_DIR . '/' . dirname($filePath);
    if (file_exists($checkdir)) {
        return trim($filePath);
    } else {
        return getDeleteDir(dirname($filePath));
    }
}

$deleteDirs = array_keys($deleteDirs);

echo implode( "\n", $deleteDirs);
