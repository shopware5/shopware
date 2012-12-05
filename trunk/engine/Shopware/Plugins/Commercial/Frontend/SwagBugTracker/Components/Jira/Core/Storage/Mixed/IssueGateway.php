<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Components
 * @subpackage Jira
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 */

namespace Shopware\Components\Jira\Core\Storage\Mixed;

use \Shopware\Components\Jira\API\Model\Query;
use \Shopware\Components\Jira\API\Model\Query\Criterion;
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

use \Shopware\Components\Jira\Core\Rest\Client;

class IssueGateway extends Gateway implements \Shopware\Components\Jira\SPI\Storage\IssueGateway
{
    /**
     * Mapping between custom field identifiers and internal JIRA ids.
     *
     * @var array
     */
//    private $customFields = array(
//        'remote_user'  => 10500,
//        'remote_email' => 10501,
//        'is_public'    => 10202,
//        'voting'       => 10214
//    );
    
    private $customFields = array(
        'remote_user'  => 10600,
        'remote_email' => 10601,
        'is_public'    => 10202,
        'voting'       => 10214
    );

    /**
     * Value that identifies an issue as public.
     *
     * @var string
     */
    private $customFieldPublic = 10110;

    /**
     * @var string
     */
    private $reporter = 'm.pichler';

    /**
     * @var \Shopware\Components\Jira\Core\Rest\Client
     */
    private $client;

    /**
     * Instantiates a new issue gateway for the given database connection and
     * the given http client.
     *
     * @param \PDO $connection
     * @param \Shopware\Components\Jira\Core\Rest\Client $client
     */
    public function __construct(\PDO $connection, Client $client)
    {
        parent::__construct($connection);

        $this->client = $client;
    }

    /**
     * Reads a single issue by it's internal identifier.
     *
     * @param integer $id
     *
     * @return array
     */
    public function fetchById($id)
    {
        return $this->fetchSingle(
            $this->connection->prepare(
                $this->getCommonSelectSql() . "
             WHERE `jiraissue`.`ID` = ?
          GROUP BY `jiraissue`.`ID`
             LIMIT 1"
            ),
            array($id)
        );
    }

    /**
     * Reads a single issue by it's human readable issue key.
     *
     * @param string $key
     *
     * @return array
     */
    public function fetchByKey($key)
    {
        return $this->fetchSingle(
            $this->connection->prepare(
                $this->getCommonSelectSql() . "
             WHERE `jiraissue`.`pkey` = ?
          GROUP BY `jiraissue`.`ID`
             LIMIT 1"
            ),
            array($key)
        );
    }

    /**
     * Reads issues that belong to the given <b>$projectId</b>.
     *
     * @param integer $projectId
     * @param \Shopware\Components\Jira\API\Model\Query $query
     *        Query object with settings related to the returned issues.
     *
     * @return array[][]
     */
    public function fetchIssues($projectId, Query $query)
    {
        $rows = $this->fetchMultiple(
            $this->connection->prepare(
                sprintf(
                    $this->getCommonSelectSql() . "%s
         LEFT JOIN `issuelink`
                ON `issuelink`.`DESTINATION` = `jiraissue`.`ID`
               AND `issuelink`.`LINKTYPE`    = 10100
             WHERE `jiraissue`.`PROJECT` = ?
               AND `issuelink`.`DESTINATION` IS NULL%s
          GROUP BY `jiraissue`.`ID`
          ORDER BY `%s` %s
             LIMIT %d, %d",
                    $this->getJoinFilterSql($query->getCriteria()),
                    $this->getWhereFilterSql($query->getCriteria()),
                    $query->getOrderBy(),
                    $query->getOrderDir(),
                    ($query->getLength() * $query->getOffset()),
                    $query->getLength()
                )
            ),
            array($projectId)
        );

        $stmt  = $this->connection->query('SELECT FOUND_ROWS() as `total`');
        $total = $stmt->fetchColumn();
        $stmt->closeCursor();

        return array($total, $rows);
    }

