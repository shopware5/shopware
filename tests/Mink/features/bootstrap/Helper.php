<?php

namespace Shopware\Tests\Mink;

use \Behat\Behat\Tester\Exception\PendingException;
use \SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use \SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Element\MultipleElement;

class Helper
{
    private static $language;

    public static function setCurrentLanguage($language)
    {
        self::$language = $language;
    }

    /**
     * Helper function to check each row of an array.
     * If each second sub-element of a row is equal or in its first, function returns true
     * If not, the key of the element will be returned (can be used for more detailed descriptions of faults)
     * Throws an exception if $check has an incorrect format
     * @param array $check
     * @param bool $strict
     * @return bool|int|string
     * @throws \Exception
     */
    public static function checkArray(array $check, $strict = false)
    {
        foreach ($check as $key => $comparison) {
            if ((!is_array($comparison)) || (count($comparison) != 2)) {
                self::throwException('Each comparison have to be an array with exactly two values!');
            }

            $comparison = array_values($comparison);

            if ($comparison[0] === $comparison[1]) {
                continue;
            }

            if ($strict || is_float($comparison[0]) || is_float($comparison[1])) {
                return $key;
            }

            $haystack = (string)$comparison[0];
            $needle = (string)$comparison[1];

            if (strlen($needle) === 0) {
                if (strlen($haystack) === 0) {
                    return true;
                }

                return $key;
            }

            if (strpos($haystack, $needle) === false) {
                return $key;
            }
        }

        return true;
    }

    /**
     * Converts the value to a float
     * @param string $value
     * @return float
     */
    public static function floatValue($value)
    {
        if (is_float($value)) {
            return $value;
        }

        $float = str_replace([' ', '.', ','], ['', '', '.'], $value);
        preg_match("/([0-9]+[\\.]?[0-9]*)/", $float, $matches);

        return floatval($matches[0]);
    }

    /**
     * Converts values with key in $keys to floats
     * @param array $values
     * @param array $keys
     * @return array
     */
    public static function floatArray(array $values, array $keys = [])
    {
        if (is_array(current($values))) {
            foreach ($values as &$array) {
                $array = self::floatArray($array, $keys);
            }

            return $values;
        }

        if (empty($keys)) {
            $keys = array_keys($values);
        }

        foreach ($keys as $key) {
            if (isset($values[$key])) {
                $values[$key] = self::floatValue($values[$key]);
            }
        }

        return $values;
    }

    /**
     * Helper function to count a HTML-Element on a page.
     * If the number is equal to $count, the function will return true.
     * If the number is not equal to $count, the function will return the count of the element.
     *
     * @param  Element $parent
     * @param  string $elementLocator
     * @param  int $count
     * @return bool|int
     */
    public static function countElements($parent, $elementLocator, $count = 0)
    {
        $elements = self::findAllOfElements($parent, [$elementLocator], false);
        $countElements = count($elements[$elementLocator]);

        if ($countElements === $count) {
            return true;
        }

        return $countElements;
    }

    /**
     * Recursive Helper function to compare two arrays over all their levels
     * @param  array $array1
     * @param  array $array2
     * @return array|bool
     */
    public static function compareArrays($array1, $array2)
    {
        foreach ($array1 as $key => $value) {
            if (!array_key_exists($key, $array2)) {
                return [
                    'error' => 'keyNotExists',
                    'key' => $key,
                    'value' => $value,
                    'value2' => null
                ];
            }

            if (is_array($value)) {
                $result = self::compareArrays($value, $array2[$key]);

                if ($result !== true) {
                    return $result;
                }

                continue;
            }

            $check = [$value, $array2[$key]];
            $result = self::checkArray([$check]);

            if ($result !== true) {
                return [
                    'error' => 'comparisonFailed',
                    'key' => $key,
                    'value' => $value,
                    'value2' => $array2[$key]
                ];
            }
        }

        return true;
    }

    /**
     * Finds elements by their selectors
     * @param Page|Element|HelperSelectorInterface $parent
     * @param array $keys
     * @param bool $throwExceptions
     * @return Element[]
     * @throws \Exception|PendingException
     */
    public static function findElements(HelperSelectorInterface $parent, array $keys, $throwExceptions = true)
    {
        $notFound = [];
        $elements = [];

        $selectors = self::getRequiredSelectors($parent, $keys);

        foreach ($selectors as $key => $locator) {
            $element = $parent->find('css', $locator);

            if (!$element) {
                $notFound[$key] = $locator;
            }

            $elements[$key] = $element;
        }

        if ($throwExceptions) {
            $messages = ['The following elements of ' . get_class($parent) . ' were not found:'];

            foreach ($notFound as $key => $locator) {
                $messages[] = sprintf('%s ("%s")', $key, $locator);
            }

            if (count($messages) > 1) {
                self::throwException($messages);
            }
        }

        return $elements;
    }

