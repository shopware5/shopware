<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Plugins
 * @subpackage ErrorHandler
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 * @author     Heiner Lohaus
 */

/**
 * Shopware Error Handler
 *
 * todo@all: Documentation
 */
class Shopware_Plugins_Core_ErrorHandler_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Plugin install method
     */
    public function install()
    {
        $event = $this->createEvent(
            'Enlight_Controller_Front_StartDispatch',
            'onStartDispatch'
        );
        $this->subscribeEvent($event);
        return true;
    }

    /**
     * Plugin event method
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onStartDispatch($args)
    {
        $this->registerErrorHandler(E_ALL | E_STRICT);
    }

    /**
     * @var callback
     */
    protected $_origErrorHandler = null;

    /**
     * @var boolean
     */
    protected $_registeredErrorHandler = false;

    /**
     * @var array
     */
    protected $_errorHandlerMap = null;

    /**
     * @var array
     */
    protected $_errorLevel = 0;

    /**
     * @var array
     */
    protected $_errorLog = false;

    /**
     * @var array
     */
    protected $_errorList = array();

    protected $_errorLevelList = array(
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_ALL => 'E_ALL',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        8192 => 'E_DEPRECATED',
        16384 => 'E_USER_DEPRECATED',
    );

    /**
     * Register error handler callback
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     * @param int $errorLevel
     */
    public function registerErrorHandler($errorLevel = E_ALL)
    {
        // Only register once.  Avoids loop issues if it gets registered twice.
        if ($this->_registeredErrorHandler) {
            return $this;
        }

        $this->_origErrorHandler = set_error_handler(array($this, 'errorHandler'), $errorLevel);

        $this->_registeredErrorHandler = true;
        return $this;
    }

    /**
     * Error Handler will convert error into log message, and then call the original error handler
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     * @return boolean
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if ($this->_errorLog) {
            $hash_id = md5($errno . $errstr . $errfile . $errline);
            if (!isset($this->_errorList[$hash_id])) {
                $errna = isset($this->_errorLevelList[$errno]) ? $this->_errorLevelList[$errno] : '';
                $this->_errorList[$hash_id] = array(
                    'count' => 1,
                    'code' => $errno,
                    'name' => $errna,
                    'message' => $errstr,
                    'line' => $errline,
                    'file' => $errfile
                );
            } else {
                ++$this->_errorList[$hash_id]['count'];
            }
        }

        switch ($errno) {
            case 0:
            case E_NOTICE:
            case E_WARNING:
            case E_USER_NOTICE:
            case E_RECOVERABLE_ERROR:
            case E_STRICT:
            case defined('E_DEPRECATED') ? E_DEPRECATED : 0:
            case defined('E_USER_DEPRECATED') ? E_USER_DEPRECATED : 0:
                break;
            case E_CORE_WARNING:
            case E_USER_WARNING:
            case E_ERROR:
            case E_USER_ERROR:
            case E_CORE_ERROR:
                break;
            default:
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
                break;
        }

        if ($this->_origErrorHandler !== null) {
            return call_user_func($this->_origErrorHandler, $errno, $errstr, $errfile, $errline, $errcontext);
        }
        return true;
    }

    /**
     * Returns error log list
     *
     * @return array
     */
    public function getErrorLog()
    {
        return $this->_errorList;
    }

    /**
     * Sets enabled log flag
     *
     * @param bool $value
     */
    public function setEnabledLog($value = true)
    {
        $this->_errorLog = $value ? true : false;
        return $this;
    }

    /**
     * Returns plugin capabilities
     */
    public function getCapabilities()
    {
        return array(
            'install' => false,
            'enable' => false,
            'update' => true
        );
    }
}