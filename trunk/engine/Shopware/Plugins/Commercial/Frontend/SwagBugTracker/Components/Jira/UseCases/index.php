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

        require_once __DIR__ . '/init.php';

        $projectService = $context->getProjectService();

        ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projectService->loadByKeys($projectKeys) as $project) { ?>
                    <tr>
                        <td>
                            <a href="issues.php?p=<?php echo $project->getId(); ?>&amp;is[]=1&amp;is[]=3&amp;is[]=4">
                                <?php echo $project->getName(); ?>
                            </a>
                            <strong>
                                (<?php echo $project->getKey(); ?>)
                            </strong>
                        </td>
                        <td>
                            <?php echo $project->getDescription(); ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </body>
</html>
