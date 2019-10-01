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

namespace Shopware\Components\Log\Formatter;

use Monolog\Formatter\WildfireFormatter as BaseWildfireFormatter;
use Monolog\Logger;

/**
 * Serializes a log message according to Wildfire's header requirements
 */
class WildfireFormatter extends BaseWildfireFormatter
{
    /**
     * Options for the object
     *
     * @var array
     */
    protected $options = [
        'traceOffset' => 6, /* The offset in the trace which identifies the source of the message */
        'maxTraceDepth' => 15, /* Maximum depth for stack traces */
        'maxObjectDepth' => 5, /* The maximum depth to traverse objects when encoding */
        'maxArrayDepth' => 20, /* The maximum depth to traverse nested arrays when encoding */
    ];

    /**
     * Filters used to exclude object members when encoding
     *
     * @var array
     */
    protected $objectFilters = [];

    /**
     * A stack of objects used during encoding to detect recursion
     *
     * @var array
     */
    protected $objectStack = [];

    /**
     * Translates Monolog log levels to Wildfire levels.
     */
    private $logLevels = [
        Logger::DEBUG => 'LOG',
        Logger::INFO => 'INFO',
        Logger::NOTICE => 'INFO',
        Logger::WARNING => 'WARN',
        Logger::ERROR => 'ERROR',
        Logger::CRITICAL => 'ERROR',
        Logger::ALERT => 'ERROR',
        Logger::EMERGENCY => 'ERROR',
    ];

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        // Retrieve the line and file if set and remove them from the formatted extra
        $file = $line = '';
        if (isset($record['extra']['file'])) {
            $file = $record['extra']['file'];
            unset($record['extra']['file']);
        }
        if (isset($record['extra']['line'])) {
            $line = $record['extra']['line'];
            unset($record['extra']['line']);
        }

        $record = $this->normalize($record);

        $message = ['message' => $record['message']];
        $handleError = false;
        if ($record['context']) {
            $message['context'] = $record['context'];
            $handleError = true;
        }
        if ($record['extra']) {
            $message['extra'] = $record['extra'];
            $handleError = true;
        }
        if (count($message) === 1) {
            $message = reset($message);
        }

        if (isset($record['context']['table'])) {
            $type = 'TABLE';
            $label = $record['message'];
            $message = $record['context']['table'];
        } elseif (isset($record['context']['dump'])) {
            $type = 'INFO';
            $label = $record['message'];
            $message = $this->encodeObject($record['context']['dump']);
        } elseif (isset($record['context']['trace'])) {
            $type = 'TRACE';
            $label = $record['message'];

            $trace = $this->getStackTrace($this->options);
            $message = [
                'Class' => $trace[0]['class'],
                'Type' => $trace[0]['type'],
                'Function' => $trace[0]['function'],
                'Message' => $label,
                'File' => isset($trace[0]['file']) ? $trace[0]['file'] : '',
                'Line' => isset($trace[0]['line']) ? $trace[0]['line'] : '',
                'Args' => isset($trace[0]['args']) ? $this->encodeObject($trace[0]['args']) : '',
                'Trace' => $this->encodeTrace(array_splice($trace, 1)),
            ];
        } else {
            $type = $this->logLevels[$record['level']];
            $label = $record['channel'];
        }

        // Create JSON object describing the appearance of the message in the console
        $json = $this->toJson(
            [
                [
                    'Type' => $type,
                    'File' => $file,
                    'Line' => $line,
                    'Label' => $label,
                ],
                $message,
            ],
            $handleError
        );

