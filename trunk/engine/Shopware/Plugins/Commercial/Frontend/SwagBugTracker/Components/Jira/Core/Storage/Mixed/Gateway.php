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

use \Shopware\Components\Jira\API\Exception\NotFoundException;

/**
 * Abstract base class for gateways.
 */
abstract class Gateway
{
    /**
     * @var \PDO
     */
    protected $connection;

    /**
     * Instantiates a new gateway with the given connection.
     *
     * @param \PDO $connection
     */
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Fetches a single record and returns the found result as an array. If the
     * given statement does not return a row this method will throw an exception.
     *
     * @param \PDOStatement $stmt
     * @param array $parameters
     *
     * @return array
     * @throws \Shopware\Components\Jira\API\Exception\NotFoundException
     */
    protected function fetchSingle(\PDOStatement $stmt, array $parameters)
    {
        $stmt->execute($parameters);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (false === is_array($row)) {
            throw new NotFoundException(
                sprintf(
                    'No matching %s found.',
                    substr(get_class($this), strrpos(get_class($this), '\\') + 1, -7)
                )
            );
        }

        return $row;
    }

    protected function fetchMultiple(\PDOStatement $stmt, array $parameters = array())
    {
        $stmt->execute($parameters);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $rows;
    }
}