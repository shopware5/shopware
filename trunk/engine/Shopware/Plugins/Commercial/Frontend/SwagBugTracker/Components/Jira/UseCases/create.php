<?php
/* @var $context \Shopware\Components\Jira\API\Context */

use \Shopware\Components\Jira\API\Model\IssueType;

require_once __DIR__ . '/init.php';

if (false === $context->canUser('create')) {
    if (isset($_GET['p'])) {
        header('Location: issues.php?p=' . ((int) $_GET['p']));
    } else {
        header('Location: index.php');
    }
    exit;
}

// Get the utilized services
$projectService   = $context->getProjectService();
$versionService   = $context->getVersionService();
$issueService     = $context->getIssueService();
$componentService = $context->getComponentService();

// Load the current project
$project = $projectService->load((int) $_GET['p']);

if (isset($_POST['project'])) {

    $issueCreate = $issueService->newIssueCreate($project);
    $issueCreate->setType($_POST['issueType']);
    $issueCreate->setName($_POST['name']);
    $issueCreate->setDescription($_POST['description']);
    $issueCreate->setRemoteUser($context->getCurrentRemoteUser());

    $issueCreate->setKeywords(
        array_filter(
            array_map(
                'trim',
                explode(',', str_replace(' ', '', $_POST['keywords']))
            )
        )
    );

    foreach ($_POST['versions'] as $versionId) {
        $issueCreate->addVersion($versionService->load($versionId));
    }

    foreach ($_POST['components'] as $componentId) {
        $issueCreate->addComponent($componentService->load($componentId));
    }

    $issue = $issueService->create($issueCreate);

    header(sprintf('Location: issue.php?p=%d&i=%d', $project->getId(), $issue->getId()));
    exit;
}

// Load all versions
$versions = $versionService->loadByProject($project);

// Load all components for the project
$components = $componentService->loadByProject($project);

?><!DOCTYPE>
<html>
<head>
    <title>JIRA Stuff</title>
    <meta charset="utf-8" />
    <link href="https://raw.github.com/twitter/bootstrap/master/docs/assets/css/bootstrap.css" rel="stylesheet" />
</head>
<body>
<h1>
    <a href="issues.php?p=<?php echo $project->getId(); ?>">
        <?php echo $project->getName(); ?></a>
    / New issue
</h1>

<form method="post">
    <input type="hidden" name="project" value="<?php echo $project->getId(); ?>" />
    <fieldset>
        <label>Issue Type:</label>
        <select name="issueType">
            <?php foreach (IssueType::getIssueTypes() as $issueType) { ?>
                <option value="<?php echo $issueType->getId(); ?>"><?php echo $issueType->getName(); ?>
            <?php } ?>
        </select>
    </fieldset>
    <fieldset>
        <label>Affected Versions:</label>
        <select name="versions[]" multiple="multiple" size="5">
            <?php foreach ($versions as $version) { ?>
                <?php if ($version->isReleased()) { ?>
                    <option value="<?php echo $version->getId(); ?>"><?php echo $version->getName(); ?>
                <?php } ?>
            <?php } ?>
        </select>
    </fieldset>
    <fieldset>
        <label>Affected components:</label>
        <select name="components[]" multiple="multiple" size="5">
            <?php foreach ($components as $component) { ?>
                <option value="<?php echo $component->getId(); ?>"><?php echo $component->getName(); ?>
            <?php } ?>
        </select>
    </fieldset>
    <fieldset>
        <label>Summary:</label><br />
        <input type="text" name="name" value="" />
    </fieldset>
    <fieldset>
        <label>Description:</label><br />
        <textarea name="description"></textarea>
    </fieldset>
    <fieldset>
        <label>Keywords:</label><br />
        <input type="text" name="keywords" value="" /><br />
        <small><em>Comma separated list of keywords.</em></small>
    </fieldset>

    <fieldset>
        <input type="submit" name="submit" value="Submit" />
    </fieldset>
</form>
