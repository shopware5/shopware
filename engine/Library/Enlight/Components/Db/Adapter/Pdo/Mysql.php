<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

use Doctrine\DBAL\Connection;

/**
 * Pdo adapter for Mysql.
 *
 * The Enlight_Components_Db_Adapter_Pdo_Mysql extends the zend pdo mysql adapter to format dates automatically
 * and removes for safety reasons, the database connection settings.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql
{
    /**
     * @var Connection
     */
    protected $dbalConnection;

    /**
     * @param Connection $connection
     * @param array      $config
     *
     * @return self
     */
    public static function createFromDbalConnectionAndConfig(Connection $connection, $config)
    {
        $adapter = new self($config);
        $adapter->dbalConnection = $connection;

        unset($adapter->_config['username'], $adapter->_config['password']);

        return $adapter;
    }

    /**
     * Special handling for PDO query().
     * All bind parameter names must begin with ':'
     *
     * @param string|Zend_Db_Select $sql  the SQL statement with placeholders
     * @param array                 $bind an array of data to bind to the placeholders
     *
     * @throws Zend_Db_Adapter_Exception to re-throw PDOException
     *
     * @return Zend_Db_Statement_Pdo|false
     */
    public function query($sql, $bind = [])
    {
        if (empty($bind) && $sql instanceof Zend_Db_Select) {
            $bind = $sql->getBind();
        }

        if (!is_array($bind)) {
            $bind = [$bind];
        }

        foreach ($bind as $name => $value) {
            if ($value instanceof Zend_Date) {
                $bind[$name] = $value->toString('yyyy-MM-dd HH:mm:ss');
            }
        }

        return parent::query($sql, $bind);
    }

    /**
     * Executes an SQL INSERT/UPDATE/DELETE query with the given parameters
     * and returns the number of affected rows.
     *
     * @param string $query  the SQL query
     * @param array  $params the query parameters
     * @param array  $types  the parameter types, ignored for now
     *
     * @return int the number of affected rows
     */
    public function executeUpdate($query, array $params = [], array $types = [])
    {
        $stmt = $this->query($query, $params);

        return $stmt->rowCount();
    }

    /**
     * Alias for query.
     *
     * @param string $query
     * @param array  $params
     * @param array  $types
     *
     * @return Zend_Db_Statement_Pdo
     */
    public function executeQuery($query, array $params = [], array $types = [])
    {
        return $this->query($query, $params);
    }

    /**
     * Returns the error message of the last query, or null if none
     *
     * @return string|null
     */
    public function getErrorMessage()
    {
        $error = $this->getConnection()->errorInfo();

        return isset($error[2]) ? $error[2] : null;
    }

    /**
     * Quote a raw string.
     *
     * @param string $value
     *
     * @return string
     */
    protected function _quote($value)
    {
        if ($value instanceof Zend_Date) {
            $value = $value->toString('yyyy-MM-dd HH:mm:ss');
        }

        return parent::_quote($value);
    }

    /**
     * Creates a PDO object and connects to the database.
     *
     * @throws Zend_Db_Adapter_Exception
     */
    protected function _connect()
    {
        // if we already have a PDO object, no need to re-connect.
        if ($this->_connection) {
            return;
        }

        if (!$this->dbalConnection) {
            throw new RuntimeException(sprintf('Class can only be constructed using %s::createFromDbalConnectionAndConfig().', __CLASS__));
        }

        $this->_connection = $this->dbalConnection->getWrappedConnection();
    }
}
