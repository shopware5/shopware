<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Mink\Tests\General\Helpers;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;
use Exception;
use RuntimeException;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Helper\Elements\MultipleElement;
use WebDriver\Exception\StaleElementReference;

class Helper
{
    public const EXCEPTION_GENERIC = 1;
    public const EXCEPTION_PENDING = 2;
    public const DEFAULT_WAIT_TIME = 60;

    private static string $language;

    private static MultipleElement $filterElements;

    public static function setCurrentLanguage(string $language): void
    {
        self::$language = $language;
    }

    /**
     * Helper function to check each row of an array.
     * If each second sub-element of a row is equal or in its first, function returns true
     * If not, the key of the element will be returned (can be used for more detailed descriptions of faults)
     * Throws an exception if $check has an incorrect format
     *
     * @throws Exception
     *
     * @return true|array-key
     */
    public static function checkArray(array $check, bool $strict = false)
    {
        foreach ($check as $key => $comparison) {
            if ((!\is_array($comparison)) || (\count($comparison) !== 2)) {
                self::throwException('Each comparison have to be an array with exactly two values!');
            }

            $comparison = array_values($comparison);

            if ($comparison[0] === $comparison[1]) {
                continue;
            }

            if ($strict || \is_float($comparison[0]) || \is_float($comparison[1])) {
                return $key;
            }

            $haystack = (string) $comparison[0];
            $needle = (string) $comparison[1];

            if ($needle === '') {
                if ($haystack === '') {
                    return true;
                }

                return $key;
            }

            if (!str_contains($haystack, $needle)) {
                return $key;
            }
        }

        return true;
    }

    /**
     * Converts the value to a float
     *
     * @param string|float|int $value
     */
    public static function floatValue($value): float
    {
        if (\is_float($value)) {
            return $value;
        }

        if (\is_int($value)) {
            return (float) $value;
        }

        $float = str_replace([' ', '.', ','], ['', '', '.'], $value);
        preg_match('/([0-9]+[\\.]?[0-9]*)/', $float, $matches);

        return (float) $matches[0];
    }

    /**
     * Converts values with key in $keys to floats
     */
    public static function floatArray(array $values, array $keys = []): array
    {
        if (\is_array(current($values))) {
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
     * Helper function to count an HTML-Element on a page.
     * If the number is equal to $count, the function will return true.
     * If the number is not equal to $count, the function will return the count of the element.
     *
     * @param Element&HelperSelectorInterface $parent
     *
     * @return bool|int
     */
    public static function countElements(Element $parent, string $elementLocator, int $count = 0)
    {
        $elements = self::findAllOfElements($parent, [$elementLocator], false);
        $countElements = \count($elements[$elementLocator]);

        if ($countElements === $count) {
            return true;
        }

        return $countElements;
    }

    /**
     * Recursive Helper function to compare two arrays over all their levels
     *
     * @return array|bool
     */
    public static function compareArrays(array $array1, array $array2)
    {
        foreach ($array1 as $key => $value) {
            if (!\array_key_exists($key, $array2)) {
                return [
                    'error' => 'keyNotExists',
                    'key' => $key,
                    'value' => $value,
                    'value2' => null,
                ];
            }

            if (\is_array($value)) {
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
                    'value2' => $array2[$key],
                ];
            }
        }

        return true;
    }

    /**
     * Finds elements by their selectors
     *
     * @param (Page|Element)&HelperSelectorInterface $parent
     * @param array<string>                          $keys
     *
     * @throws Exception|PendingException
     *
     * @return array<string, NodeElement>
     */
    public static function findElements($parent, array $keys, bool $throwExceptions = true): array
    {
        $notFound = [];
        $elements = [];

        $selectors = self::getRequiredSelectors($parent, $keys);

        foreach ($selectors as $key => $locator) {
            $element = $parent->find('css', $locator);

            if (!$element) {
                $notFound[$key] = $locator;
                continue;
            }

            $elements[$key] = $element;
        }

        if ($throwExceptions) {
            $messages = ['The following elements of ' . \get_class($parent) . ' were not found:'];

            foreach ($notFound as $key => $locator) {
                $messages[] = sprintf('%s ("%s")', $key, $locator);
            }

            if (\count($messages) > 1) {
                self::throwException($messages);
            }
        }

        return $elements;
    }

    /**
     * Finds all elements of their selectors
     *
     * @param (Page|Element)&HelperSelectorInterface $parent
     *
     * @throws Exception|PendingException
     */
    public static function findAllOfElements($parent, array $keys, bool $throwExceptions = true): array
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
            $messages = ['The following elements of ' . \get_class($parent) . ' were not found:'];

            foreach ($notFound as $key => $locator) {
                $messages[] = sprintf('%s ("%s")', $key, $locator);
            }

            if (\count($messages) > 1) {
                self::throwException($messages);
            }
        }

