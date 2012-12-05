<?php
namespace Shopware\Components\Jira\UseCases;

use \Shopware\Components\Jira\Core\Rest\Client;
use \Shopware\Components\Jira\Core\Service\Context;
use \Shopware\Components\Jira\Core\Mapper\MapperFactory;
use \Shopware\Components\Jira\Core\Storage\Mixed\GatewayFactory;

// TODO: Remove this
ini_set('display_errors', 'on');

require_once __DIR__ . '/../bootstrap.php';

$jira = 'http://jira_extern:123a21ex%@jira.shopware.cc:1234/jira';

$dbdsn  = 'mysql:host=localhost;dbname=jira';
$dbuser = 'root';
$dbpass = 'deltavista11X';

$remoteUser = 'Qafoo GmbH';

/**
 * List of projects shown here
 */
$projectKeys = array('EN', 'SW');

$pdo = new \PDO($dbdsn, $dbuser, $dbpass);
$pdo->query('SET NAMES `utf8`');

$rest = new Client(
    new \Guzzle\Http\Client($jira, array('version' => 'latest'))
);

// Initialize a context implementation
$context = new Context();
$context->initialize(
    new MapperFactory($context),
    new GatewayFactory($pdo, $rest),
    $remoteUser
);