    /**
     * Finds all elements of their selectors
     * @param Page|Element|HelperSelectorInterface $parent
     * @param array $keys
     * @param bool $throwExceptions
     * @return array
     * @throws \Exception|PendingException
     */
    public static function findAllOfElements(HelperSelectorInterface $parent, array $keys, $throwExceptions = true)
    {
        $notFound = [];
        $elements = [];

        $selectors = self::getRequiredSelectors($parent, $keys);

        foreach ($selectors as $key => $locator) {
            $element = $parent->findAll('css', $locator);

            if (!$element) {
                $notFound[$key] = $locator;
            }

            $elements[$key] = $element;
        }

        if ($throwExceptions) {
            $messages = ['The following elements of ' . get_class($parent) . ' were not found:'];

            foreach ($notFound as $key => $locator) {
                $messages[] = sprintf('%s ("%s")', $key, $locator);
            }

            if (count($messages) > 1) {
                self::throwException($messages);
            }
        }

        return $elements;
    }

    /**
     * Returns the requested element css selectors
     * @param Page|Element|HelperSelectorInterface $parent
     * @param array $keys
     * @param bool $throwExceptions
     * @return array
     * @throws \Exception
     * @throws PendingException
     */
    public static function getRequiredSelectors(HelperSelectorInterface $parent, array $keys, $throwExceptions = true)
    {
        $errors = [];
        $locators = [];
        $selectors = $parent->getCssSelectors();

        foreach ($keys as $key) {
            if (!array_key_exists($key, $selectors)) {
                $errors['noSelector'][] = $key;
                continue;
            }

            if (empty($selectors[$key])) {
                $errors['emptySelector'][] = $key;
                continue;
            }

            $locators[$key] = $selectors[$key];
        }

        if (empty($errors) || !$throwExceptions) {
            return $locators;
        }

        $message = ['Following element selectors of ' . get_class($parent) . ' are wrong:'];

        if (isset($errors['noSelector'])) {
            $message[] = sprintf('%s (not defined)', implode(', ', $errors['noSelector']));
        }
        if (isset($errors['emptySelector'])) {
            $message[] = sprintf('%s (empty)', implode(', ', $errors['emptySelector']));
        }

        self::throwException($message, self::EXCEPTION_PENDING);
    }

    /**
     * Returns the css selector of the element
     * @param HelperSelectorInterface $parent
     * @param string $key
     * @return string|bool
     */
    public static function getRequiredSelector(HelperSelectorInterface $parent, $key)
    {
        $selectors = self::getRequiredSelectors($parent, [$key], false);

        return (isset($selectors[$key])) ? $selectors[$key] : false;
    }

    const EXCEPTION_GENERIC = 1;
    const EXCEPTION_PENDING = 2;

