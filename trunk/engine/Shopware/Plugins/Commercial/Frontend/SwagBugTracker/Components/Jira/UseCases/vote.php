<?php
require_once __DIR__ . '/init.php';

/* @var $context \Shopware\Components\Jira\API\Context */

if (false === isset($_GET['i']) || false === isset($_GET['p']) || false === isset($_GET['v'])) {
    return;
}

// Load utilized services
$issueService   = $context->getIssueService();
$projectService = $context->getProjectService();

if ($context->canUser('vote')) {
    $issue = $issueService->load((int) $_GET['i']);

    $issueUpdate = $issueService->newIssueUpdate();
    $issueUpdate->setVotes($issue->getVotes() + (int) $_GET['v']);

    $issueService->update($issue, $issueUpdate);

    header('Location: issue.php?i=' . $issue->getId() . '&p=' . ((int) $_GET['p']));
    exit;
}

header('Location: issue.php?i=' . $issue->getId() . '&p=' . ((int) $_GET['p']));
