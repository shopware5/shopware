<?php

namespace Shopware\Tests\Mink;


use \Behat\Behat\Exception\PendingException;
use \SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use \SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Element\MultipleElement;
use Shopware\Tests\Mink\Helper as MinkHelper;

class Helper
{
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
        $locator = array($elementLocator);
        $elements = self::findAllOfElements($parent, $locator, false);

        $countElements = count($elements[$elementLocator]);

        if ($countElements === intval($count)) {
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
                return array(
                    'error' => 'keyNotExists',
                    'key' => $key,
                    'value' => $value,
                    'value2' => null
                );
            }

            if (is_array($value)) {
                $result = self::compareArrays($value, $array2[$key]);

                if ($result !== true) {
                    return $result;
                }

                continue;
            }

            $check = array($value, $array2[$key]);
            $result = self::checkArray(array($check));

            if ($result !== true) {
                return array(
                    'error' => 'comparisonFailed',
                    'key' => $key,
                    'value' => $value,
                    'value2' => $array2[$key]
                );
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
        $notFound = array();
        $elements = array();

        $selectors = self::getRequiredSelectors($parent, $keys);

        foreach ($selectors as $key => $locator) {
            $element = $parent->find('css', $locator);

            if (!$element) {
                $notFound[$key] = $locator;
            }

            $elements[$key] = $element;
        }

        if ($throwExceptions) {
            $messages = array('The following elements of ' . get_class($parent) . ' were not found:');

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
        $notFound = array();
        $elements = array();

        $selectors = self::getRequiredSelectors($parent, $keys);

        foreach ($selectors as $key => $locator) {
            $element = $parent->findAll('css', $locator);

            if (!$element) {
                $notFound[$key] = $locator;
            }

            $elements[$key] = $element;
        }

        if ($throwExceptions) {
            $messages = array('The following elements of ' . get_class($parent) . ' were not found:');

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
        $errors = array();
        $locators = array();
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

        $message = array('Following element selectors of ' . get_class($parent) . ' are wrong:');

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
        $selectors = self::getRequiredSelectors($parent, array($key), false);

        return (isset($selectors[$key])) ? $selectors[$key] : false;
    }

    const EXCEPTION_GENERIC = 1;
    const EXCEPTION_PENDING = 2;

    /**
     * Throws a generic or pending exception
     * @param array|string $messages
     * @param int $type
     * @throws \Exception|PendingException
     */
    public static function throwException($messages = array(), $type = self::EXCEPTION_GENERIC)
    {
        if (!is_array($messages)) {
            $messages = array($messages);
        }

        $debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);

        $additionalText = '';

        if (isset($debug[2]['class'])) {
            $additionalText = sprintf(
                ', called by %s%s%s() (line %d)',
                $debug[2]['class'],
                $debug[2]['type'],
                $debug[2]['function'],
                $debug[1]['line']
            );
        }

        $message = sprintf(
            'Exception thrown in %s%s%s() (line %d%s)',
            $debug[1]['class'],
            $debug[1]['type'],
            $debug[1]['function'],
            $debug[0]['line'],
            $additionalText
        );

        $messages = array_merge(array($message), $messages);
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
     * @param string $language
     * @return bool
     * @throws \Exception
     * @throws PendingException
     */
    public static function hasNamedLink(HelperSelectorInterface $parent, $key, $language = '')
    {
        $locatorArray = $parent->getNamedSelectors();

        if (empty($language)) {
            if ($parent instanceof Page) {
                $language = self::getCurrentLanguage($parent);
            } else {
                self::throwException('For elements the language has to be set!', self::EXCEPTION_PENDING);
            }
        }

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        return $parent->hasLink($locatorArray[$key][$language]);
    }

    /**
     * Clicks the requested named link
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string $key
     * @param string $language
     * @throws \Exception
     * @throws PendingException
     */
    public static function clickNamedLink(HelperSelectorInterface $parent, $key, $language = '')
    {
        $locatorArray = $parent->getNamedSelectors();

        if (empty($language)) {
            if ($parent instanceof Page) {
                $language = self::getCurrentLanguage($parent);
            } else {
                self::throwException('For elements the language has to be set!', self::EXCEPTION_PENDING);
            }
        }

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        $parent->clickLink($locatorArray[$key][$language]);
    }

    /**
     * Presses the requested named button
     * @param Page|Element|HelperSelectorInterface $parent
     * @param string $key
     * @param string $language
     */
    public static function pressNamedButton(HelperSelectorInterface $parent, $key, $language = '')
    {
        $locatorArray = $parent->getNamedSelectors();

        if (empty($language)) {
            if ($parent instanceof Page) {
                $language = self::getCurrentLanguage($parent);
            } else {
                self::throwException('For elements the language has to be set!', self::EXCEPTION_PENDING);
            }
        }

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        $parent->pressButton($locatorArray[$key][$language]);
    }

    /**
     * Helper method that returns the content block of a page
     * @param Page $parent
     * @return \Behat\Mink\Element\NodeElement
     * @throws \Exception
     */
    private static function getContentBlock(Page $parent)
    {
        $contentBlocks = array(
            'emotion' => 'div#content > div.inner',
            'responsive' => 'div.content-main--inner'
        );

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
     * @param $formKey
     * @param $values
     */
    public static function fillForm(HelperSelectorInterface $parent, $formKey, $values)
    {
        $locators = array($formKey);
        $elements = self::findElements($parent, $locators);

        /** @var \SensioLabs\Behat\PageObjectExtension\PageObject\Element $form */
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
     * @param Page $page
     * @return string
     */
    public static function getCurrentLanguage(Page $page)
    {
        $shop = null;
        $meta = $page->find('css', 'meta[name=application-name]');
        $shop = $meta->getAttribute('content');

        if ($shop === 'English') {
            return 'en';
        }

        return 'de';
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
        $prefixes = array(
            'emotion' => array(
                'controller' => 'ctl_'
            ),
            'responsive' => array(
                'controller' => 'is--ctl-',
                'action' => 'is--act-'
            )
        );

        $body = $session->getPage()->find('css', 'body');
        $class = $body->getAttribute('class');

        foreach ($prefixes as $template => $modes) {
            $activeModes = array();

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
     * @deprecated Only used in sitemap
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
        $result = array();

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
            $messages = array('There are more than one unique values in the array!');
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
        $check = array();

        foreach ($properties as $propertyName => $value) {
            $property = self::getElementProperty($element, $propertyName);
            $check[$propertyName] = array($property, $value);
        }

        $result = self::checkArray($check);

        if ($result === true) {
            return true;
        }

        return array(
            'key' => $result,
            'value' => $check[$result][0],
            'value2' => $check[$result][1]
        );
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
        $result = array_filter($needles, array('self', 'filter'));

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
        $failures = array();

        foreach ($needles as $key => $item) {
            $element = $haystack->setInstance($key + 1);
            $result = self::assertElementProperties($element, $item);

            if ($result !== true) {
                $failures[] = array(
                    'properties' => $item,
                    'result' => $result
                );
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
            MinkHelper::throwException($message);
        }
    }
}
