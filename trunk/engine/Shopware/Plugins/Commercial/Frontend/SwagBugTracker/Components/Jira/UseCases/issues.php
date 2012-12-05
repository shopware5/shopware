<?php
use \Shopware\Components\Jira\API\Model\Query;
use \Shopware\Components\Jira\API\Model\Query\Criterion\AffectedVersion;
use \Shopware\Components\Jira\API\Model\Query\Criterion\Component;
use \Shopware\Components\Jira\API\Model\Query\Criterion\DateRange;
use \Shopware\Components\Jira\API\Model\Query\Criterion\FixVersion;
use \Shopware\Components\Jira\API\Model\Query\Criterion\Keyword;
use \Shopware\Components\Jira\API\Model\Query\Criterion\Priority;
use \Shopware\Components\Jira\API\Model\Query\Criterion\Reporter;
use \Shopware\Components\Jira\API\Model\Query\Criterion\SearchText;
use \Shopware\Components\Jira\API\Model\Query\Criterion\Status;
use \Shopware\Components\Jira\API\Model\Query\Criterion\Type;

?><!DOCTYPE>
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

        // Get the utilized services
        $projectService   = $context->getProjectService();
        $issueService     = $context->getIssueService();
        $versionService   = $context->getVersionService();
        $componentService = $context->getComponentService();

        // Load project
        $project = $projectService->load((int) $_GET['p']);

        $query = new Query();
        if (isset($_GET['b'])) {
            $query->setOrderBy($_GET['b']);
        } else {
            $query->setOrderBy(Query::ORDER_BY_CREATED_AT);
        }
        if (isset($_GET['d'])) {
            $query->setOrderDir($_GET['d']);
        } else {
            $query->setOrderDir(Query::ORDER_DESC);
        }
        if (isset($_GET['o'])) {
            $query->setOffset((int) $_GET['o']);
        }

        $params = array(
            'o'  =>  $query->getOffset(),
            'p'  =>  $project->getId(),
            'b'  =>  $query->getOrderBy(),
            'd'  =>  $query->getOrderDir()
        );

        if (isset($_GET['v'])) {
            $query->addCriterion(new AffectedVersion($_GET['v']));
            $params['v'] = $_GET['v'];
        }
        if (isset($_GET['fv'])) {
            $query->addCriterion(new FixVersion($_GET['fv']));
            $params['fv'] = $_GET['fv'];
        }
        if (isset($_GET['it'])) {
            $query->addCriterion(new Type($_GET['it']));
            $params['it'] = $_GET['it'];
        }
        if (isset($_GET['is'])) {
            $query->addCriterion(new Status($_GET['is']));
            $params['is'] = $_GET['is'];
        }
        if (isset($_GET['ip'])) {
            $query->addCriterion(new Priority($_GET['ip']));
            $params['ip'] = $_GET['ip'];
        }
        if (isset($_GET['c'])) {
            $query->addCriterion(new Component($_GET['c']));
            $params['c'] = $_GET['c'];
        }
        if (isset($_GET['r'])) {
            $query->addCriterion(new Reporter($_GET['r']));
            $params['r'] = $_GET['r'];
        }
        if (isset($_GET['k'])) {
            $query->addCriterion(new Keyword($_GET['k']));
            $params['k'] = $_GET['k'];
        }
        if (isset($_GET['s'])) {
            $query->addCriterion(new SearchText($_GET['s']));
            $params['s'] = $_GET['s'];
        }
        if (isset($_GET['t'])) {
            list($from, $to) = explode('-', $_GET['t']);

            $query->addCriterion(new DateRange(new \DateTime($from), new \DateTime($to)));
            $params['t'] = $_GET['t'];
        }

        $issues = $issueService->loadIssues($project, $query);

        // Load all versions available in this project
        $versions = $versionService->loadByProject($project);

        // Load all components available in this project
        $components = $componentService->loadByProject($project);

        ?>

        <div>
            <h2>
                Filters
                <small>
                    <a href="issues.php?p=<?php echo $project->getId(); ?>">(Reset all)</a>
                </small>
            </h2>
            <div style="float: left; margin-right: 10px;">
                <h3>Affected Version</h3>
                <ul>
                    <?php foreach ($versions as $version) { ?>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('v' => $version->getId(), 'o' => 0))); ?>">
                            <?php echo $version->getName(); ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>

            <div style="float: left; margin-right: 10px;">
                 <h3>Fix Version</h3>
                <ul>
                    <?php foreach ($versions as $version) { ?>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('fv' => $version->getId(), 'o' => 0))); ?>">
                            <?php echo $version->getName(); ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>

            <div style="float: left; margin-right: 10px;">
                <h3>Type</h3>
                <ul>
                    <?php foreach (\Shopware\Components\Jira\API\Model\IssueType::getIssueTypes() as $issueType) { ?>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('it' => $issueType->getId(), 'o' => 0))); ?>">
                            <?php echo $issueType->getName(); ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>

            <div style="float: left; margin-right: 10px;">
                <h3>Status</h3>
                <ul>
                    <?php foreach (\Shopware\Components\Jira\API\Model\IssueStatus::getIssueStatus() as $issueStatus) { ?>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('is' => $issueStatus->getId(), 'o' => 0))); ?>">
                            <?php echo $issueStatus->getName(); ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>

            <div style="float: left; margin-right: 10px;">
                <h3>Priority</h3>
                <ul>
                    <?php foreach (\Shopware\Components\Jira\API\Model\IssuePriority::getIssuePriority() as $issuePriority) { ?>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('ip' => $issuePriority->getId(), 'o' => 0))); ?>">
                            <?php echo $issuePriority->getName(); ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>

            <div style="float: left; margin-right: 10px;">
                <h3>Component</h3>
                <ul>
                    <?php foreach ($components as $component) { ?>
                        <li>
                            <a href="issues.php?<?php echo http_build_query(array_merge($params, array('c' => $component->getId(), 'o' => 0))); ?>">
                                <?php echo $component->getName(); ?>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <div style="float: left; margin-right: 10px;">
                <h3>Reporter</h3>
                <ul>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('r' => 'Qafoo GmbH', 'o' => 0))); ?>">
                            Qafoo GmbH
                        </a>
                    </li>
                </ul>
            </div>

            <div style="float: left; margin-right: 10px;">
                <h3>Search</h3>
                <ul>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('s' => 'Artikel', 'o' => 0))); ?>">
                            Artikel
                        </a>
                    </li>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('s' => 'Müll', 'o' => 0))); ?>">
                            Müll
                        </a>
                    </li>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('s' => 'auf Liefer', 'o' => 0))); ?>">
                            auf Liefer
                        </a>
                    </li>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('s' => 'zeichensatz', 'o' => 0))); ?>">
                            zeichensatz
                        </a>
                    </li>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('s' => 'shopware', 'o' => 0))); ?>">
                            shopware
                        </a>
                    </li>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('s' => 'SW-41', 'o' => 0))); ?>">
                            SW-41
                        </a>
                    </li>
                </ul>
            </div>

            <div style="float: left; margin-right: 10px;">
                <h3>Date Range</h3>
                <ul>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('t' => '2012/04/01-2012/05/01', 'o' => 0))); ?>">
                            2012/04/01 - 2012/05/01
                        </a>
                    </li>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('t' => '2012/03/01-2012/04/01', 'o' => 0))); ?>">
                            2012/03/01 - 2012/04/01
                        </a>
                    </li>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('t' => '2012/02/01-2012/03/01', 'o' => 0))); ?>">
                            2012/02/01 - 2012/03/01
                        </a>
                    </li>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('t' => '2012/01/01-2012/02/01', 'o' => 0))); ?>">
                            2012/01/01 - 2012/02/01
                        </a>
                    </li>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('t' => '2011/06/01-2012/01/01', 'o' => 0))); ?>">
                            2011/06/01 - 2012/01/01
                        </a>
                    </li>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('t' => '2010/01/01-2011/06/01', 'o' => 0))); ?>">
                            2010/01/01 - 2011/06/01
                        </a>
                    </li>
                </ul>
            </div>

            <div style="float: left; margin-right: 10px;">
                <h3>Keyword</h3>
                <ul>
                    <li>
                        <a href="issues.php?<?php echo http_build_query(array_merge($params, array('k' => 'shopware', 'o' => 0))); ?>">
                            shopware
                        </a>
                    </li>
                </ul>
            </div>

            <br style="clear: left" />
        </div>

        <?php
        function getSort($name)
        {
            global $params;

            if ($params['b'] === $name) {
                $order = ($params['d'] === 'asc' ? 'desc' : 'asc');
            } else {
                $order = 'asc';
            }
            return array_merge($params, array('b' => $name, 'd' => $order));
        }
        ?>

        <table class="table table-striped">
            <thead>
            <tr>
                <td>
                    <?php if ($context->canUser('create')) { ?>
                        <a class="btn" href="create.php?p=<?php echo $project->getId(); ?>">Create issue</a>
                    <?php } ?>
                </td>
                <td colspan="8">
                    <div class="pagination pagination-centered">
                        <ul>
                            <?php for ($i = 0; $i < ceil($issues->getTotal() / $query->getLength()); ++$i) { ?>
                            <li<?php if ($params['o'] == $i) { ?> class="active" <?php } ?>>
                                <a href="issues.php?<?php echo http_build_query(array_merge($params, array('o' => $i))); ?>">
                                    <?php echo ($i + 1); ?>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </td>
            </tr>
            </thead>
            <thead>
                <tr>
                    <th>
                        <a href="issues.php?<?php echo http_build_query(getSort('name')); ?>">Name</a>
                    </th>
                    <th>
                        <a href="issues.php?<?php echo http_build_query(getSort('type')); ?>">Type</a>
                    </th>
                    <th>
                        <a href="issues.php?<?php echo http_build_query(getSort('status')); ?>">Status</a>
                    </th>
                    <th>
                        <a href="issues.php?<?php echo http_build_query(getSort('priority')); ?>">Priority</a>
                    </th>
                    <th>
                        <a href="issues.php?<?php echo http_build_query(getSort('reporter')); ?>">Reporter</a>
                    </th>
                    <th>
                        <a href="issues.php?<?php echo http_build_query(getSort('createdAt')); ?>">Created</a>
                    </th>
                    <th>
                        <a href="issues.php?<?php echo http_build_query(getSort('assignee')); ?>">Assignee</a>
                    </th>
                    <th>
                        <a href="issues.php?<?php echo http_build_query(getSort('modifiedAt')); ?>">Updated</a>
                    </th>
                    <th>
                        <a href="issues.php?<?php echo http_build_query(getSort('votes')); ?>">Votes</a>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="9">
                        <div class="pagination pagination-centered">
                        <ul>
                            <?php for ($i = 0; $i < ceil($issues->getTotal() / $query->getLength()); ++$i) { ?>
                                <li<?php if ($params['o'] == $i) { ?> class="active" <?php } ?>>
                                    <a href="issues.php?<?php echo http_build_query(array_merge($params, array('o' => $i))); ?>">
                                        <?php echo ($i + 1); ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                        </div>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php foreach ($issues as $issue) { ?>
                    <tr>
                        <td style="text-overflow: ellipsis; width: 400px;">
                            <a href="issue.php?p=<?php echo $project->getId(); ?>&amp;i=<?php echo $issue->getId(); ?>">
                                <?php echo $issue->getName(); ?>
                            </a>
                            <strong>
                                (<?php echo $issue->getKey(); ?>)
                            </strong>
                        </td>
                        <td>
                            <?php echo $issue->getType(); ?>
                        </td>
                        <td>
                            <?php echo $issue->getStatus(); ?>
                        </td>
                        <td>
                            <?php echo $issue->getPriority(); ?>
                        </td>
                        <td>
                            <?php echo $issue->getReporter(); ?>
                        </td>
                        <td>
                            <?php echo $issue->getCreatedAt()->format("Y/m/d"); ?>
                        </td>
                        <td>
                            <?php echo $issue->getAssignee(); ?>
                        </td>
                        <td>
                            <?php echo $issue->getModifiedAt()->format("Y/m/d"); ?>
                        </td>
                        <td>
                            <?php echo $issue->getVotes(); ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </body>
</html>
