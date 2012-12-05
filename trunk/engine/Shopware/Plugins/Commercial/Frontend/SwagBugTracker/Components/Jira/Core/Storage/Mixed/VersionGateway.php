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
 * Default implementation of the version gateway that utilizes mysql/pdo to read
 * project/issue versions from a database.
 */
class VersionGateway extends Gateway implements \Shopware\Components\Jira\SPI\Storage\VersionGateway
{
    /**
     * Returns a single version for the given id.
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
                SELECT `projectversion`.`ID`          AS `id`,
                       `projectversion`.`vname`       AS `name`,
                       `projectversion`.`DESCRIPTION` AS `description`,
                       `projectversion`.`RELEASED`    AS `released`,
                       `projectversion`.`RELEASEDATE` AS `releaseDate`
                  FROM `projectversion`
                 WHERE `projectversion`.`ID` = ?
                 LIMIT 1"
            ),
            array($id)
        );
    }

    /**
     * Returns a set of version where an issue will be fixed.
     *
     * @param string $issueId
     *
     * @return array
     */
    public function fetchFixedByIssueId($issueId)
    {
        return $this->fetchMultiple(
            $this->connection->prepare(
                "
                SELECT `projectversion`.`ID`          AS `id`,
                       `projectversion`.`vname`       AS `name`,
                       `projectversion`.`DESCRIPTION` AS `description`,
                       `projectversion`.`RELEASED`    AS `released`,
                       `projectversion`.`RELEASEDATE` AS `releaseDate`
                  FROM `projectversion`
            INNER JOIN `nodeassociation`
                    ON `nodeassociation`.`SOURCE_NODE_ID` = ?
                   AND `nodeassociation`.`ASSOCIATION_TYPE` = 'IssueFixVersion'
                   AND `nodeassociation`.`SINK_NODE_ID` = `projectversion`.`ID`
              ORDER BY `projectversion`.`SEQUENCE` ASC"
            ),
            array($issueId)
        );
    }

    /**
     * Returns a set of versions that are affected by an issue.
     *
     * @param integer $issueId
     *
     * @return array
     */
    public function fetchAffectedByIssueId($issueId)
    {
        return $this->fetchMultiple(
            $this->connection->prepare(
                "
                SELECT `projectversion`.`ID`          AS `id`,
                       `projectversion`.`vname`       AS `name`,
                       `projectversion`.`DESCRIPTION` AS `description`,
                       `projectversion`.`RELEASED`    AS `released`,
                       `projectversion`.`RELEASEDATE` AS `releaseDate`
                  FROM `projectversion`
            INNER JOIN `nodeassociation`
                    ON `nodeassociation`.`SOURCE_NODE_ID`   = ?
                   AND `nodeassociation`.`ASSOCIATION_TYPE` = 'IssueVersion'
                   AND `nodeassociation`.`SINK_NODE_ID`     = `projectversion`.`ID`
              ORDER BY `projectversion`.`SEQUENCE` ASC"
            ),
            array($issueId)
        );
    }

    /**
     * The method reads all versions that are defined for the project with the
     * given <b>$projectId</b>
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
                SELECT `projectversion`.`ID`          AS `id`,
                       `projectversion`.`vname`       AS `name`,
                       `projectversion`.`DESCRIPTION` AS `description`,
                       `projectversion`.`RELEASED`    AS `released`,
                       `projectversion`.`RELEASEDATE` AS `releaseDate`
                  FROM `projectversion`
                 WHERE `projectversion`.`PROJECT` = ?
              ORDER BY `projectversion`.`SEQUENCE` ASC"
            ),
            array($projectId)
        );
    }
}