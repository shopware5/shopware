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
 * Adodb statement adapter component.
 *
 * The Enlight_Components_Adodb_Statement class is the interface for adodb statements,
 * which grants an easy access to sql statement results.
 *
 * @category   Enlight
 * @package    Enlight_Adodb
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Adodb_Statement extends Enlight_Class
{
    /**
     * Internal sql statement object
     * @var object
     */
    protected $_statement;

    /**
     * Field array with all fields of the sql statement
     * @var array
     */
    public $fields = array();

    /**
     * Flag whether the statement is reached at the end.
     * @var bool
     */
    public $EOF = true;

    /**
     * If the class created the cursor will set to the first row over the MoveNext() function.
     *
     * @param $statement sql statement object. Must support the rowCount(), fetch(), closeCursor(), columnCount(),
     */
    public function __construct($statement)
    {
        $this->_statement = $statement;
        $this->MoveNext();
    }

    /**
     * Returns the row count of the executed sql statement.
     *
     * @return mixed
     */
    public function RecordCount()
    {
        return $this->_statement->rowCount();
    }

    /**
     * This function moves the cursor to the next row
     */
    public function MoveNext()
    {
        if ($this->_statement->columnCount()) {
            $this->fields = $this->_statement->fetch();
            $this->EOF = $this->fields === false;
        }
    }

    /**
     * Fetch the first row of the sql statement with Zend_Db::FETCH_ASSOC.
     *
     * @return array
     */
    public function FetchRow()
    {
        if ($this->fields) {
            $result = $this->fields;
            $this->fields = array();
            return $result;
        }
        return $this->_statement->fetch(Zend_Db::FETCH_ASSOC);
    }

    /**
     * Closes the sql statement cursor.
     *
     * @return boolean success
     */
    public function Close()
    {
        return $this->_statement->closeCursor();
    }
}
