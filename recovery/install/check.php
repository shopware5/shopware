<?php
error_reporting(-1);
ini_set('display_errors', true);

$tokenFile = __DIR__ . '/tmp/token';
$token = '';
if (is_readable($tokenFile)) {
    $token = file_get_contents($tokenFile);
}
$token = trim($token);

if (!$token
    || empty($token)
    || !isset($_GET['token'])
    || empty($_GET['token'])
    || $token != $_GET['token']
) {
    header("HTTP/1.1 403 Forbidden");
    echo("Forbidden");
    exit;
}

$result = [
    'ioncubeLoader' => checkIonCubeLoader(),
    'phpversion'    => phpversion(),
];

if (defined(JSON_PRETTY_PRINT)) {
    echo json_encode($result, JSON_PRETTY_PRINT);
} else {
    echo json_encode($result, JSON_PRETTY_PRINT);
}

/**
 * Checks the ion cube loader
 *
 * @return bool|string
 */
function checkIonCubeLoader()
{
    if (!extension_loaded('ionCube Loader')) {
        return false;
    }
    ob_start();
    phpinfo(1);
    $s = ob_get_contents();
    ob_end_clean();
    if (preg_match('/ionCube&nbsp;PHP&nbsp;Loader&nbsp;v([0-9.]+)/', $s, $match)) {
        return $match[1];
    }

    return false;
}