    /**
     * Returns that part of an sql query, which is common in all select queries.
     *
     * @return string
     */
    private function getCommonSelectSql()
    {
        return "
            SELECT SQL_CALC_FOUND_ROWS
                   `jiraissue`.`ID`                 AS `id`,
                   `jiraissue`.`pkey`               AS `key`,
                   `jiraissue`.`SUMMARY`            AS `name`,
                   `jiraissue`.`DESCRIPTION`        AS `description`,
                   `jiraissue`.`CREATED`            AS `createdAt`,
                   `jiraissue`.`UPDATED`            AS `modifiedAt`,
                   `priority`.`pname`               AS `priority`,
                   `jiraissue`.`issuetype`          AS `type`,
                   IF (LENGTH(`cf_remote_user`.`STRINGVALUE`) > 0,
                       `cf_remote_user`.`STRINGVALUE`,
                       `cwd_user_reporter`.`display_name`
                   )                                AS `reporter`,
                   IF (LENGTH(`cwd_user_assignee`.`display_name`) > 0,
                       `cwd_user_assignee`.`display_name`,
                       'Unassigned'
                   )                                AS `assignee`,
                   `issuestatus`.`pname`            AS `status`,
                   (`cf_voting`.`STRINGVALUE` * 1)  AS `votes`
              FROM `jiraissue`
        INNER JOIN `issuestatus`
                ON `issuestatus`.`ID` = `jiraissue`.`issuestatus`
        INNER JOIN `priority`
                ON `priority`.`ID` = `jiraissue`.`PRIORITY`
        INNER JOIN `customfieldvalue` AS `cf_public_available`
                ON `cf_public_available`.`ISSUE`       = `jiraissue`.`ID`
               AND `cf_public_available`.`CUSTOMFIELD` = {$this->customFields['is_public']}
               AND `cf_public_available`.`STRINGVALUE` = {$this->customFieldPublic}
        INNER JOIN `cwd_user` AS `cwd_user_reporter`
                ON `cwd_user_reporter`.`user_name` = `jiraissue`.`REPORTER`
         LEFT JOIN `cwd_user` AS `cwd_user_assignee`
                ON `cwd_user_assignee`.`user_name` = `jiraissue`.`ASSIGNEE`
         LEFT JOIN `customfieldvalue` AS `cf_remote_user`
                ON `cf_remote_user`.`ISSUE`       = `jiraissue`.`ID`
               AND `cf_remote_user`.`CUSTOMFIELD` = {$this->customFields['remote_user']}
         LEFT JOIN `customfieldvalue` AS `cf_voting`
                ON `cf_voting`.`ISSUE`       = `jiraissue`.`ID`
               AND `cf_voting`.`CUSTOMFIELD` = {$this->customFields['voting']}
        ";
    }

    /**
     * Creates additional sql filters based on the given criterion objects.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion[] $criteria
     *
     * @return string
     */
    private function getJoinFilterSql(array $criteria)
    {
        $sql = '';
        foreach ($criteria as $criterion) {
            switch (true) {
                case ($criterion instanceof AffectedVersion):
                    $sql .= $this->getFilterSqlForAffectedVersion($criterion);
                    break;

                case ($criterion instanceof FixVersion):
                    $sql .= $this->getFilterSqlForFixVersion($criterion);
                    break;

                case ($criterion instanceof Component):
                    $sql .= $this->getFilterSqlForComponent($criterion);
                    break;

                case ($criterion instanceof Keyword):
                    $sql .= $this->getFilterSqlForKeyword($criterion);
                    break;
            }
        }
        return $sql;
    }

    /**
     * Returns the required sql fragment to filter issues by their affected
     * version.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion\AffectedVersion $criterion
     *
     * @return string
     */
    private function getFilterSqlForAffectedVersion(AffectedVersion $criterion)
    {
        return sprintf(
            "
        INNER JOIN `nodeassociation` AS `na_affected_version`
                ON `na_affected_version`.`SOURCE_NODE_ID`   = `jiraissue`.`ID`
               AND `na_affected_version`.`ASSOCIATION_TYPE` = 'IssueVersion'
               AND `na_affected_version`.`SINK_NODE_ID` IN (%s)",
            join(
                ', ',
                array_map(
                    array($this->connection, 'quote'),
                    $criterion->getVersions()
                )
            )
        );
    }

    /**
     * Generates the required sql fragment to filter issues by their fix version.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion\FixVersion $criterion
     *
     * @return string
     */
    private function getFilterSqlForFixVersion(FixVersion $criterion)
    {
        return sprintf(
            "
        INNER JOIN `nodeassociation` AS `na_fix_version`
                ON `na_fix_version`.`SOURCE_NODE_ID`   = `jiraissue`.`ID`
               AND `na_fix_version`.`ASSOCIATION_TYPE` = 'IssueFixVersion'
               AND `na_fix_version`.`SINK_NODE_ID`     = %s",
            $this->connection->quote($criterion->getVersion(), \PDO::PARAM_INT)
        );
    }

