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
 * @package    Enlight_Config
 * @copyright  Copyright (c) 2013, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Tiago Garcia
 */

/**
 * Query adapter for the Enlight config classes.
 *
 * The Enlight_Config_Adapter is an adapter to write config information into query format.
 * Tested only for snippet information, only supports writing
 *
 * @category   Enlight
 * @package    Enlight_Config
 * @copyright  Copyright (c) 2013, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Config_Writer_Query extends Enlight_Config_Writer_Writer
{
    /**
     * The table instance.
     *
     * @var null|string|Enlight_Components_Table
     */
    protected $_table;

    /**
     * The namespace column in the database table.
     *
     * @var null|string
     */
    protected $_namespaceColumn;

    /**
     * The name column in the database table.
     *
     * @var null|string
     */
    protected $_nameColumn = 'name';

    /**
     * The value column in the database table.
     *
     * @var null|string
     */
    protected $_valueColumn = 'value';

    /**
     * The section column in the database table.
     *
     * @var null|string
     */
    protected $_sectionColumn = 'section';

    /**
     * The dirty column in the database table.
     *
     * @var null|string
     */
    protected $_dirtyColumn = 'dirty';

    /**
     * The created column in the database table.
     *
     * @var null|string
     */
    protected $_createdColumn = 'created';

    /**
     * The created column in the database table.
     *
     * @var null|string
     */
    protected $_updatedColumn = 'updated';

    /**
     * @var string
     */
    protected $queries = array();

    public function setOptions(array $options)
    {
        foreach ($options as $key => $option) {
            switch ($key) {
                case 'nameColumn':
                case 'namespaceColumn':
                case 'valueColumn':
                case 'createdColumn':
                case 'updatedColumn':
                    $this->{'_' . $key} = (string) $option;
                    break;
                case 'automaticSerialization':
                    $this->{'_' . $key} = (bool) $option;
                    break;
                case 'sectionColumn':
                case 'table':
                    $this->{'_' . $key} = $option;
                    break;
                case 'db':
                    $this->{'_' . $key} = $option;
                    break;
                default:
                    break;
            }
        }
        return parent::setOptions($options);
    }

    /**
     * @inheritdoc
     */
    public function write(Enlight_Config $config = null, $fields = null, $update = true)
    {
        if ($config !== null) {
            $this->setConfig($config);
        }

        if ($update) {
            $sqlSkeleton = 'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s;';
        } else {
            $sqlSkeleton = 'INSERT IGNORE INTO %s (%s) VALUES (%s);';
        }

        $name = $config->getName();
        $section = explode($config->getSectionSeparator(), $config->getSection());

        if ($fields === null) {
            $fields = $config->getDirtyFields();
        }
        if (empty($fields) || empty($this->_table) || empty($this->_dirtyColumn)) {
            return $this;
        }

        $columns = array();
        $values = array();
        $update = array();

        if ($this->_namespaceColumn !== null) {
            $columns[] = $this->_namespaceColumn;
            $values[] = '\''.$name.'\'';
        }

        if ($this->_updatedColumn !== null) {
            $columns[] = $this->_updatedColumn;
            $values[] = '\''.date('Y-m-d H:i:s', time()).'\'';
            $update[] = $this->_updatedColumn.'=IF('.$this->_dirtyColumn.' = 1, '.$this->_updatedColumn.', \''.date('Y-m-d H:i:s', time()).'\')';
        }
        if ($this->_createdColumn !== null) {
            $columns[] = $this->_createdColumn;
            $values[] = '\''.date('Y-m-d H:i:s', time()).'\'';
        }

        if ($section !== null) {
            if (is_array($this->_sectionColumn)) {
                foreach ($this->_sectionColumn as $key => $sectionColumn) {
                    if (isset($section[$key])) {
                        $columns[] = $sectionColumn;
                        $values[] = $section[$key];
                    }
                }
            } else {
                $columns[] = $this->_sectionColumn;
                $values[] = $section;
            }
        }

        foreach ((array) $fields as $field) {
            $columnsData = $columns;
            $valuesData = $values;
            $updateData = $update;

            $columnsData[] = $this->_nameColumn;
            $valuesData[] = '\''.addslashes($field).'\'';

            $columnsData[] = $this->_valueColumn;
            $valuesData[] = '\''.addslashes($config->get($field)).'\'';
            $updateData[] = $this->_valueColumn.'=IF('.$this->_dirtyColumn.' = 1, '.$this->_valueColumn.', \''.addslashes($config->get($field)).'\')';
            $updateData[] = $this->_dirtyColumn.'=IF('.$this->_valueColumn.' = '.addslashes($config->get($field)).', 0, 1)';

            if ($update) {
                $query = sprintf($sqlSkeleton, $this->_table, implode(', ', $columnsData), implode(', ', $valuesData), implode(', ', $updateData));
            } else {
                $query = sprintf($sqlSkeleton, $this->_table, implode(', ', $columnsData), implode(', ', $valuesData));
            }

            $this->queries[] = $query;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getQueries()
    {
        return $this->queries;
    }


}
