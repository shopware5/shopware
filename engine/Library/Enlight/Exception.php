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
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * Basic Enlight exception class.
 *
 * The Enlight_Exception is the basic class for each specified exception class. (Controller_Exception, ...)
 * Extends the default exception class with an previous Exception property
 *
 * @category   Enlight
 * @package    Enlight_Exception
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Exception extends Exception
{
    /**
     * Constant that a class could not be found
     */
    const CLASS_NOT_FOUND = 1000;

    /**
     * Constant that a method could not be found
     */
    const METHOD_NOT_FOUND = 1100;

    /**
     * Constant that a class property could not be found
     */
    const PROPERTY_NOT_FOUND = 1200;

    /**
     * The class constructor sets the given previous exception into the internal property.
     * If the given code is one of the internal constants, it will generate a back trace and iterate
     * the returned values to set the line and file property.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if (in_array($code, array(self::CLASS_NOT_FOUND, self::METHOD_NOT_FOUND, self::PROPERTY_NOT_FOUND))) {
            $trace = debug_backtrace(false);
            foreach ($trace as $i => $var) {
                if (!$i || $var['function'] == '__call' || !isset($var['line'])) {
                    unset($trace[$i]);
                    continue;
                }
                $this->file = $var['file'];
                $this->line = $var['line'];
                break;
            }
        }
    }
}