    /**
     * Generates the required sql fragment to filter issues by on of their
     * associated components.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion\Component $component
     *
     * @return string
     */
    private function getFilterSqlForComponent(Component $component)
    {
        return sprintf(
            "
        INNER JOIN `nodeassociation` AS `na_component`
                ON `na_component`.`SOURCE_NODE_ID`   = `jiraissue`.`ID`
               AND `na_component`.`ASSOCIATION_TYPE` = 'IssueComponent'
               AND `na_component`.`SINK_NODE_ID`     = %s",
            $this->connection->quote($component->getComponent(), \PDO::PARAM_INT)
        );
    }

    /**
     * Generates the required sql fragment to filter those issues that are tagged
     * with a special keyword.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion\Keyword $keyword
     *
     * @return string
     */
    private function getFilterSqlForKeyword(Keyword $keyword)
    {
        return sprintf(
            "
        INNER JOIN `label`
                ON `label`.`ISSUE` = `jiraissue`.`ID`
               AND `label`.`LABEL` = %s",
            $this->connection->quote($keyword->getKeyword(), \PDO::PARAM_STR)
        );
    }

    /**
     * Creates additional sql filters based on the given criterion objects.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion[] $criteria
     *
     * @return string
     */
    private function getWhereFilterSql(array $criteria)
    {
        $sql = '';
        foreach ($criteria as $criterion) {
            switch (true) {
                case ($criterion instanceof Type):
                    $sql .= $this->getFilterSqlForIssueType($criterion);
                    break;

                case ($criterion instanceof Status):
                    $sql .= $this->getFilterSqlForIssueStatus($criterion);
                    break;

                case ($criterion instanceof Priority):
                    $sql .= $this->getFilterSqlForPriority($criterion);
                    break;

                case ($criterion instanceof Reporter):
                    $sql .= $this->getFilterSqlForReporter($criterion);
                    break;

                case ($criterion instanceof SearchText):
                    $sql .= $this->getFilterSqlForSearch($criterion);
                    break;

                case ($criterion instanceof DateRange):
                    $sql .= $this->getFilterSqlForDateRange($criterion);
                    break;
            }
        }
        return $sql;
    }

    /**
     * Generates an additional sql fragement that filter's issues by their issue
     * type.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion\Type $type
     *
     * @return string
     */
    private function getFilterSqlForIssueType(Type $type)
    {
        return sprintf(
            "
               AND `jiraissue`.`issuetype` = %s",
            $this->connection->quote($type->getType(), \PDO::PARAM_INT)
        );
    }

    /**
     * Generates an additional sql fragement that filter's issues by their issue
     * status.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion\Status $status
     *
     * @return string
     */
    private function getFilterSqlForIssueStatus(Status $status)
    {
        return sprintf(
            "
               AND `jiraissue`.`issuestatus` IN (%s)",
            join(
                ', ',
                array_map(
                    array($this->connection, 'quote'),
                    $status->getStatus()
                )
            )
        );
    }

    /**
     * Generates an additional sql fragement that filter's issues by their
     * priority.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion\Priority $priority
     *
     * @return string
     */
    private function getFilterSqlForPriority(Priority $priority)
    {
        return sprintf(
            "
               AND `jiraissue`.`PRIORITY` = %s",
            $this->connection->quote($priority->getPriority(), \PDO::PARAM_INT)
        );
    }

    /**
     * Generates an additional sql fragement that filter's issues by their
     * reporter.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion\Reporter $reporter
     *
     * @return string
     */
    private function getFilterSqlForReporter(Reporter $reporter)
    {
        return sprintf(
            "
               AND `cf_remote_user`.`STRINGVALUE` = %s",
            $this->connection->quote($reporter->getReporter(), \PDO::PARAM_STR)
        );
    }

    /**
     * Generates an additional sql fragement that filter's issues by their
     * create date. The sql fragment will only allow issues that were created
     * between the given date range.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion\DateRange $dateRange
     *
     * @return string
     */
    private function getFilterSqlForDateRange(DateRange $dateRange)
    {
        return sprintf(
            "
               AND `jiraissue`.`CREATED`
           BETWEEN FROM_UNIXTIME(%s)
               AND FROM_UNIXTIME(%s)",
            $this->connection->quote($dateRange->getFrom()->getTimestamp(), \PDO::PARAM_INT),
            $this->connection->quote($dateRange->getTo()->getTimestamp(), \PDO::PARAM_INT)
        );
    }

