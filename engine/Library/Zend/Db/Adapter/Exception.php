<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Db_Exception
 */

/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Adapter_Exception extends Zend_Db_Exception
{
    protected $_chainedException = null;

    public function __construct($message = '', $code = 0, Exception $e = null)
    {
        if ($e && (0 === $code)) {
            $code = $e->getCode();
        }

        /**
         * As $e might be an instance of \PDOException $e::getCode() could be
         * a string or it could be passed as string
         */
        if (!is_int($code)) {
            $code = 0;
        }

        parent::__construct($message, $code, $e);
    }

    public function hasChainedException()
    {
        return ($this->_previous !== null);
    }

    public function getChainedException()
    {
        return $this->getPrevious();
    }
}
