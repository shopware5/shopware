<?php
use Behat\Mink\Element\Element;
use Behat\Mink\Element\TraversableElement;

class Helper
{
    /**
     * Helper function to check each row of an array.
     * If each second sub-element of a row is equal or in its first, function returns true
     * If not, the key of the element will be returned (can be used for more detailed descriptions of faults)
     * Throws an exception if $check has an incorrect format
     * @param $check
     * @return bool|int|string
     * @throws Exception
     */
    public static function checkArray($check)
    {
        if (!is_array($check)) {
            throw new \Exception('$check have to be an array');
        }

        foreach ($check as $key => $compare) {

            if ((!is_array($compare)) || (count($compare) != 2)) {
                throw new \Exception('Each compare have to be an array with exactly two values!');
            }

            $compare = array_values($compare);

            if ($compare[0] === $compare[1]) {
                continue;
            }

            if (strpos((string)$compare[0], (string)$compare[1]) === false) {
                return $key;
            }
        }

        return true;
    }

    /**
     * Helper function to validate values to floats
     * @param array $values
     * @return array
     */
    public static function toFloat($values)
    {
        foreach ($values as $key => $value) {
            if (!is_float($value)) {
                preg_match("/\d+[\.*]*[\d+\.*]*[,\d+]*/", $value, $value); //matches also numbers like 123.456.789,00

                $value = $value[0];

                if(!is_numeric($value)) {
                    $value = str_replace('.', '', $value);
                    $value = str_replace(',', '.', $value);
                }

                $values[$key] = floatval($value);
            }
        }

        return $values;
    }

    /**
     * Helper function to count a HTML-Element on a page.
     * If the number is equal to $count, the function will return true.
     * If the number is not equal to $count, the function will return the count of the element.
     *
     * @param Element $parent
     * @param string $elementLocator
     * @param int $count
     * @return bool|int
     */
    public static function countElements($parent, $elementLocator, $count = 0)
    {
        $locator = array('element' => $elementLocator);
        $elements = self::findElements($parent, array(), $locator, true, false);

        $countElements = count($elements['element']);

        if ($countElements === intval($count)) {
            return true;
        }

        return $countElements;
    }

    /**
     * Recursive Helper function to compare two arrays over all their levels
     *
     * @param array $array1
     * @param array $array2
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
     * Helper function to find one or more page elements.
     * Throws an exception if one ore more elements were not found.
     * Returns an array of the elements if all were found.
     * If $keys parameter is set, only the elements with matching keys were searched.
     * If $locatorArray parameter is set, the element locators will be read from it instead of the cssLocator of $parent
     * If $all is set to true, the search uses findAll() instead of find()
     * If $throwExceptions is set to false, no Exception will be thrown, if an element was not found.
     * @param Element $parent
     * @param array $keys
     * @param array $locatorArray
     * @param bool $all
     * @param bool $throwExceptions
     * @return array
     * @throws Exception
     */
    public static function findElements($parent, $keys = array(), $locatorArray = array(), $all = false, $throwExceptions = true)
    {
        $missingElements = array();
        $elements = array();

        if (empty($locatorArray)) {
            if(isset($parent->cssLocator)) {
                $locatorArray = $parent->cssLocator;
            }
            else {
                throw new \Exception('No locatorArray defined!');
            }
        }

        //if $keys is empty, find all Elements defined in $locatorArray
        if (empty($keys)) {
            $keys = array_keys($locatorArray);
        }

        //$keys array have to have no numeric indices, each value have to be an array
        foreach ($keys as $key => $locator) {
            if (is_integer($key)) {
                $keys[$locator] = array();
                unset($keys[$key]);
                continue;
            }
            if (!is_array($locator)) {
                $keys[$key] = array($locator);
            }
        }

        //get all locators of elements to found given by the $keys arrays keys
        $locators = array_intersect_key($locatorArray, $keys);

        //check if for each given $key exists an locator
        foreach ($keys as $key => $values) {
            if (!array_key_exists($key, $locators)) {
                $missingElements['noLocator'][] = $key;
            }
        }

        foreach ($locators as $key => $locator) {
            //each locator can have some variables in it, so they have to be filleTest'd with values of $keys array
            $oldErrorReporting = error_reporting(0);
            $locator = vsprintf($locator, $keys[$key]);
            error_reporting($oldErrorReporting);
            $locator = trim($locator);

            //check if locator is empty, p.e. because there were a different number of values than variables in the locator
            if (empty($locator)) {
                $missingElements['emptyLocator'][] = $key;
                continue;
            }

            //find the element matching to the css locator
            if ($all) {
                $element = $parent->findAll('css', $locator);
            } else {
                $element = $parent->find('css', $locator);
            }

            if (empty($element)) {
                $missingElements['notFound'][] = $key;
                $elements[$key] = array();
                continue;
            }

            $elements[$key] = $element;
        }

        if (empty($missingElements)) {
            return $elements;
        }

        if($throwExceptions)
        {
            $message = array('Following elements were not found:');

            if (isset($missingElements['noLocator'])) {
                $message[] = sprintf('%s (no locator defined)', implode(', ', $missingElements['noLocator']));
            }
            if (isset($missingElements['emptyLocator'])) {
                $message[] = sprintf('%s (locator is empty)', implode(', ', $missingElements['emptyLocator']));
            }
            if (isset($missingElements['notFound'])) {
                $message[] = sprintf('%s (element not found)', implode(', ', $missingElements['notFound']));
            }

            self::throwException($message);
        }

        return $elements;
    }

    public static function throwException($messages = array())
    {
        if(!is_array($messages)) {
            $messages = array($messages);
        }

        $debug = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);

        $additionalText = '';

        if(isset($debug[2]['class']))
        {
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

        throw new \Exception(implode(
            '
',
            $messages
        ));
    }

    /**
     * @param SubContext $context
     * @param string $page
     * @param string $key
     * @param array $locatorArray
     */
    public static function pressNamedButton(SubContext $context, $page, $key, $locatorArray = array())
    {
        if (empty($page)) {
            self::throwException(array('No page defined!'));
        }

        $parent = $context->getPage($page);

        if (empty($locatorArray)) {
            if (isset($parent->namedSelectors)) {
                $locatorArray = $parent->namedSelectors;
            } else {
                self::throwException(array('No locatorArray defined!'));
            }
        }

        $language = $context->getElement('LanguageSwitcher')->getCurrentLanguage();

        $parent->clickLink($locatorArray[$key][$language]);
    }
}
