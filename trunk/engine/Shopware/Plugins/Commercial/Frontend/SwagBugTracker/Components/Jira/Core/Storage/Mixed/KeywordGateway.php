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

class KeywordGateway extends Gateway implements \Shopware\Components\Jira\SPI\Storage\KeywordGateway
{
    /**
     * Returns all keywords for the given issue identifier.
     *
     * @param integer $issueId
     *
     * @return array
     */
    public function fetchByIssueId($issueId)
    {
        $stmt = $this->connection->prepare(
            "
            SELECT `label`.`ID`    AS `id`,
                   `label`.`LABEL` AS `name`
              FROM `label`
             WHERE `label`.`ISSUE` = ?
          ORDER BY `label`.`ID` DESC
          "
        );

        $stmt->execute(array($issueId));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $rows;
    }
}