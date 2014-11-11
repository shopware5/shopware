<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class CartPosition extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.table_row');

    public $cssLocator = array(
        'name' => 'div.basket_details > a.title',
        'number' => 'div.basket_details > p.ordernumber',
        'thumbnailLink' => 'a.thumb_image',
        'thumbnailImage' => 'a.thumb_image > img',
        'quantity' => 'div > form > div:nth-of-type(3) > select > option',
        'itemPrice' => 'div > form > div:nth-of-type(4)',
        'sum' => 'div > form > div:nth-of-type(5)'
    );

    /** @var array $namedSelectors */
    protected $namedSelectors = array(
        'remove'  => array('de' => 'Löschen',   'en' => 'Delete')
    );

    /**
     * @return array
     */
    public function getNamesToCheck()
    {
        $locators = array('name', 'thumbnailLink', 'thumbnailImage');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'articleName' => $elements['name']->getText(),
            'articleTitle' => $elements['name']->getAttribute('title'),
            'articleThumbnailLinkTitle' => $elements['thumbnailLink']->getAttribute('title'),
            'articleThumbnailImageAlt' => $elements['thumbnailImage']->getAttribute('alt'),
        );
    }

    /**
     * @return array
     */
    public function getNumbersToCheck()
    {
        $locators = array('number');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'articleNumber' => $elements['number']->getText()
        );
    }

    public function getQuantitysTocheck()
    {
        $locators = array('quantity');
        $elements = \Helper::findElements($this, $locators, null, true);

        $quantity = 0;

        /** @var NodeElement $option */
        foreach($elements['quantity'] as $option)
        {
            if($option->hasAttribute('selected')) {
                $quantity = $option->getText();
                break;
            }
        }

        return array(
            'quantity' => $quantity
        );
    }

    public function getItemPricesTocheck()
    {
        $locators = array('itemPrice');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'itemPrice' => $elements['itemPrice']->getText()
        );
    }

    public function getSumsTocheck()
    {
        $locators = array('sum');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'totalPrice' => $elements['sum']->getText()
        );
    }

    /**
     * @param string $name
     * @param string $language
     */
    public function clickActionLink($name, $language)
    {
        $this->clickLink($this->namedSelectors[$name][$language]);
    }
}
