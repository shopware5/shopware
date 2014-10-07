<?php

namespace Page\Emotion;

use Element\MultipleElement;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Element\TraversableElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page, Behat\Mink\Exception\ResponseTextException,
    Behat\Behat\Context\Step;

class Homepage extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/';

    public $cssLocator = array(
        'contentBlock' => 'div#content > div.inner',
        'searchForm' => 'div#searchcontainer form',
        'newsletterForm' => 'div.footer_column.col4 > form',
        'newsletterFormSubmit' => 'div.footer_column.col4 > form input[type="submit"]',
        'controller' => array(
            'account' => 'body.ctl_account',
            'checkout' => 'body.ctl_checkout',
            'newsletter' => 'body.ctl_newsletter'
        )
    );

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'searchButton'            => array('de' => 'Suchen',  'en' => 'Search')
    );

    protected $srcAttribute = 'src';

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
        \Helper::fillForm($this, 'searchForm', $data);
        \Helper::pressNamedButton($this, 'searchButton');
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
        \Helper::fillForm($this, 'searchForm', $data);
        $this->getSession()->wait(5000, "$('ul.searchresult').children().length > 0");
    }

    /**
     * Compares the comparison list with the given list of articles
     * @param  array                                       $articles
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function checkComparison($articles)
    {
        //TODO: REFAKTORIEREN!!! AUCH FÜR RESPONSIVE!!!


        $result = \Helper::countElements($this, 'div.compare_article', count($articles));

        if ($result !== true) {
            $message = sprintf('There are %d articles in the comparison (should be %d)', $result, count($articles));
            \Helper::throwException(array($message));
        }

        $articlesInComparison = $this->findAll('css', 'div.compare_article');

        foreach ($articles as $articleKey => $article) {
            foreach ($articlesInComparison as $articleInComparisonKey => $articleInComparison) {

                $locator = sprintf('div.compare_article:nth-of-type(%d) ', $articleInComparisonKey + 2);

                $elements = array(
                    'a-picture' => $this->find('css', $locator . 'div.picture a'),
                    'img' => $this->find('css', $locator . 'div.picture img'),
                    'h3-a-name' => $this->find('css', $locator . 'div.name h3 a'),
                    'a-name' => $this->find('css', $locator . 'div.name a.button-right'),
                    'div-votes' => $this->find('css', $locator . 'div.votes div.star'),
                    'p-desc' => $this->find('css', $locator . 'div.desc'),
                    'strong-price' => $this->find('css', $locator . 'div.price strong')
                );

                $check = array();

                if (!empty($article['image'])) {
                    $check[] = array($elements['img']->getAttribute('src'), $article['image']);
                }

                if (!empty($article['name'])) {
                    $check[] = array($elements['a-picture']->getAttribute('title'), $article['name']);
                    $check[] = array($elements['img']->getAttribute('alt'), $article['name']);
                    $check[] = array($elements['h3-a-name']->getAttribute('title'), $article['name']);
                    $check[] = array($elements['h3-a-name']->getText(), $article['name']);
                    $check[] = array($elements['a-name']->getAttribute('title'), $article['name']);
                }

                if (!empty($article['ranking'])) {
                    $check[] = array($elements['div-votes']->getAttribute('class'), $article['ranking']);
                }

                if (!empty($article['text'])) {
                    $check[] = array($elements['p-desc']->getText(), $article['text']);
                }

                if (!empty($article['price'])) {
                    $check[] = \Helper::toFloat(array($elements['strong-price']->getText(), $article['price']));
                }

                if (!empty($article['link'])) {
                    $check[] = array($elements['a-picture']->getAttribute('href'), $article['link']);
                    $check[] = array($elements['h3-a-name']->getAttribute('href'), $article['link']);
                    $check[] = array($elements['a-name']->getAttribute('href'), $article['link']);
                }

                $result = \Helper::checkArray($check);

                if ($result === true) {
                    unset($articlesInComparison[$articleInComparisonKey]);
                    break;
                }

                if ($articleInComparison == end($articlesInComparison)) {
                    $message = sprintf(
                        'The article on position %d was not found!',
                        $articleKey + 1
                    );
                    \Helper::throwException($message);
                }
            }
        }
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

    /**
     * @param string             $formLocatorName
     * @param TraversableElement $element
     * @param array              $values
     */
    public function submitForm($formLocatorName, TraversableElement $element, $values)
    {
        $locators = array(
            'form' => $element->cssLocator[$formLocatorName],
            'formSubmitButton' => $element->cssLocator[$formLocatorName] . ' *[type="submit"]'
        );
        $elements = \Helper::findElements($element, $locators, $locators, false, false);

        if(empty($elements['form'])) {
            $message = sprintf('The form "%s" was not found!', $formLocatorName);
            \Helper::throwException($message);
        }

        $form = $elements['form'];
        $formSubmit = $elements['formSubmitButton'];

        if(empty($formSubmit)) {
            $locators = array(
                'submitButton' => '*[type="submit"]'
            );
            $elements = \Helper::findElements($element, $locators, $locators, true, false);

            $formId = $form->getAttribute('id');

            foreach($elements['submitButton'] as $submit) {
                if($submit->getAttribute('form') === $formId) {
                    $formSubmit = $submit;
                    break;
                }
            }
        }

        if(empty($formSubmit)) {
            $message = sprintf('The form "%s" has no submit button!', $formLocatorName);
            \Helper::throwException($message);
        }

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

                    $message = sprintf('The form "%s" has no field "%s"!', $formLocatorName, $fieldName);
                    \Helper::throwException($message);
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

        $formSubmit->press();
    }

    /**
     * Returns the called Shopware controller
     * @return string
     */
    public function getController()
    {
        $elements = \Helper::findElements($this, $this->cssLocator['controller'], $this->cssLocator['controller'], false, false);
        $elements = array_filter($elements);
        return key($elements);
    }
}
