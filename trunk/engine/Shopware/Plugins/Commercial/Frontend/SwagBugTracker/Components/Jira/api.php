<?php
exit;
require_once __DIR__ . '/bootstrap.php';

use \Shopware\Components\Jira\Core\Service\Context;

require_once __DIR__ . '/config.php';

$pdo = new \PDO($dbdsn, $dbuser, $dbpass);
$pdo->query('SET NAMES `utf8`');

$rest = new \Shopware\Components\Jira\Core\Rest\Client(
    new \Guzzle\Http\Client($jira, array('version' => 'latest'))
);

// Initialize a context implementation
$context = new Context();
$context->initialize(
    new \Shopware\Components\Jira\Core\Mapper\MapperFactory($context),
    new \Shopware\Components\Jira\Core\Storage\Mixed\GatewayFactory($pdo, $rest),
    'Qafoo GmbH'
);

// Get the issue service
$issueService = $context->getIssueService();

// With sub issues
$issue = $issueService->load(12031);
var_dump(
    $issue->getSubIssues()
);

/*
// With components and keywords
$issue = $issueService->load(12274);
var_dump(
    $issue->getKeywords(),
    $issue->getComponents()
);

*/
$versionService = $context->getVersionService();
var_dump($versionService->loadAffectedByIssue($issue));

// Get the project service
$projectService = $context->getProjectService();

// Load a project by it's id
$project = $projectService->load(10100);


$componentService = $context->getComponentService();
$components = $componentService->loadByProject($project);

$issueCreate = $issueService->newIssueCreate($project);
$issueCreate->setName('Issue summary (' . microtime(true) . ')');
$issueCreate->setDescription('Some detailed description here...');
$issueCreate->setRemoteUser('Qafoo GmbH');

$issue = $issueService->create($issueCreate);
var_dump($issue);

$issueUpdate = $issueService->newIssueUpdate();
$issueUpdate->setName('My issue (' . microtime(true) . ')');

var_dump($issueService->update($issue, $issueUpdate));

//var_dump($issueService->loadByKey('SW-101'));

//$issueService->delete($issue);

return;
$rest->post('rest/api/2.0.alpha1/issue/QF-16/votes', array('username' => 'sindelfingen'));

var_dump($rest->get('rest/api/2.0.alpha1/issue/QF-16/votes'));