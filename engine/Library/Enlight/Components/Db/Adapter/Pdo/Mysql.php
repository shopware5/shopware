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
 * @package    Enlight_Db
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     $Author$
 */

/**
 * Pdo adapter for Mysql.
 *
 * The Enlight_Components_Db_Adapter_Pdo_Mysql extends the zend pdo mysql adapter to format dates automatically
 * and removes for safety reasons, the database connection settings.
 *
 * @category   Enlight
 * @package    Enlight_Db
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql
{
    /**
     * Quote a raw string.
     *
     * @param string $value
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
     * Special handling for PDO query().
     * All bind parameter names must begin with ':'
     *
     * @param string|Zend_Db_Select $sql The SQL statement with placeholders.
     * @param array $bind An array of data to bind to the placeholders.
     * @return Zend_Db_Statement_Pdo
     * @throws Zend_Db_Adapter_Exception To re-throw PDOException.
     */
    public function query($sql, $bind = array())
    {
        if (empty($bind) && $sql instanceof Zend_Db_Select) {
            $bind = $sql->getBind();
        }

        if (!is_array($bind)) {
            $bind = array($bind);
        }

        foreach ($bind as $name => $value) {
            if ($value instanceof Zend_Date) {
                $bind[$name] = $value->toString('yyyy-MM-dd HH:mm:ss');
            }
        }

        return parent::query($sql, $bind);
    }

    /**
     * Creates a PDO object and connects to the database.
     *
     * @return void
     * @throws Zend_Db_Adapter_Exception
     */
    protected function _connect()
    {
        // if we already have a PDO object, no need to re-connect.
        if ($this->_connection) {
            return;
        }

        try {
            parent::_connect();
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message = str_replace(array(
                    $this->_config['username'],
                    $this->_config['password']
                ), '******', $message
            );

            throw new Zend_Db_Adapter_Exception($message, $e->getCode());
        }

        // finally, we delete the authorization data
        unset($this->_config['username'], $this->_config['password']);
    }

    /**
     * Executes an SQL INSERT/UPDATE/DELETE query with the given parameters
     * and returns the number of affected rows.
     *
     * @param string $query  The SQL query.
     * @param array  $params The query parameters.
     * @param array  $types  The parameter types, ignored for now.
     *
     * @return integer The number of affected rows.
     */
    public function executeUpdate($query, array $params = array(), array $types = array())
    {
        $stmt = $this->query($query, $params);

        return $stmt->rowCount();
    }

    /**
     * Alias for query.
     * @param $query
     * @param array $params
     * @param array $types
     * @return Zend_Db_Statement_Pdo
     */
    public function executeQuery($query, array $params = array(), array $types = array())
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
}