        return $elements;
    }

    /**
     * Returns the requested element css selectors
     *
     * @param (Page|Element)&HelperSelectorInterface $parent
     * @param array<string>                          $keys
     *
     * @throws Exception
     * @throws PendingException
     *
     * @return array<string, string>
     */
    public static function getRequiredSelectors($parent, array $keys, bool $throwExceptions = true): array
    {
        $errors = [];
        $locators = [];
        $selectors = $parent->getCssSelectors();

        foreach ($keys as $key) {
            if (!\array_key_exists($key, $selectors)) {
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

        $message = ['Following element selectors of ' . \get_class($parent) . ' are wrong:'];

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
     *
     * @param (Page|Element)&HelperSelectorInterface $parent
     */
    public static function getRequiredSelector(HelperSelectorInterface $parent, string $key): string
    {
        $selectors = self::getRequiredSelectors($parent, [$key], false);

        if (isset($selectors[$key])) {
            return $selectors[$key];
        }

        self::throwException(sprintf('Could not find "%s" selector', $key));
    }

    /**
     * Throws a generic or pending exception, shows the backtrace to the first context class call
     *
     * @param array|string $messages
     *
     * @throws Exception|PendingException
     *
     * @return never-return
     */
    public static function throwException($messages = [], int $type = self::EXCEPTION_GENERIC): void
    {
        if (!\is_array($messages)) {
            $messages = [$messages];
        }

        $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $debugClass = $debug[1]['class'] ?? '';
        $debugType = $debug[1]['type'] ?? '';
        $debugLine = $debug[0]['line'] ?? '';

        $message = [<<<EOD
Exception thrown in $debugClass$debugType{$debug[1]['function']}():$debugLine

Stacktrace:
EOD
        ];

        foreach ($debug as $key => $call) {
            $next = $debug[$key + 1];

            if (!isset($next['class'])) {
                break;
            }

            $nextType = $next['type'] ?? '';
            $callLine = $call['line'] ?? '';
            $message[] = "{$next['class']}$nextType{$next['function']}():$callLine";
        }

        $message[] = "\r\nException:";

        $messages = array_merge($message, $messages);
        $message = implode("\r\n", $messages);

        switch ($type) {
            case self::EXCEPTION_GENERIC:
                throw new RuntimeException($message);
            case self::EXCEPTION_PENDING:
                throw new PendingException($message);
            default:
                self::throwException('Invalid exception type!', self::EXCEPTION_PENDING);
        }
    }

    /**
     * Checks if a page or element has the requested named link
     *
     * @param (Page|Element)&HelperSelectorInterface $parent
     */
    public static function hasNamedLink(HelperSelectorInterface $parent, string $key): bool
    {
        return self::hasNamedLinks($parent, [$key]) === true;
    }

    /**
     * Searches for named links given by $keys. Returns true if all exist, otherwise an array of the not found keys.
     *
     * @param (Page|Element)&HelperSelectorInterface $parent
     * @param string[]                               $keys
     *
     * @return bool|string[]
     */
    public static function hasNamedLinks(HelperSelectorInterface $parent, array $keys)
    {
        $notFound = [];
        $locatorArray = $parent->getNamedSelectors();

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        foreach ($keys as $key) {
            if ($parent->hasLink($locatorArray[$key][self::$language])) {
                continue;
            }

            $notFound[$key] = $locatorArray[$key][self::$language];
        }

        return $notFound ?: true;
    }

    /**
     * Clicks the requested named link
     *
     * @param (Page|Element)&HelperSelectorInterface $parent
     *
     * @throws Exception
     * @throws PendingException
     */
    public static function clickNamedLink(HelperSelectorInterface $parent, string $key): void
    {
        $locatorArray = $parent->getNamedSelectors();

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        $parent->clickLink($locatorArray[$key][self::$language]);
    }

    /**
     * Checks if a page or element has the requested named link
     *
     * @param (Page|Element)&HelperSelectorInterface $parent
     */
    public static function hasNamedButton(HelperSelectorInterface $parent, string $key): bool
    {
        return self::hasNamedButtons($parent, [$key]) === true;
    }

    /**
     * Searches for named buttons given by $keys. Returns true if all exist, otherwise an array of the not found keys.
     *
     * @param (Page|Element)&HelperSelectorInterface $parent
     * @param string[]                               $keys
     *
     * @return bool|string[]
     */
    public static function hasNamedButtons(HelperSelectorInterface $parent, array $keys)
    {
        $notFound = [];
        $locatorArray = $parent->getNamedSelectors();

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        foreach ($keys as $key) {
            if ($parent->hasButton($locatorArray[$key][self::$language])) {
                continue;
            }

            $notFound[$key] = $locatorArray[$key][self::$language];
        }

        return $notFound ?: true;
    }

    /**
     * Presses the requested named button
     *
     * @param (Page|Element)&HelperSelectorInterface $parent
     */
    public static function pressNamedButton(HelperSelectorInterface $parent, string $key): void
    {
        $locatorArray = $parent->getNamedSelectors();

        if ($parent instanceof Page) {
            $parent = self::getContentBlock($parent);
        }

        self::spin(static function () use ($parent, $locatorArray, $key): bool {
            try {
                $parent->pressButton($locatorArray[$key][self::$language]);

                return true;
            } catch (Exception $e) {
                // got stale element, try again (refresh happens in the $parents pressButton method)
                return false;
            }
        });
    }

    /**
     * Helper method that returns the content block of a page
     *
     * @throws Exception
     */
    public static function getContentBlock(Page $parent): NodeElement
    {
        $contentBlocks = [
            'emotion' => 'div#content > div.inner',
            'responsive' => 'div.content-main--inner',
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
     * Fills inputs of a form
     *
     * @param (Page|Element)&HelperSelectorInterface $parent
     * @param array<array<string, string>>           $values
     */
    public static function fillForm(HelperSelectorInterface $parent, string $formKey, array $values, bool $waitForOverlays = false): void
    {
        foreach ($values as $value) {
            $tempFieldName = $fieldName = $value['field'];
            unset($value['field']);

            foreach ($value as $key => $fieldValue) {
                if ($fieldValue === '<ignore>') {
                    continue;
                }

                if ($waitForOverlays) {
                    self::waitForOverlay($parent->getSession()->getPage());
                }

                if ($key !== 'value') {
                    $fieldName = sprintf('%s[%s]', $key, $tempFieldName);
                }

                if (str_contains($fieldName, '.')) {
                    $fieldName = str_replace('.', '][', $fieldName);
                }

                $elements = self::findElements($parent, [$formKey]);
                $form = $elements[$formKey];

                $field = $form->findField($fieldName);

                if (!$field instanceof NodeElement) {
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

                self::spin(static function () use (&$field, $fieldType, $fieldValue, $form, $fieldName): bool {
                    try {
                        if (!$field instanceof NodeElement) {
                            return false;
                        }

                        // Select
                        if (empty($fieldType)) {
                            $field->selectOption($fieldValue);

                            return true;
                        }

                        // Checkbox
                        if ($fieldType === 'checkbox') {
                            $field->check();

                            return true;
                        }

                        // Text
                        $field->setValue($fieldValue);

                        return true;
                    } catch (StaleElementReference $e) {
                        // got stale element, refresh and try again
                        $field = $form->findField($fieldName);

                        return false;
                    }
                });
            }
        }
    }

    /**
     * Helper function to get the current language ('de' or 'en')
     */
    public static function getCurrentLanguage(): string
    {
        return self::$language;
    }

    /**
     * Helper function to get some information about the current page
     * Possible modes are 'controller', 'action' and 'template' or a combination of them
     * Please note, that 'template' only works in combination with 'controller' and/or 'action'.
     *
     * @return array|bool
     */
    public static function getPageInfo(Session $session, array $selectionMode)
    {
        $prefixes = [
            'emotion' => [
                'controller' => 'ctl_',
            ],
            'responsive' => [
                'controller' => 'is--ctl-',
                'action' => 'is--act-',
            ],
        ];

        $body = $session->getPage()->find('css', 'body');
        if (!$body instanceof NodeElement) {
            self::throwException('body not found');
        }
        $class = (string) $body->getAttribute('class');

        foreach ($prefixes as $template => $modes) {
            $activeModes = [];

            foreach ($modes as $mode => $prefix) {
                if (\in_array($mode, $selectionMode)) {
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

            if (\array_key_exists('controller', $result)) {
                $result['controller'] = $mode['1'];

                if (\array_key_exists('action', $result) && isset($mode['2'])) {
                    $result['action'] = $mode['2'];
                }
            } elseif (\array_key_exists('action', $result) && isset($mode['1'])) {
                $result['action'] = $mode['1'];
            }

            if (\array_key_exists('template', $result)) {
                $result['template'] = $template;
            }

            return $result;
        }

        return false;
    }

    /**
     * @param Element&HelperSelectorInterface $element
     *
     * @return array<string, mixed>
     */
    public static function getElementData(HelperSelectorInterface $element, bool $throwExceptions = true): array
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

    public static function convertTableHashToArray(array $hash, string $keyKey = 'property', string $valueKey = 'value'): array
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
     *
     * @throws Exception
     */
    public static function getUnique(array $array): string
    {
        $unique = array_unique($array);

        if (\count($unique) > 1) {
            $messages = ['There are more than one unique values in the array!'];
            foreach ($unique as $key => $value) {
                $messages[] = sprintf('"%s" (Key: "%s")', $value, $key);
            }

            self::throwException($messages);
        }

        return current($unique);
    }

    /**
     * @return string|float|array
     */
    public static function getElementProperty(Element $element, string $propertyName)
    {
        $method = 'get' . ucfirst($propertyName) . 'Property';

        return $element->$method();
    }

    /**
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
            'value2' => $check[$result][1],
        ];
    }

    /**
     * @return true|array
     */
    public static function searchElements(array $needles, MultipleElement $haystack)
    {
        self::$filterElements = $haystack;
        $result = array_filter($needles, [__CLASS__, 'filter']);

        if ($result) {
            return $result;
        }

        return true;
    }

    /**
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
                    'result' => $result,
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
     */
    public static function assertElementCount(MultipleElement $elements, int $count = 0): void
    {
        if ($count !== \count($elements)) {
            $message = sprintf(
                'There are %d elements of type "%s" on page (should be %d)',
                \count($elements),
                \get_class($elements),
                $count
            );
            self::throwException($message);
        }
    }

    public static function spin(callable $lambda, int $wait = self::DEFAULT_WAIT_TIME, ?object $callingClass = null): void
    {
        if (!self::spinWithNoException($lambda, $wait, $callingClass)) {
            self::throwException(sprintf('Spin function timed out after %s seconds', $wait));
        }
    }

    /**
     * Based on Behat's own example
     *
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     */
    public static function spinWithNoException(callable $lambda, int $wait = self::DEFAULT_WAIT_TIME, ?object $callingClass = null): bool
    {
        $time = time();
        $stopTime = $time + $wait;
        while (time() < $stopTime) {
            try {
                if ($lambda($callingClass)) {
                    return true;
                }
            } catch (Exception $e) {
                // do nothing
            }

            usleep(250000);
        }

        return false;
    }

    public static function waitForOverlay(DocumentElement $page): void
    {
        $page->waitFor(4000, static function () use ($page) {
            try {
                $element = $page->find('css', '.js--overlay');

                return $element === null;
            } catch (Exception $e) {
                return true;
            }
        });
    }

    private static function filter(array $var): bool
    {
        foreach (self::$filterElements as $element) {
            if (self::assertElementProperties($element, $var) === true) {
                self::$filterElements->remove();

                return false;
            }
        }

        return true;
    }
}
