<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page,
    Behat\Mink\Exception\ExpectationException,
    Behat\Behat\Context\Step;

class Helper extends Page
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
    public function checkArray($check)
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
    public function toFloat($values)
    {
        foreach ($values as $key => $value) {
            if (!is_float($value)) {
                $value = str_replace(array('ab', ' ', '.'), '', $value);
                $value = str_replace(',', '.', $value);

                $values[$key] = floatval($value);
            }
        }

        return $values;
    }

    /**
     * Helper function to count a HTML-Element on a page.
     * If the number is not equal to $count, the function will throw an exception $message.
     * If the number is equal to $count, the function will return an array of all matching elements.
     * @param string $locator
     * @param string $message
     * @param int $count
     * @return array
     * @throws ExpectationException
     */
    public function countElements($locator, $message, $count = 0)
    {
        $articles = $this->findAll('css', $locator);

        if (count($articles) !== intval($count)) {
            $message = sprintf($message, count($articles), intval($count));
            throw new ExpectationException($message, $this->getSession());
        }

        return $articles;
    }
}