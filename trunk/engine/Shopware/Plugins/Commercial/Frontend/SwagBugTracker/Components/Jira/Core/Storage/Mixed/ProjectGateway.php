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
 * Gateway implementation that handles project related queries based on a
 * relational database.
 */
class ProjectGateway extends Gateway implements \Shopware\Components\Jira\SPI\Storage\ProjectGateway
{
    /**
     * Returns a single project for the given identifier.
     *
     * @param integer $id
     *
     * @return array
     */
    public function fetchById($id)
    {
        $stmt = $this->connection->prepare(
            "
            SELECT `project`.`ID`          AS `id`,
                   `project`.`pkey`        AS `key`,
                   `project`.`pname`       AS `name`,
                   `project`.`DESCRIPTION` AS `description`
              FROM `project`
             WHERE `project`.`ID` = ?
          "
        );

        return $this->fetchSingle($stmt, array($id));
    }

    /**
     * Returns a single project for the human readable project key.
     *
     * @param string $key
     *
     * @return array
     */
    public function fetchByKey($key)
    {
        $stmt = $this->connection->prepare(
            "
            SELECT `project`.`ID`          AS `id`,
                   `project`.`pkey`        AS `key`,
                   `project`.`pname`       AS `name`,
                   `project`.`DESCRIPTION` AS `description`
              FROM `project`
             WHERE `project`.`pkey` = ?"
        );

        return $this->fetchSingle($stmt, array($key));
    }

    /**
     * Fetches the data of all projects that are identified by the given project
     * keys.
     *
     * @param array $keys
     *
     * @return array
     */
    public function fetchByKeys(array $keys)
    {
        $keys = array_map(array($this->connection, 'quote'), $keys);

        return $this->fetchMultiple(
            $this->connection->prepare(
                sprintf(
                    "
                    SELECT `project`.`ID`          AS `id`,
                           `project`.`pkey`        AS `key`,
                           `project`.`pname`       AS `name`,
                           `project`.`DESCRIPTION` AS `description`
                      FROM `project`
                     WHERE `project`.`pkey` IN (%s)
                  ORDER BY `project`.`pname` ASC",
                    join(',', $keys)
                )
            )
        );
    }
}