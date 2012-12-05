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

/**
 * Default implementation of the component service interface.
 *
 * This implementation utilizes mysql/pdo to read JIRA components from the
 * database.
 */
class ComponentGateway extends Gateway implements \Shopware\Components\Jira\SPI\Storage\ComponentGateway
{
    /**
     * Returns a single component identified by the given <b>$id</b>.
     *
     * @param integer $id
     *
     * @return array
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    public function fetchById($id)
    {
        return $this->fetchSingle(
            $this->connection->prepare(
                "
                SELECT `component`.`ID`          AS `id`,
                       `component`.`PROJECT`     AS `projectId`,
                       `component`.`cname`       AS `name`,
                       `component`.`description` AS `description`
                  FROM `component`
                 WHERE `component`.`ID` = ?"
            ),
            array($id)
        );
    }

    /**
     * Returns all components for the given project identifier.
     *
     * @param integer $projectId
     *
     * @return array
     */
    public function fetchByProjectId($projectId)
    {
        return $this->fetchMultiple(
            $this->connection->prepare(
                "
                SELECT `component`.`ID`          AS `id`,
                       `component`.`PROJECT`     AS `projectId`,
                       `component`.`cname`       AS `name`,
                       `component`.`description` AS `description`
                  FROM `component`
                 WHERE `component`.`PROJECT` = ?
              ORDER BY `component`.`cname` ASC"
            ),
            array($projectId)
        );
    }

    /**
     * Returns all keywords for the given issue identifier.
     *
     * @param integer $issueId
     *
     * @return array
     */
    public function fetchByIssueId($issueId)
    {
        return $this->fetchMultiple(
            $this->connection->prepare(
                "
                SELECT `component`.`ID`          AS `id`,
                       `component`.`PROJECT`     AS `projectId`,
                       `component`.`cname`       AS `name`,
                       `component`.`description` AS `description`
                  FROM `nodeassociation`
            INNER JOIN `jiraissue`
                    ON `jiraissue`.`ID` = `nodeassociation`.`SOURCE_NODE_ID`
                   AND `jiraissue`.`ID` = ?
            INNER JOIN `component`
                    ON `component`.`ID` = `nodeassociation`.`SINK_NODE_ID`
                 WHERE `nodeassociation`.`ASSOCIATION_TYPE` = 'IssueComponent'
              GROUP BY `component`.`ID`
              ORDER BY `component`.`cname` ASC"
            ),
            array($issueId)
        );
    }
}