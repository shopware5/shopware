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
 * @package    Enlight_Adodb
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     $Author$
 */

/**
 * Adapter for adodb connections.
 *
 * The Enlight_Components_Adodb is an interface for the zend db adapter
 * to have an easy way to get adodb sql syntax for an sql expression.
 *
 * @category   Enlight
 * @package    Enlight_Adodb
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Adodb extends Enlight_Class
{
    /**
     * @var Zend_Db_Adapter_Abstract Performs all operations on the database
     */
    protected $db;

    /**
     * @var Zend_Cache_Core Used for the statement caching.
     */
    protected $cache;

    /**
     * @var array
     */
    protected $cacheTags = array();

    /**
     * @var string
     */
    protected $cacheIdPrefix = 'Adodb_';

    /**
     * @var int
     */
    protected $cacheLifetime = 0;

    /**
     * @var int
     */
    protected $rowCount;

    /**
     * Public constructor
     *
     * @param  array $config
     * @return void
     */
    public function __construct($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }
        if (isset($config['db'])) {
            $this->db = $config['db'];
        }
        if (isset($config['cache'])) {
            $this->cache = $config['cache'];
        }
        if (isset($config['cacheTags'])) {
            $this->cacheTags = $config['cacheTags'];
        }
        if (isset($config['cacheIdPrefix'])) {
            $this->cacheIdPrefix = (string)$config['cacheIdPrefix'];
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
     * @param array  $bind
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
     * @param array  $bind
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
     * @param array  $bind
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
     * @param array  $bind
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
     * @param array  $bind
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
     * @param array  $bind
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
     * @param int    $count
     * @param int    $offset
     * @param array  $bind
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
     * @param int    $timeout
     * @param string $sql
     * @param array  $bind
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
     * @return int all founded rows
     */
    public function CacheGetFoundRows()
    {
        if ($this->foundRows === null) {
            $this->foundRows = $this->GetFoundRows();
        }
        return $this->foundRows;
    }

    /**
     * @var null Contains the founded rows.
     */
    protected $foundRows = null;

    /**
     * Returns statement cache id
     *
     * @param string $name
     * @param string $sql
     * @param array  $bind
     * @return string
     */
    public function getCacheId($name, $sql, $bind = array())
    {
        return $this->cacheIdPrefix . md5($name . $sql . serialize($bind));
    }

    /**
     * Call statement cached
     *
     * @param   string $name
     * @param   int    $timeout
     * @param   string $sql
     * @param   array  $bind
     * @param   array  $tags
     * @return  mixed
     */
    protected function callCached($name, $timeout, $sql = null, $bind = array(), $tags = array())
    {
        if ($sql === null) {
            $sql = $timeout;
            $timeout = null;
        }
        $timeout = $timeout === null ? $this->cacheLifetime : (int)$timeout;
        $tags = array_merge((array)$tags, $this->cacheTags);
        $bind = (array)$bind;

        $this->foundRows = null;

        if ($timeout > 0 && $this->cache !== null) {

            $id = $this->getCacheId($name, $sql, $bind);
            $calcFoundRows = strpos($sql, 'SQL_CALC_FOUND_ROWS') !== false;

            if (!$this->cache->test($id)) {
                $result = $this->$name($sql, $bind);
                if ($calcFoundRows) {
                    $result = array(
                        'rows' => $result,
                        'foundRows' => $this->GetFoundRows()
                    );
                }
                $this->cache->save($result, $id, $tags, $timeout);
            } else {
                $result = $this->cache->load($id);
            }

            if ($calcFoundRows && isset($result['rows'])) {
                $this->foundRows = $result['foundRows'];
                $result = $result['rows'];
            }
        } else {
            $result = $this->$name($sql, $bind);
        }

        return $result;
    }

    /**
     * Fetch all rows cached
     *
     * @param   int     $timeout
     * @param   string  $sql
     * @param   array   $bind
     * @param   array   $tags
     * @return  mixed
     */
    public function CacheGetAll($timeout, $sql = null, $bind = array(), $tags = array())
    {
        return $this->callCached('GetAll', $timeout, $sql, $bind, $tags);
    }

    /**
     * Fetch first row cached
     *
     * @param  int    $timeout
     * @param  string $sql
     * @param  array  $bind
     * @param  array  $tags
     * @return array
     */
    public function CacheGetRow($timeout, $sql = null, $bind = array(), $tags = array())
    {
        return $this->callCached('GetRow', $timeout, $sql, $bind, $tags);
    }

    /**
     * Fetch first value cached
     *
     * @param int    $timeout
     * @param string $sql
     * @param array  $bind
     * @param array  $tags
     * @return string
     */
    public function CacheGetOne($timeout, $sql = null, $bind = array(), $tags = array())
    {
        return $this->callCached('GetOne', $timeout, $sql, $bind, $tags);
    }

    /**
     * Fetch first column cached
     *
     * @param int    $timeout
     * @param string $sql
     * @param array  $bind
     * @param array  $tags
     * @return array
     */
    public function CacheGetCol($timeout, $sql = null, $bind = array(), $tags = array())
    {
        return $this->callCached('GetCol', $timeout, $sql, $bind, $tags);
    }

    /**
     * Fetch all rows cached
     *
     * @param int    $timeout
     * @param string $sql
     * @param array  $bind
     * @param array  $tags
     * @return array
     */
    public function CacheGetAssoc($timeout, $sql = null, $bind = array(), $tags = array())
    {
        return $this->callCached('GetAssoc', $timeout, $sql, $bind, $tags);
    }
}