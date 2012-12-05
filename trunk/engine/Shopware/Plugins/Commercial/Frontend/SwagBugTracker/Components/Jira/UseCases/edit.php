<?php
/* @var $context \Shopware\Components\Jira\API\Context */

use \Shopware\Components\Jira\API\Model\IssueType;

require_once __DIR__ . '/init.php';

// Get the utilized services
$projectService   = $context->getProjectService();
$versionService   = $context->getVersionService();
$issueService     = $context->getIssueService();
$componentService = $context->getComponentService();

// Load the current project
$project = $projectService->load((int) $_GET['p']);

// Load current issue
$issue = $issueService->load((int) $_GET['i']);

if (false === $context->canUser('edit', $issue)) {
    if (isset($_GET['p'])) {
        header('Location: issues.php?p=' . ((int) $_GET['p']));
    } else {
        header('Location: index.php');
    }
    exit;
}



if (isset($_POST['submit'])) {

    $issueUpdate = $issueService->newIssueUpdate();
    $issueUpdate->setType($_POST['issueType']);
    $issueUpdate->setName($_POST['name']);
    $issueUpdate->setDescription($_POST['description']);
    $issueUpdate->setKeywords(
        array_filter(
            array_map(
                'trim',
                explode(',', str_replace(' ', '', $_POST['keywords']))
            )
        )
    );

    foreach ($_POST['versions'] as $versionId) {
        $issueUpdate->addVersion($versionService->load($versionId));
    }

    foreach ($_POST['components'] as $componentId) {
        $issueUpdate->addComponent($componentService->load($componentId));
    }

    $issue = $issueService->update($issue, $issueUpdate);

    header(sprintf('Location: issue.php?p=%d&i=%d', $project->getId(), $issue->getId()));
    exit;
}

// Load all versions
$versions = $versionService->loadByProject($project);

// Load affected versions
$affectedVersions = array();
foreach ($versionService->loadAffectedByIssue($issue) as $version) {
    $affectedVersions[$version->getId()] = $version->getId();
}

// Load all components for the project
$components = $componentService->loadByProject($project);

// Load selected components
$selectedComponents = array();
foreach ($componentService->loadByIssue($issue) as $component) {
    $selectedComponents[$component->getId()] = $component->getId();
}

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
    / Edit issue
</h1>

<form method="post">
    <fieldset>
        <label>Issue Type:</label>
        <select name="issueType">
            <?php foreach (IssueType::getIssueTypes() as $issueType) { ?>
                <option value="<?php echo $issueType->getId(); ?>"<?php if ($issue->getType() === $issueType->getName()) { ?> selected="selected"<?php } ?>><?php echo $issueType->getName(); ?>
            <?php } ?>
        </select>
    </fieldset>
    <fieldset>
        <label>Affected Versions:</label>
        <select name="versions[]" multiple="multiple" size="5">
            <?php foreach ($versions as $version) { ?>
                <?php if ($version->isReleased()) { ?>
                    <option value="<?php echo $version->getId(); ?>"<?php if (isset($affectedVersions[$version->getId()])) { ?> selected="selected"<?php } ?>><?php echo $version->getName(); ?>
                <?php } ?>
            <?php } ?>
        </select>
    </fieldset>
    <fieldset>
        <label>Affected components:</label>
        <select name="components[]" multiple="multiple" size="5">
            <?php foreach ($components as $component) { ?>
                <option value="<?php echo $component->getId(); ?>"<?php if (isset($selectedComponents[$component->getId()])) { ?> selected="selected"<?php } ?>><?php echo $component->getName(); ?>
            <?php } ?>
        </select>
    </fieldset>
    <fieldset>
        <label>Summary:</label><br />
        <input type="text" name="name" value="<?php echo $issue->getName(); ?>" />
    </fieldset>
    <fieldset>
        <label>Description:</label><br />
        <textarea name="description"><?php echo $issue->getDescription(); ?></textarea>
    </fieldset>
    <fieldset>
        <?php
        $keywords = array();
        foreach ($issue->getKeywords() as $keyword) {
            $keywords[] = $keyword->getName();
        }
        ?>
        <label>Keywords:</label><br />
        <input type="text" name="keywords" value="<?php echo join(', ', $keywords); ?>" /><br />
        <small><em>Comma separated list of keywords.</em></small>
    </fieldset>

    <fieldset>
        <input type="submit" name="submit" value="Submit" class="btn" />
    </fieldset>
</form>