    /**
     * Throws a generic or pending exception, shows the backtrace to the first context class call
     * @param array|string $messages
     * @param int $type
     * @throws \Exception|PendingException
     */
    public static function throwException($messages = [], $type = self::EXCEPTION_GENERIC)
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }

        $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $message = [<<<EOD
Exception thrown in {$debug[1]['class']}{$debug[1]['type']}{$debug[1]['function']}():{$debug[0]['line']}

Stacktrace:
EOD
        ];

        foreach ($debug as $key => $call) {
            $next = $debug[$key + 1];

            if (!isset($next['class'])) {
                break;
            }

            $message[] = "{$next['class']}{$next['type']}{$next['function']}():{$call['line']}";
        }

        $message[] = "\r\nException:";

        $messages = array_merge($message, $messages);
        $message = implode("\r\n", $messages);

        switch ($type) {
            case self::EXCEPTION_GENERIC:
                throw new \Exception($message);
                break;

            case self::EXCEPTION_PENDING:
                throw new PendingException($message);
                break;

            default:
                self::throwException('Invalid exception type!', self::EXCEPTION_PENDING);
                break;
        }
    }

    /**
     * Checks if a page or element has the requested named link
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string $key
     * @return bool
     */
    public static function hasNamedLink(HelperSelectorInterface $parent, $key)
    {
        return (self::hasNamedLinks($parent, [$key]) === true) ?: false;
    }

    /**
     * Searches for named links given by $keys. Returns true if all exist, otherwise an array of the not found keys.
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string[] $keys
     * @return bool|string[]
     */
    public static function hasNamedLinks(HelperSelectorInterface $parent, array $keys)
    {
        $notFound = [];
        $locatorArray = $parent->getNamedSelectors();

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        foreach($keys as $key) {
            if($parent->hasLink($locatorArray[$key][self::$language])) {
                continue;
            }

            $notFound[$key] = $locatorArray[$key][self::$language];
        }

        return ($notFound) ?: true;
    }

    /**
     * Clicks the requested named link
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string $key
     * @throws \Exception
     * @throws PendingException
     */
    public static function clickNamedLink(HelperSelectorInterface $parent, $key)
    {
        $locatorArray = $parent->getNamedSelectors();

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        $parent->clickLink($locatorArray[$key][self::$language]);
    }

    /**
     * Checks if a page or element has the requested named link
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string $key
     * @return bool
     */
    public static function hasNamedButton(HelperSelectorInterface $parent, $key)
    {
        return (self::hasNamedButtons($parent, [$key]) === true) ?: false;
    }

    /**
     * Searches for named buttons given by $keys. Returns true if all exist, otherwise an array of the not found keys.
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string[] $keys
     * @return bool|string[]
     */
    public static function hasNamedButtons(HelperSelectorInterface $parent, array $keys)
    {
        $notFound = [];
        $locatorArray = $parent->getNamedSelectors();

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        foreach($keys as $key) {
            if($parent->hasButton($locatorArray[$key][self::$language])) {
                continue;
            }

            $notFound[$key] = $locatorArray[$key][self::$language];
        }

        return ($notFound) ?: true;
    }

    /**
     * Presses the requested named button
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string $key
     */
    public static function pressNamedButton(HelperSelectorInterface $parent, $key)
    {
        $locatorArray = $parent->getNamedSelectors();

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        $parent->pressButton($locatorArray[$key][self::$language]);
    }

    /**
     * Helper method that returns the content block of a page
     * @param Page $parent
     * @return \Behat\Mink\Element\NodeElement
     * @throws \Exception
     */
    public static function getContentBlock(Page $parent)
    {
        $contentBlocks = [
            'emotion' => 'div#content > div.inner',
            'responsive' => 'div.content-main--inner'
        ];

        foreach ($contentBlocks as $locator) {
            $block = $parent->find('css', $locator);

            if ($block) {
                return $block;
            }
        }

        self::throwException('No content block found!');
    }

    /**
     * Fills a the inputs of a form
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string $formKey
     * @param array $values
     */
    public static function fillForm(HelperSelectorInterface $parent, $formKey, $values)
    {
        $elements = self::findElements($parent, [$formKey]);
        $form = $elements[$formKey];

        foreach ($values as $value) {
            $tempFieldName = $fieldName = $value['field'];
            unset($value['field']);

            foreach ($value as $key => $fieldValue) {
                if ($key !== 'value') {
                    $fieldName = sprintf('%s[%s]', $key, $tempFieldName);
                }

                $field = $form->findField($fieldName);

                if (empty($field)) {
                    if (empty($fieldValue)) {
                        continue;
                    }

                    $message = sprintf('The form "%s" has no field "%s"!', $formKey, $fieldName);
                    self::throwException($message);
                }

                $fieldTag = $field->getTagName();

                if ($fieldTag === 'textarea') {
                    $field->setValue($fieldValue);
                    continue;
                }

                $fieldType = $field->getAttribute('type');

                //Select
                if (empty($fieldType)) {
                    $field->selectOption($fieldValue);
                    continue;
                }

                //Checkbox
                if ($fieldType === 'checkbox') {
                    $field->check();
                    continue;
                }

                //Text
                $field->setValue($fieldValue);
            }
        }
    }

    /**
     * Helper function to get the current language ('de' or 'en')
     * @return string
     */
    public static function getCurrentLanguage()
    {
        return self::$language;
    }

    /**
     * Helper function to get some information about the current page
     * Possible modes are 'controller', 'action' and 'template' or a combination of them
     * Please note, that 'template' only works in combination with 'controller' and/or 'action'.
     * @param \Behat\Mink\Session $session
     * @param array $selectionMode
     * @return array|bool
     */
    public static function getPageInfo(\Behat\Mink\Session $session, array $selectionMode)
    {
        $prefixes = [
            'emotion' => [
                'controller' => 'ctl_'
            ],
            'responsive' => [
                'controller' => 'is--ctl-',
                'action' => 'is--act-'
            ]
        ];

        $body = $session->getPage()->find('css', 'body');
        $class = $body->getAttribute('class');

        foreach ($prefixes as $template => $modes) {
            $activeModes = [];

            foreach ($modes as $mode => $prefix) {
                if (in_array($mode, $selectionMode)) {
                    $activeModes[] = $prefix . '([A-Za-z]+)';
                }
            }

            if (empty($activeModes)) {
                continue;
            }

            $regex = '/' . implode(' ', $activeModes) . '/';

            if (preg_match($regex, $class, $mode) !== 1) {
                continue;
            }

            $result = array_fill_keys($selectionMode, null);

            if (array_key_exists('controller', $result)) {
                $result['controller'] = $mode['1'];

                if (array_key_exists('action', $result) && isset($mode['2'])) {
                    $result['action'] = $mode['2'];
                }
            } elseif (array_key_exists('action', $result) && isset($mode['1'])) {
                $result['action'] = $mode['1'];
            }

            if (array_key_exists('template', $result)) {
                $result['template'] = $template;
            }

            return $result;
        }

        return false;
    }

    /**
     * @param HelperSelectorInterface $element
     * @param bool $throwExceptions
     * @return array
     */
    public static function getElementData(HelperSelectorInterface $element, $throwExceptions = true)
    {
        $locators = array_keys($element->getCssSelectors());
        $elements = self::findAllOfElements($element, $locators, $throwExceptions);

        $result = array_fill_keys($locators, null);

        foreach ($elements as $key => $subElement) {
            if (empty($subElement)) {
                continue;
            }
            $method = 'get' . ucfirst($key) . 'Data';
            $result[$key] = $element->$method($subElement);
        }

        return $result;
    }

    /**
     * @param array $hash
     * @param string $keyKey
     * @param string $valueKey
     * @return array
     */
    public static function convertTableHashToArray(array $hash, $keyKey = 'property', $valueKey = 'value')
    {
        $result = [];

        foreach ($hash as $item) {
            $key = $item[$keyKey];
            $value = $item[$valueKey];
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Returns the unique value of an array, throws in exception if there are differences
     * @param $array
     * @return string
     * @throws \Exception
     */
    public static function getUnique(array $array)
    {
        $unique = array_unique($array);

        if (count($unique) > 1) {
            $messages = ['There are more than one unique values in the array!'];
            foreach ($unique as $key => $value) {
                $messages[] = sprintf('"%s" (Key: "%s")', $value, $key);
            }

            self::throwException($messages);
        }

        return current($unique);
    }

    /**
     *
     * @param Element $element
     * @param string $propertyName
     * @return string|float|array
     */
    public static function getElementProperty(Element $element, $propertyName)
    {
        $method = 'get' . ucFirst($propertyName) . 'Property';
        return $element->$method();
    }

    /**
     *
     * @param Element $element
     * @param array $properties
     * @return bool|array
     */
    public static function assertElementProperties(Element $element, array $properties)
    {
        $check = [];

        foreach ($properties as $propertyName => $value) {
            $property = self::getElementProperty($element, $propertyName);
            $check[$propertyName] = [$property, $value];
        }

        $result = self::checkArray($check);

        if ($result === true) {
            return true;
        }

        return [
            'key' => $result,
            'value' => $check[$result][0],
            'value2' => $check[$result][1]
        ];
    }

    /** @var  MultipleElement */
    private static $filterElements;

    /**
     *
     * @param $var
     * @return bool
     */
    private static function filter($var)
    {
        /** @var MultipleElement $element */
        foreach (self::$filterElements as $element) {
            if (self::assertElementProperties($element, $var) === true) {
                self::$filterElements->remove();

                return false;
            }
        }

        return true;
    }

    /**
     *
     * @param array $needles
     * @param MultipleElement $haystack
     * @return bool|array
     */
    public static function searchElements(array $needles, MultipleElement $haystack)
    {
        self::$filterElements = $haystack;
        $result = array_filter($needles, ['self', 'filter']);

        if ($result) {
            return $result;
        }

        return true;
    }

    /**
     *
     * @param array $needles
     * @param MultipleElement $haystack
     * @return array|bool
     */
    public static function assertElements(array $needles, MultipleElement $haystack)
    {
        $failures = [];

        foreach ($needles as $key => $item) {
            $element = $haystack->setInstance($key + 1);
            $result = self::assertElementProperties($element, $item);

            if ($result !== true) {
                $failures[] = [
                    'properties' => $item,
                    'result' => $result
                ];
            }
        }

        if ($failures) {
            return $failures;
        }

        return true;
    }

    /**
     * Global method to check the count of an MultipleElement
     * @param MultipleElement $elements
     * @param int              $count
     */
    public static function assertElementCount(MultipleElement $elements, $count = 0)
    {
        if ($count !== count($elements)) {
            $message = sprintf(
                'There are %d elements of type "%s" on page (should be %d)',
                count($elements),
                get_class($elements),
                $count
            );
            self::throwException($message);
        }
    }
}
