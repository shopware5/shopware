<?php
require_once __DIR__ . '/init.php';

/* @var $context \Shopware\Components\Jira\API\Context */

if (false === isset($_POST['i']) || false === isset($_POST['p'])) {
    return;
}

// Load utilized services
$issueService   = $context->getIssueService();
$projectService = $context->getProjectService();


$issue = $issueService->load((int) $_POST['i']);

if ($context->canUser('delete', $issue)) {
    $issueService->delete($issue);

    header('Location: issues.php?i=' . $issue->getId() . '&p=' . ((int) $_POST['p']));
    exit;
}

header('Location: issue.php?i=' . $issue->getId() . '&p=' . ((int) $_POST['p']));
