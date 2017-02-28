<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

use Monolog\Handler\BufferHandler;
use Shopware\Components\Log\Handler\EnlightMailHandler;
use Shopware\Components\Log\Processor\ShopwareEnvironmentProcessor;
use Shopware\Components\Log\Formatter\HtmlFormatter;

/**
 * Shopware Error Handler
 *
 * @category  Shopware
 * @package   Shopware\Plugins\Core\ErrorHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Core_ErrorHandler_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var callback
     */
    protected static $_origErrorHandler = null;

    /**
     * @var boolean
     */
    protected static $_registeredErrorHandler = false;

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

    /**
     * @var bool
     */
    private $throwOnRecoverableError = false;

    /**
     * @var array
     */
    protected $_errorLevelList = array(
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
        E_STRICT            => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED        => 'E_DEPRECATED',
        E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        E_ALL               => 'E_ALL',
    );

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

    /**
     * Plugin install method
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
     * Plugin event method
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onStartDispatch($args)
    {
        $this->throwOnRecoverableError = Shopware()->Container()->getParameter('shopware.errorHandler.throwOnRecoverableError');

        // Register ErrorHanlder for all errors, including strict
        $this->registerErrorHandler(E_ALL | E_STRICT);

        if ($this->Config()->get('logMail')) {
            $this->get('corelogger')->pushHandler($this->createMailHandler());
        }

        $this->get('events')->addListener(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            array($this, 'onDispatchLoopShutdown')
        );
    }

    /**
     * Register error handler callback
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     * @param  int $errorLevel
     * @return Shopware_Plugins_Core_ErrorHandler_Bootstrap
     */
    public function registerErrorHandler($errorLevel = E_ALL)
    {
        // Only register once.  Avoids loop issues if it gets registered twice.
        if (self::$_registeredErrorHandler) {
            set_error_handler(array($this, 'errorHandler'), $errorLevel);
            return $this;
        }

        self::$_origErrorHandler = set_error_handler(array($this, 'errorHandler'), $errorLevel);
        self::$_registeredErrorHandler = true;

        return $this;
    }

    /**
     * Error Handler will convert error into log message, and then call the original error handler
     *
     * @link http://www.php.net/manual/en/function.set-error-handler.php Custom error handler
     * @param  int            $errno
     * @param  string         $errstr
     * @param  string         $errfile
     * @param  int            $errline
     * @param  array          $errcontext
     * @throws ErrorException
     * @return boolean
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        // Ignore suppressed errors/warnings
        if (error_reporting() === 0) {
            return;
        }

        // Ignore access to not initialized variables in smarty templates
        if ($errno === E_NOTICE && stripos($errfile, '/var/cache/') !== false && stripos($errfile, '/templates/') !== false) {
            return;
        }

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
            case E_DEPRECATED:
            case E_NOTICE:
            case E_WARNING:
            case E_STRICT:
            case E_USER_NOTICE:
            case E_USER_DEPRECATED:
            case E_CORE_WARNING:
            case E_USER_WARNING:
            case E_ERROR:
            case E_USER_ERROR:
            case E_CORE_ERROR:
                break;
            case E_RECOVERABLE_ERROR:
                if ($this->throwOnRecoverableError) {
                    throw new ErrorException($this->_errorLevelList[$errno].': '.$errstr, 0, $errno, $errfile, $errline);
                }
                break;
            default:
                throw new ErrorException($this->_errorLevelList[$errno].': '.$errstr, 0, $errno, $errfile, $errline);
                break;
        }

        if (self::$_origErrorHandler !== null) {
            return call_user_func(self::$_origErrorHandler, $errno, $errstr, $errfile, $errline, $errcontext);
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
     * @param  bool $value
     * @return Shopware_Plugins_Core_ErrorHandler_Bootstrap
     */
    public function setEnabledLog($value = true)
    {
        $this->_errorLog = $value ? true : false;

        return $this;
    }


    /**
     * @param Enlight_Controller_EventArgs $args
     */
    public function onDispatchLoopShutdown(Enlight_Controller_EventArgs $args)
    {
        $response = $args->getSubject()->Response();
        $exceptions = $response->getException();
        if (empty($exceptions)) {
            return;
        }

        $logger = $this->get('corelogger');
        foreach ($exceptions as $exception) {
            $logger->error((string) $exception);
        }
    }

    /**
     * @return BufferHandler
     */
    public function createMailHandler()
    {
        $mail = Shopware()->Config()->get('logMailAddress');

        if (empty($mail)) {
            $mail = Shopware()->Config()->Mail;
        }

        $mailer = new \Enlight_Components_Mail();
        $mailer->addTo($mail);
        $mailer->setSubject('Error in shop "'.Shopware()->Config()->Shopname.'".');
        $mailHandler = new EnlightMailHandler($mailer, \Monolog\Logger::WARNING);
        $mailHandler->pushProcessor(new ShopwareEnvironmentProcessor());
        $mailHandler->setFormatter(new HtmlFormatter());
        $bufferedHandler = new BufferHandler($mailHandler);

        return $bufferedHandler;
    }
}
