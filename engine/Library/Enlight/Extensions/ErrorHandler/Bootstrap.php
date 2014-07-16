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
 * @package    Enlight_Extensions
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Enlight error handler extension to log the exception into an array.
 *
 * The Enlight_Extensions_ErrorHandler_Bootstrap logs the exceptions into an array for further processing.
 * It uses the setErrorHandler function. If the same exception multiple thrown a count property will increased.
 *
 * @category   Enlight
 * @package    Enlight_Extensions
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Extensions_ErrorHandler_Bootstrap extends Enlight_Plugin_Bootstrap_Config
{
    /**
     * @var callback Contains the original error handler
     * which will be set in the registerErrorHandler method.
     *
     */
    protected $origErrorHandler = null;

    /**
     * @var boolean Flag whether the error handler is already registered. Will be set in the
     * registerErrorHandler function.
     */
    protected $registeredErrorHandler = false;

    /**
     * @var array Contains all mapped error handlers
     */
    protected $errorHandlerMap = null;

    /**
     * @var int Contains the current error level. Used to set the error handler.
     */
    protected $errorLevel = null;

    /**
     * @var array Flag whether errors should be logged
     */
    protected $errorLog = false;

    /**
     * @var array Contains all logged errors.
     */
    protected $errorList = array();

    /**
     * @var array List of all error levels.
     */
    protected $errorLevelList = array(
        E_ERROR             => 'E_ERROR',
        E_WARNING           => 'E_WARNING',
        E_PARSE             => 'E_PARSE',
        E_NOTICE            => 'E_NOTICE',
        E_CORE_ERROR        => 'E_CORE_ERROR',
        E_CORE_WARNING      => 'E_CORE_WARNING',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        E_USER_ERROR        => 'E_USER_ERROR',
        E_USER_WARNING      => 'E_USER_WARNING',
        E_USER_NOTICE       => 'E_USER_NOTICE',
        E_ALL               => 'E_ALL',
        E_STRICT            => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        //E_DEPRECATED        => 'E_DEPRECATED',
        //E_USER_DEPRECATED    => 'E_USER_DEPRECATED',
    );

    /**
     * Initial the error level list.
     * @return void
     */
    public function init()
    {
        if (defined('E_DEPRECATED')) {
            $this->errorLevelList[E_DEPRECATED] = 'E_DEPRECATED';
        }
        if (defined('E_USER_DEPRECATED')) {
            $this->errorLevelList[E_USER_DEPRECATED] = 'E_USER_DEPRECATED';
        }
    }

    /**
     * Plugin install method. Subscribes the Enlight_Controller_Front_StartDispatch
     * event to register the error handler.
     *
     * @return bool success
     */
    public function install()
    {
         $this->subscribeEvent(
            'Enlight_Controller_Front_StartDispatch',
            'onStartDispatch'
        );
        return true;
    }

    /**
     * Listener method of the Enlight_Controller_Front_StartDispatch event.
     * Calls the internal function "registerErrorHandler" to register the error handler.
     */
    public function onStartDispatch()
    {
        $this->registerErrorHandler();
    }

    /**
     * Register error handler callback method.
     *
     * @link    http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     * @param   int $errorLevel
     * @return  Enlight_Extensions_ErrorHandler_Bootstrap
     */
    public function registerErrorHandler($errorLevel = null)
    {
        if ($errorLevel === null) {
            $errorLevel = E_ALL | E_STRICT;
        }

        // Only register once.  Avoids loop issues if it gets registered twice.
        if ($this->registeredErrorHandler
          && $errorLevel === $this->errorLevel) {
            return $this;
        }

        if (isset($this->errorLevel) && isset($this->origErrorHandler)) {
            set_error_handler(array($this, 'errorHandler'), $errorLevel);
        } else {
            $this->origErrorHandler = set_error_handler(array($this, 'errorHandler'), $errorLevel);
        }

        $this->errorLevel = $errorLevel;
        $this->registeredErrorHandler = true;

        return $this;
    }

    /**
     * Error Handler will convert error into log message, and then call the original error handler
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     * @param   int    $errorLevel
     * @param   string $errorMessage
     * @param   string $errorFile
     * @param   int    $errorLine
     * @param   array  $errorContext
     * @return  bool
     */
    public function errorHandler($errorLevel, $errorMessage, $errorFile, $errorLine, $errorContext)
    {
        if ($this->errorLog) {
            $hashId = md5($errorLevel . $errorMessage . $errorFile . $errorLine);
            if (!isset($this->errorList[$hashId])) {
                $errorName = isset($this->errorLevelList[$errorLevel]) ? $this->errorLevelList[$errorLevel] : '';
                $this->errorList[$hashId] = array(
                    'count'   => 1,
                    'code'    => $errorLevel,
                    'name'    => $errorName,
                    'message' => $errorMessage,
                    'line'    => $errorLine,
                    'file'    => $errorFile
                );
            } else {
                ++$this->errorList[$hashId]['count'];
            }
        }

        //throw new ErrorException($errorMessage, 0, $errorLevel, $errorFile, $errorLine);

        if ($this->origErrorHandler !== null) {
            return call_user_func(
                $this->origErrorHandler,
                $errorLevel,
                $errorMessage,
                $errorFile,
                $errorLine,
                $errorContext
            );
        }
        return true;
    }

    /**
     * Getter method for the errorList property. Contains all logged errors.
     *
     * @return  array
     */
    public function getErrorLog()
    {
        return $this->errorList;
    }

    /**
     * Setter method for the errorLog property. The errorLog is a flag whether errors should be logged.
     *
     * @param   bool $value
     * @return  Shopware_Plugins_Core_ErrorHandler_Bootstrap
     */
    public function setEnabledLog($value = true)
    {
        $this->errorLog = $value ? true : false;
        return $this;
    }

    /**
     * Getter method for the errorLog property.
     *
     * @return array|bool
     */
    public function isEnabledLog()
    {
        return $this->errorLog;
    }

    /**
     * Returns the original error handler. The original error handler will
     * be set in the registerErrorHandler method
     *
     * @return callback|null
     */
    public function getOrigErrorHandler()
    {
        return $this->origErrorHandler;
    }

    /**
     * Getter method for the registeredErrorHandler property.
     * Flag whether the error handler is already registered.
     *
     * @return bool
     */
    public function isRegisteredErrorHandler()
    {
        return $this->registeredErrorHandler;
    }

    /**
     * Getter method for the errorLevelList property. Contains all error levels.
     * @return array
     */
    public function getErrorLevelList()
    {
        return $this->errorLevelList;
    }

    /**
     * Returns the current registered error level.
     * @return array|int
     */
    public function getErrorLevel()
    {
        return $this->errorLevel;
    }

    /**
     * Setter method for the original error handler method.
     * @access private
     * @param $handler
     */
    public function setOrigErrorHandler($handler)
    {
        $this->origErrorHandler = $handler;
    }
}
