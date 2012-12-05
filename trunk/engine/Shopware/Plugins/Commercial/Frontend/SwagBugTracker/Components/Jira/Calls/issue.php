<!DOCTYPE>
<html>
<head>
    <title>JIRA Stuff</title>
    <meta charset="utf-8" />
    <link href="https://raw.github.com/twitter/bootstrap/master/docs/assets/css/bootstrap.css" rel="stylesheet" />
</head>
    <body>
        <?php
        /* @var $context \Shopware\Components\Jira\API\Context */
        exit;
        require_once __DIR__ . '/init.php';

        // Get the utilized services
        $projectService = $context->getProjectService();
        $issueService   = $context->getIssueService();
        $commentService = $context->getCommentService();
        
        $sw_project = $projectService->loadByKeys('SW');
        
        $query = new Shopware\Components\Jira\API\Model\Query();
        
        echo "<pre>";
        $searchResult = $issueService->loadIssues(current($sw_project), $query);
        $total = $searchResult->getTotal();
        $issues = $searchResult->getIssues();
        if(!empty($total)) {
        	foreach ($issues as $issue) {
        		echo $issue->getKey();
        		echo "<br>";
        		echo $issue->getName();
        		echo "<br>";
        	}
        }
        die;
        

        $project = $projectService->load((int) $_GET['p']);
        $issue   = $issueService->load((int) $_GET['i']);

        if (isset($_POST['comment']) && $context->canUser('comment')) {
            $commentCreate = $commentService->newCommentCreate($issue);
            $commentCreate->setAuthor($context->getCurrentRemoteUser());
            $commentCreate->setBody($_POST['comment']);

            $commentService->create($commentCreate);
        }

        $comments = $commentService->loadByIssue($issue);

        ?>
        <h1>
            <a href="issues.php?p=<?php echo $project->getId(); ?>">
                <?php echo $project->getName(); ?></a>
            /
            <?php echo $issue->getKey(); ?>
        </h1>
        <h2>
            Issue Type: <?php echo $issue->getType(); ?>;
            Status: <?php echo $issue->getStatus(); ?>;
            Priority: <?php echo $issue->getPriority(); ?>
        </h2>
        <p><?php echo $issue->getName(); ?></p>
        <?php if ($issue->getVersions()) { ?>
            <h3>Versions</h3>
            <ul>
                <?php foreach ($issue->getVersions() as $version) { ?>
                    <li>
                        <a href="issues.php?p=<?php echo $project->getId(); ?>&amp;v=<?php echo $version->getId(); ?>">
                            <?php echo $version->getName(); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
        <?php if ($issue->getComponents()) { ?>
            <h3>Components</h3>
            <ul>
                <?php foreach ($issue->getComponents() as $component) { ?>
                    <li>
                        <a href="issues.php?p=<?php echo $project->getId(); ?>&amp;c=<?php echo $component->getId(); ?>">
                            <?php echo $component->getName(); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
        <?php if ($issue->getKeywords()) { ?>
            <h3>Keywords</h3>
            <ul>
                <?php foreach ($issue->getKeywords() as $keyword) { ?>
                    <li>
                        <a href="issues.php?p=<?php echo $project->getId(); ?>&amp;k=<?php echo $keyword->getName(); ?>">
                            <?php echo $keyword->getName(); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
        <h3>
            Votes: <?php echo $issue->getVotes(); ?>
            <a class="btn" href="vote.php?p=<?php echo $project->getId(); ?>&amp;i=<?php echo $issue->getId(); ?>&amp;v=+1">+1</a>
            <a class="btn" href="vote.php?p=<?php echo $project->getId(); ?>&amp;i=<?php echo $issue->getId(); ?>&amp;v=-1">-1</a>
        </h3>

        <h3>Description:</h3>
        <p><?php echo $issue->getDescription(); ?></p>

        <?php if ($issue->getSubIssues()) { ?>
            <h3>Sub issues</h3>
            <ul>
                <?php foreach ($issue->getSubIssues() as $subIssue) { ?>
                    <li>
                        <a href="issue.php?p=<?php echo $project->getId(); ?>&amp;i=<?php echo $subIssue->getId(); ?>">
                            <?php echo $subIssue->getName(); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>

        <?php if ($context->canUser('edit', $issue) || $context->canUser('delete', $issue)) { ?>
            <form action="delete.php" method="post">
                <fieldset>
                    <?php if ($context->canUser('edit', $issue)) { ?>
                        <a href="edit.php?i=<?php echo $issue->getId(); ?>&amp;p=<?php echo $project->getId(); ?>" class="btn">Edit</a>
                    <?php } ?>
                    <?php if ($context->canUser('delete', $issue)) { ?>
                        <input type="hidden" name="i" value="<?php echo $issue->getId(); ?>" />
                        <input type="hidden" name="p" value="<?php echo $project->getId(); ?>" />
                        <input type="submit" name="delete" value="Delete" class="btn" />
                    <?php } ?>
                </fieldset>
            </form>
        <?php } ?>

        <h3>Comments</h3>
        <ol>
            <?php foreach ($comments as $comment) { ?>
                <li>
                    <small>
                        Created at
                        <em><?php echo $comment->getCreatedAt()->format("Y/m/d H:i"); ?></em>
                        by
                        <em><?php echo $comment->getAuthor(); ?></em>
                    </small><br />
                    <?php echo $comment->getDescription(); ?>
                </li>
            <?php } ?>
        </ol>

        <?php if ($context->canUser('comment')) { ?>
            <form method="post">
                <textarea name="comment"></textarea>
                <br />
                <input type="submit" name="submit" value="Submit" class="btn" />
            </form>
        <?php } ?>
    </body>
</html>
