<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Adapter for adodb connections.
 *
 * The Enlight_Components_Adodb is an interface for the zend db adapter
 * to have an easy way to get adodb sql syntax for an sql expression.
 *
 * @deprecated Superseded by Enlight_Components_Db
 * @category  Shopware
 * @package   Enlight\Component\Adodb
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Enlight_Components_Adodb extends Enlight_Class
{
    /**
     * @var Zend_Db_Adapter_Abstract Performs all operations on the database
     */
    protected $db;

    /**
     * @var int
     */
    protected $rowCount;

    /**
     * @var string Standard system date expression (default: 'CURDATE()')
     */
    public $sysDate = 'CURDATE()';

    /**
     * @var string Standard system timestamp expression (default: 'NOW()')
     */
    public $sysTimeStamp = 'NOW()';

    /**
     * @var string Standard date format (default: 'Y-m-d')
     */
    protected $fmtDate = 'Y-m-d';

    /**
     * @var string Standard timestamp format (default: 'Y-m-d H:i:s')
     */
    protected $fmtTimeStamp = 'Y-m-d H:i:s';

    /**
     * Public constructor
     *
     * @param  array $config
     */
    public function __construct($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        if (isset($config['db'])) {
            $this->db = $config['db'];
        }
    }

    /**
     * Returns last insert id
     *
     * @return int
     */
    public function Insert_ID()
    {
        return $this->db->lastInsertId();
    }

    /**
     * Execute sql statement
     *
     * @param string $sql
     * @param array $bind
     * @return bool|Enlight_Components_Adodb_Statement
     */
    public function Execute($sql, $bind = array())
    {
        if (empty($bind) && strtoupper(substr(ltrim($sql), 0, 3)) != 'SEL' && empty($bind)) {
            $this->rowCount = $this->db->exec($sql);
            return $this->rowCount !== false;
        }
        $stm = $this->db->query($sql, $bind);
        $this->rowCount = $stm->rowCount();
        return new Enlight_Components_Adodb_Statement($stm);
    }

    /**
     * Quote sql value method
     *
     * @param string|int|array $value
     * @return string
     */
    public function qstr($value)
    {
        return $this->db->quote($value);
    }

    /**
     * Quote sql value method
     *
     * @param string|int|array $value
     * @return string
     */
    public function quote($value)
    {
        return $this->db->quote($value);
    }

    /**
     * Returns named placeholder
     *
     * @param string
     * @return string
     */
    public function Param($value)
    {
        return '?';
    }

    /**
     * Returns database offset date
     *
     * @param unknown_type $dayFraction
     * @param unknown_type $date
     * @return unknown
     */
    public function OffsetDate($dayFraction, $date = null)
    {
        if (empty($date)) {
            $date = $this->sysDate;
        }
        $fraction = $dayFraction * 24 * 3600;
        return '(' . $date . ' + INTERVAL ' . $fraction . ' SECOND)';
    }

    /**
     * Returns database timestamp
     *
     * @param unknown_type $timestamp
     * @return unknown
     */
    public function DBTimeStamp($timestamp)
    {
        if (empty($timestamp) && $timestamp !== 0) {
            return 'null';
        }
        $date = new Zend_Date($timestamp);
        return $this->db->quote($date->toString($this->fmtTimeStamp, 'php'));
    }

    /**
     * Returns database timestamp
     *
     * @param unknown_type $timestamp
     * @return unknown
     */
    public function DBDate($timestamp)
    {
        if (empty($timestamp) && $timestamp !== 0) {
            return 'null';
        }
        $date = new Zend_Date($timestamp);
        return $this->db->quote($date->toString($this->fmtDate, 'php'));
    }

    /**
     * Returns database timestamp
     *
     * @param unknown_type $timestamp
     * @return unknown
     */
    public function BindTimeStamp($timestamp)
    {
        if (empty($timestamp) && $timestamp !== 0) {
            return 'null';
        }
        $date = new Zend_Date($timestamp);
        return $date->toString($this->fmtTimeStamp, 'php');
    }

    /**
     * Set log sql
     *
     * @deprecated
     * @param bool $enable
     * @return bool
     */
    public function LogSQL($enable = true)
    {
        return true;
    }

    /**
     * Returns last error message
     *
     * @return unknown
     */
    public function ErrorMsg()
    {
        $error = $this->db->getConnection()->errorInfo();
        return isset($error[2]) ? $error[2] : null;
    }

    /**
     * Returns affected rows
     *
     * @return unknown
     */
    public function Affected_Rows()
    {
        return $this->rowCount;
    }

    /**
     * Fetch all rows
     *
     * @param string $sql
     * @param array $bind
     * @return array
     */
    public function GetAll($sql, $bind = array())
    {
        return $this->db->fetchAll($sql, $bind);
    }

    /**
     * Fetch first row
     *
     * @param string $sql
     * @param array $bind
     * @return array
     */
    public function GetRow($sql, $bind = array())
    {
        $result = $this->db->fetchRow($sql, $bind);
        return $result === false ? array() : $result;
    }

    /**
     * Fetch first value
     *
     * @param string $sql
     * @param array $bind
     * @return string
     */
    public function GetOne($sql, $bind = array())
    {
        return $this->db->fetchOne($sql, $bind);
    }

    /**
     * Fetch first column
     *
     * @param string $sql
     * @param array $bind
     * @return array
     */
    public function GetCol($sql, $bind = array())
    {
        return $this->db->fetchCol($sql, $bind);
    }

    /**
     * Fetch all rows named
     *
     * @param string $sql
     * @param array $bind
     * @return array
     */
    public function GetAssoc($sql, $bind = array())
    {
        $stmt = $this->db->query($sql, $bind);
        $data = array();
        if ($stmt->columnCount() == 2) {
            while ($row = $stmt->fetch(Zend_Db::FETCH_NUM)) {
                $data[$row[0]] = $row[1];
            }
        } elseif ($stmt->columnCount() > 2) {
            while ($row = $stmt->fetch()) {
                $row_id = array_shift($row);
                $data[$row_id] = $row;
            }
        }
        return $data;
    }

    /**
     * Execute sql statement with limit
     *
     * @param string $sql
     * @param int $count
     * @param int $offset
     * @param array $bind
     * @return bool|Enlight_Components_Adodb_Statement
     */
    public function SelectLimit($sql, $count, $offset = 0, $bind = array())
    {
        $sql = $this->db->limit($sql, $count, $offset);
        return $this->db->Execute($sql, $bind);
    }

    /**
     * Execute sql statement cached
     *
     * @param int $timeout
     * @param string $sql
     * @param array $bind
     * @return bool|Enlight_Components_Adodb_Statement
     */
    public function CacheExecute($timeout, $sql, $bind = array())
    {
        return $this->Execute($sql, $bind);
    }

    /**
     * Returns found rows
     *
     * @return int
     */
    public function GetFoundRows()
    {
        return $this->db->fetchOne('SELECT FOUND_ROWS() as count');
    }

    /**
     * Returns found rows cached
     *
     * @deprecated
     * @return int all founded rows
     */
    public function CacheGetFoundRows()
    {
        return $this->GetFoundRows();
    }

    /**
     * Returns statement cache id
     *
     * @deprecated
     * @param string $name
     * @param string $sql
     * @param array $bind
     * @return string
     */
    public function getCacheId($name, $sql, $bind = array())
    {
        return md5($name . $sql . serialize($bind));
    }

    /**
     * Call statement cached
     *
     * @deprecated
     * @param   string $name
     * @param   int $timeout
     * @param   string $sql
     * @param   array $bind
     * @param   array $tags
     * @return  mixed
     */
    protected function callCached($name, $timeout, $sql = null, $bind = array(), $tags = array())
    {
        if ($sql === null) {
            $sql = $timeout;
        }

        $bind = (array) $bind;

        $result = $this->$name($sql, $bind);

        return $result;
    }

    /**
     * Fetch all rows cached
     *
     * @deprecated
     * @param   int $timeout
     * @param   string $sql
     * @param   array $bind
     * @param   array $tags
     * @return  mixed
     */
    public function CacheGetAll($timeout, $sql = null, $bind = array(), $tags = array())
    {
        return $this->callCached('GetAll', $timeout, $sql, $bind, $tags);
    }

    /***
     * Fetch first row cached
     *
     * @deprecated
     * @param  int $timeout
     * @param  string $sql
     * @param  array $bind
     * @param  array $tags
     * @return array
     */
    public function CacheGetRow($timeout, $sql = null, $bind = array(), $tags = array())
    {
        return $this->callCached('GetRow', $timeout, $sql, $bind, $tags);
    }

    /**
     * Fetch first value cached
     *
     * @deprecated
     * @param int $timeout
     * @param string $sql
     * @param array $bind
     * @param array $tags
     * @return string
     */
    public function CacheGetOne($timeout, $sql = null, $bind = array(), $tags = array())
    {
        return $this->callCached('GetOne', $timeout, $sql, $bind, $tags);
    }

    /**
     * Fetch first column cached
     *
     * @deprecated
     * @param int $timeout
     * @param string $sql
     * @param array $bind
     * @param array $tags
     * @return array
     */
    public function CacheGetCol($timeout, $sql = null, $bind = array(), $tags = array())
    {
        return $this->callCached('GetCol', $timeout, $sql, $bind, $tags);
    }

    /**
     * Fetch all rows cached
     *
     * @deprecated
     * @param int $timeout
     * @param string $sql
     * @param array $bind
     * @param array $tags
     * @return array
     */
    public function CacheGetAssoc($timeout, $sql = null, $bind = array(), $tags = array())
    {
        return $this->callCached('GetAssoc', $timeout, $sql, $bind, $tags);
    }
}
