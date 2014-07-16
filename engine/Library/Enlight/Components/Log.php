<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_Log
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license/new-bsd     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */
use Psr\Log\LoggerInterface;

/**
 * Basic Enlight log component.
 *
 * The Enlight_Components_Log is a component to log data and to output these via appropriate log writer.
 *
 * @category   Enlight
 * @package    Enlight_Log
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license/new-bsd     New BSD License
 *
 * @method  mixed table()
 * @method  mixed exception()
 * @method  mixed dump()
 * @method  mixed trace()
 * @method  mixed err()
 *
 * @deprecated 4.2
 */
class Enlight_Components_Log extends Zend_Log
{
    const TABLE = 8;
    const EXCEPTION = 9;
    const DUMP = 10;
    const TRACE = 11;

    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $config
     * @return Enlight_Components_Log
     */
    public static function factory($config = array())
    {
        return new self();
    }

    /**
     *
     */
    public function __destruct()
    {
    }

    /**
     * @param string $method
     * @param string $params
     */
    public function __call($method, $params)
    {
        $method = strtolower($method);
        switch ($method) {
            case 'debug':
            case 'info':
            case 'notice':
            case 'warn':
            case 'err':
            case 'crit':
            case 'alert':
            case 'emerg':
            case 'trace':
            case 'dump':
            case 'exception':
            case 'table':
                if (isset($params[0])) {
                    $this->deprecatedMessage($params[0]);
                }
                break;
            default:
                return;
        }
    }

    /**
     * @param $message
     */
    private function deprecatedMessage($message)
    {
        if ($this->logger) {
            $this->logger->info("Deprecated Shopware()->Log() call. Message: " . $message);
        }
    }

    /**
     * @param string $message
     * @param int $priority
     * @param null $extras
     */
    public function log($message, $priority, $extras = null)
    {
        $this->deprecatedMessage($message);
    }

    /**
     * @param string $name
     * @param int $priority
     * @return $this
     */
    public function addPriority($name, $priority)
    {
        return $this;
    }

    /**
     * @param array|int|Zend_Config|Zend_Log_Filter_Interface $filter
     * @return $this
     */
    public function addFilter($filter)
    {
        return $this;
    }

    /**
     * @param mixed $writer
     * @return $this
     */
    public function addWriter($writer)
    {
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setEventItem($name, $value)
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function registerErrorHandler()
    {
        return $this;
    }

    /**
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     * @return bool
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {
        return false;
    }

    /**
     * @param string $format
     * @return $this
     */
    public function setTimestampFormat($format)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getTimestampFormat()
    {
        return 'c';
    }
}
