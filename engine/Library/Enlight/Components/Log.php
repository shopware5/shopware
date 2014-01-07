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
 */
class Enlight_Components_Log extends Zend_Log
{
    const TABLE = 8;
    const EXCEPTION = 9;
    const DUMP = 10;
    const TRACE = 11;

    /**
     * Factory to construct the logger and one or more writers
     * based on the configuration array
     *
     * @throws  Zend_Log_Exception
     * @param   Enlight_Config|array $config
     * @return  Enlight_Components_Log
     */
    public static function factory($config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        if (!is_array($config) || empty($config)) {
            throw new Enlight_Exception('Configuration must be an array or instance of Enlight_Config');
        }

        $log = new self;

        if (array_key_exists('timestampFormat', $config)) {
            if (null != $config['timestampFormat'] && '' != $config['timestampFormat']) {
                $log->setTimestampFormat($config['timestampFormat']);
            }
            unset($config['timestampFormat']);
        }

        if (!is_array(current($config))) {
            $log->addWriter(current($config));
        } else {
            foreach ($config as $writer) {
                $log->addWriter($writer);
            }
        }

        return $log;
    }

    /**
     * Add a writer.  A writer is responsible for taking a log
     * message and writing it out to storage.
     *
     * @param  mixed $writer Zend_Log_Writer_Abstract or Config array
     * @return Zend_Log
     */
    public function addWriter($writer)
    {
        if (is_array($writer) || $writer instanceof  Zend_Config) {
            $writer = $this->_constructWriterFromConfig($writer);
        }

        if ($writer instanceof Zend_Log_Writer_Firebug) {
            /** @var $writer Zend_Log_Writer_Firebug */
            $writer->setPriorityStyle(self::TABLE, 'TABLE');
            $writer->setPriorityStyle(self::EXCEPTION, 'EXCEPTION');
            $writer->setPriorityStyle(self::DUMP, 'DUMP');
            $writer->setPriorityStyle(self::TRACE, 'TRACE');
        }

        return parent::addWriter($writer);
    }
}
