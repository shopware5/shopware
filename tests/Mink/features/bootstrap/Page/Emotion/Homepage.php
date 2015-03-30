<?php

namespace Page\Emotion;

use Element\MultipleElement;
use Behat\Mink\Element\TraversableElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class Homepage extends Page implements \HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'newsletterForm' => 'div.footer_column.col4 > form',
            'newsletterFormSubmit' => 'div.footer_column.col4 > form input[type="submit"]'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array();
    }

    /**
     * Searches the given term in the shop
     * @param string $searchTerm
     */
    public function searchFor($searchTerm)
    {
        $data = array(
            array(
                'field' => 'sSearch',
                'value' => $searchTerm
            )
        );

        $searchForm = $this->getElement('SearchForm');
        $language = \Helper::getCurrentLanguage($this);
        \Helper::fillForm($searchForm, 'searchForm', $data);
        \Helper::pressNamedButton($searchForm, 'searchButton', $language);
        $this->verifyResponse();
    }

    /**
     * Search the given term using live search
     * @param $searchTerm
     */
    public function receiveSearchResultsFor($searchTerm)
    {
        $data = array(
            array(
                'field' => 'sSearch',
                'value' => $searchTerm
            )
        );

        $searchForm = $this->getElement('SearchForm');
        \Helper::fillForm($searchForm, 'searchForm', $data);
        $this->getSession()->wait(5000, "$('ul.searchresult').children().length > 0");
    }

    /**
     * @param string $keyword
     */
    public function receiveNoResultsMessageForKeyword($keyword)
    {
        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->pageTextContains(sprintf(
            'Leider wurden zu "%s" keine Artikel gefunden',
            $keyword
        ));
    }

    /**
     * Changes the currency
     * @param string $currency
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function changeCurrency($currency)
    {
        $this->pressButton($currency);
    }

    /**
     * @param array $data
     */
    public function subscribeNewsletter(array $data)
    {
        \Helper::fillForm($this, 'newsletterForm', $data);

        $locators = array('newsletterFormSubmit');
        $elements = \Helper::findElements($this, $locators);
        $elements['newsletterFormSubmit']->press();
    }

    /**
     * Global method to check the count of an MultipleElement
     * @param MultipleElement $elements
     * @param int              $count
     */
    public function assertElementCount(MultipleElement $elements, $count = 0)
    {
        if ($count !== count($elements)) {
            $message = sprintf(
                'There are %d elements of type "%s" on page (should be %d)',
                count($elements),
                get_class($elements),
                $count
            );
            \Helper::throwException($message);
        }
    }

    /**
     * Global method to check the content of an Element or Page
     * @param TraversableElement $element
     * @param array              $content
     * @throws \Exception
     */
    public function assertElementContent(TraversableElement $element, $content)
    {
        $check = array();

        foreach ($content as $subCheck) {
            if(empty($subCheck['position'])) {
                $this->assertElementItems($element, $content);
                return;
            }

            $checkValues = \Helper::getValuesToCheck($element, $subCheck['position']);

            foreach ($checkValues as $key => $checkValue) {
                //Convert the contentValue to a float if checkValue is also one
                if (is_float($checkValue)) {
                    $subCheck['content'] = \Helper::toFloat($subCheck['content']);
                }

                $check[$key] = array($checkValue, $subCheck['content']);
            }
        }

        $result = \Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf(
                '"%s" not found in "%s" of "%s"! (is "%s")',
                $check[$result][1],
                $result,
                get_class($element),
                $check[$result][0]
            );
            \Helper::throwException($message);
        }
    }

    /**
     * Helper function to assert the items of an element (called from assertElementContent when content array doesn't include a position column)
     *
     * @param TraversableElement $element
     * @param $items
     */
    private function assertElementItems(TraversableElement $element, $items)
    {
        $positions = array_keys($items[0]);

        foreach($positions as $position)
        {
            $checkValues = \Helper::getValuesToCheck($element, $position);
            $values = array_column($items, $position);

            foreach($values as &$value) {
                //Convert the contentValue to a float if checkValue is also one
                if (is_float($checkValues[0][0])) {
                    $value = \Helper::toFloat($value);
                }

                $value = array_fill(0, count($checkValues[0]), $value);
            }

            $result = \Helper::compareArrays($checkValues, $values);

            if ($result === true) {
                continue;
            }

            if($result['key'] >= count($values)) {
                continue;
            }

            $message = sprintf('Item %d is different! ("%s" not found in "%s")', $result['key'] + 1, $result['value2'], $result['value']);
            \Helper::throwException($message);
        }
    }
}