    /**
     * Generates an additional sql fragement that filter's issues by a free text
     * search value. The sql fragment will search in the issue's summary, key
     * and description.
     *
     * @param \Shopware\Components\Jira\API\Model\Query\Criterion\SearchText $searchText
     *
     * @return string
     */
    private function getFilterSqlForSearch(SearchText $searchText)
    {
        $value = $this->connection->quote("%{$searchText->getSearchText()}%");
        
        //FIX: This doesn´t work!
      	//$value = $this->connection->quote("%{$searchText->getSearchText()}%", \PDO::PARAM_STR);

        return sprintf(
            "
               AND (`jiraissue`.`pkey`        LIKE %s OR
                    `jiraissue`.`SUMMARY`     LIKE %s OR
                    `jiraissue`.`DESCRIPTION` LIKE %s
               )",
            $value,
            $value,
            $value
        );
    }

    /**
     * Reads all sub issues of the issue identified by <b>$id</b>.
     *
     * @param integer $id
     *
     * @return array[][]
     */
    public function fetchSubIssues($id)
    {
        $stmt = $this->connection->prepare(
            $this->getCommonSelectSql() . "
        INNER JOIN `issuelink`
                ON `issuelink`.`DESTINATION` = `jiraissue`.`ID`
               AND `issuelink`.`LINKTYPE`    = 10100
             WHERE `issuelink`.`SOURCE`      = ?
          GROUP BY `jiraissue`.`ID`
          ORDER BY `issuelink`.`SEQUENCE` ASC"
        );

        $stmt->execute(array($id));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $rows;
    }

    /**
     * @param array $data
     *
     * @return mixed
     * @throws \Shopware\Components\Jira\API\Exception\UnauthorizedException
     */
    public function store(array $data)
    {
        $remoteUserField  = $this->getCustomFieldKey('remote_user');
        $remoteEmailField = $this->getCustomFieldKey('remote_email');
        $isPublicField    = $this->getCustomFieldKey('is_public');

        $versions = array();
        foreach ($data['versions'] as $versionId) {
            $versions[] = array('id' => "{$versionId}");
        }

        $components = array();
        foreach ($data['components'] as $componentId) {
            $components[] = array('id' => "{$componentId}");
        }

        $json = array(
            'fields' => array(
                'project'         => array('id' => $data['project']),
                'summary'         => $data['name'],
                'description'     => $data['description'],
                //FIX: The field reporter is not available!
//                'reporter'        => array('name' => 'd.scharfenberg'),
                'issuetype'       => array('id' => $data['type']),
                $remoteUserField  => $data['remoteUser'],
                $remoteEmailField => $data['remoteEmail'],
                $isPublicField    => array('id' => "{$this->customFieldPublic}"),
//                'versions'        => $versions,
				//FIX: Set fix-version
                'fixVersions'     => $versions,
//                'components'      => $components,
//                'labels'          => $data['keywords']
            )
        );

        $response = $this->client->post('rest/api/{version}/issue', $json);

        return $response->key;
    }

    public function update($id, array $data)
    {
        $voteCustomField = $this->getCustomFieldKey('voting');

        $json = array(
            'fields' => array(
                'summary'        => $data['name'],
                'description'    => $data['description'],
                'issuetype'      => array('id' => $data['type']),
                $voteCustomField => "{$data['votes']}"
            )
        );

        if (isset($data['keywords'])) {
            $json['fields']['labels'] = $data['keywords'];

        }
        if (isset($data['versions'])) {
            foreach ($data['versions'] as $i => $versionId) {
                $json['fields']['versions'][$i] = array('id' => "{$versionId}");
            }
        }
        if (isset($data['components'])) {
            foreach ($data['components'] as $i => $componentId) {
                $json['fields']['components'][$i] = array('id' => "{$componentId}");
            }
        }

        $this->client->put(sprintf('rest/api/{version}/issue/%d', $id), $json);
    }

    /**
     * Deletes an issue by it's id.
     *
     * @param integer $id
     *
     * @return void
     */
    public function delete($id)
    {
        $this->client->delete(sprintf('rest/api/{version}/issue/%s', $id));
    }

    /**
     * Returns the customfield identifier as it is used by the JIRA webservice
     * for the custom issue properties.
     *
     * @param string $name
     * @return string
     */
    private function getCustomFieldKey($name)
    {
        return sprintf('customfield_%d', $this->customFields[$name]);
    }
}