        return $json;
    }

    /**
     * Encodes a trace by encoding all "args" with encodeObject()
     *
     * @param array|null $trace
     *
     * @return array The encoded trace
     */
    protected function encodeTrace($trace)
    {
        if (!$trace) {
            return $trace;
        }

        for ($i = 0; $i < count($trace); ++$i) {
            if (isset($trace[$i]['args'])) {
                $trace[$i]['args'] = $this->encodeObject($trace[$i]['args']);
            }
        }

        return $trace;
    }

    /**
     * Gets a stack trace
     *
     * @param array $options Options to change how the stack trace is returned
     *
     * @return array The stack trace
     */
    protected function getStackTrace($options)
    {
        $trace = debug_backtrace();

        $trace = array_splice($trace, $options['traceOffset']);

        if (!count($trace)) {
            return $trace;
        }

        return array_splice($trace, 0, $options['maxTraceDepth']);
    }

    /**
     * Encode an object by generating an array containing all object members.
     *
     * All private and protected members are included. Some meta info about
     * the object class is added.
     *
     * @param mixed $object      The object/array/value to be encoded
     * @param int   $objectDepth
     * @param int   $arrayDepth
     *
     * @return array|string The encoded object
     */
    protected function encodeObject($object, $objectDepth = 1, $arrayDepth = 1)
    {
        $return = [];

        if (is_resource($object)) {
            return '** ' . (string) $object . ' **';
        } elseif (is_object($object)) {
            if ($objectDepth > $this->options['maxObjectDepth']) {
                return '** Max Object Depth (' . $this->options['maxObjectDepth'] . ') **';
            }

            foreach ($this->objectStack as $refVal) {
                if ($refVal === $object) {
                    return '** Recursion (' . get_class($object) . ') **';
                }
            }
            $this->objectStack[] = $object;

            $return['__className'] = $class = get_class($object);

            $reflectionClass = new \ReflectionClass($class);
            $properties = [];
            foreach ($reflectionClass->getProperties() as $property) {
                $properties[$property->getName()] = $property;
            }

            $members = (array) $object;

            foreach ($properties as $just_name => $property) {
                $name = $raw_name = $just_name;

                if ($property->isStatic()) {
                    $name = 'static:' . $name;
                }
                if ($property->isPublic()) {
                    $name = 'public:' . $name;
                } elseif ($property->isPrivate()) {
                    $name = 'private:' . $name;
                    $raw_name = "\0" . $class . "\0" . $raw_name;
                } else {
                    if ($property->isProtected()) {
                        $name = 'protected:' . $name;
                        $raw_name = "\0" . '*' . "\0" . $raw_name;
                    }
                }

                if (!(isset($this->objectFilters[$class]) && is_array($this->objectFilters[$class]) && in_array(
                                $just_name,
                                $this->objectFilters[$class]
                        ))
                ) {
                    if (array_key_exists($raw_name, $members) && !$property->isStatic()) {
                        $return[$name] = $this->encodeObject($members[$raw_name], $objectDepth + 1);
                    } else {
                        if (method_exists($property, 'setAccessible')) {
                            $property->setAccessible(true);
                            $return[$name] = $this->encodeObject($property->getValue($object), $objectDepth + 1);
                        } elseif ($property->isPublic()) {
                            $return[$name] = $this->encodeObject($property->getValue($object), $objectDepth + 1);
                        } else {
                            $return[$name] = '** Need PHP 5.3 to get value **';
                        }
                    }
                } else {
                    $return[$name] = '** Excluded by Filter **';
                }
            }

            // Include all members that are not defined in the class
            // but exist in the object
            foreach ($members as $just_name => $value) {
                $name = $raw_name = $just_name;

                if ($name[0] == "\0") {
                    $parts = explode("\0", $name);
                    $name = $parts[2];
                }
                if (!isset($properties[$name])) {
                    $name = 'undeclared:' . $name;

                    if (!(isset($this->objectFilters[$class]) && is_array($this->objectFilters[$class]) && in_array(
                                    $just_name,
                                    $this->objectFilters[$class]
                            ))
                    ) {
                        $return[$name] = $this->encodeObject($value, $objectDepth + 1);
                    } else {
                        $return[$name] = '** Excluded by Filter **';
                    }
                }
            }

            array_pop($this->objectStack);
        } elseif (is_array($object)) {
            if ($arrayDepth > $this->options['maxArrayDepth']) {
                return '** Max Array Depth (' . $this->options['maxArrayDepth'] . ') **';
            }

            foreach ($object as $key => $val) {
                // Encoding the $GLOBALS PHP array causes an infinite loop
                // if the recursion is not reset here as it contains
                // a reference to itself. This is the only way I have come up
                // with to stop infinite recursion in this case.
                if ($key == 'GLOBALS' && is_array($val) && array_key_exists('GLOBALS', $val)) {
                    $val['GLOBALS'] = '** Recursion (GLOBALS) **';
                }
                $return[$key] = $this->encodeObject($val, 1, $arrayDepth + 1);
            }
        } else {
            return $object;
        }

        return $return;
    }
}
