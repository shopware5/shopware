<?php

namespace Element\Emotion;

use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class CompareColumn extends MultipleElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.compare_article');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'thumbnailImage'    => 'div.picture > a > img',
            'thumbnailLink'     => 'div.picture > a',
            'name'              => 'div.name > h3 > a',
            'detailsButton'     => 'div.name > a.button-right',
            'stars'             => 'div.votes > div.star',
            'description'       => 'div.desc',
            'price'             => 'div.price > p > strong'
        );
    }

    /** @var array $namedSelectors */
    protected $namedSelectors = array(
        'details'  => array('de' => 'Zum Produkt',   'en' => 'View product')
    );

    /**
     * @return array
     */
    public function getImagesToCheck()
    {
        $locators = array('thumbnailImage');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'articleThumbnailImageAlt' => $elements['thumbnailImage']->getAttribute('src')
        );
    }

    /**
     * @return array
     */
    public function getNamesToCheck()
    {
        $locators = array('thumbnailImage', 'thumbnailLink', 'name', 'detailsButton');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'articleThumbnailImageAlt' => $elements['thumbnailImage']->getAttribute('alt'),
            'articleThumbnailLinkTitle' => $elements['thumbnailLink']->getAttribute('title'),
            'articleName' => $elements['name']->getText(),
            'articleTitle' => $elements['name']->getAttribute('title'),
            'articleDetailsButtonTitle' => $elements['detailsButton']->getAttribute('title')
        );
    }

    /**
     * @return array
     */
    public function getRankingsToCheck()
    {
        $locators = array('stars');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'articleRanking' => $elements['stars']->getAttribute('class')
        );
    }

    /**
     * @return array
     */
    public function getDescriptionsToCheck()
    {
        $locators = array('description');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'articleDescription' => $elements['description']->getText()
        );
    }

    /**
     * @return array
     */
    public function getPricesToCheck()
    {
        $locators = array('price');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'articlePrice' => $elements['price']->getText()
        );
    }

    /**
     * @return array
     */
    public function getLinksToCheck()
    {
        $locators = array('thumbnailLink', 'name', 'detailsButton');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'articleThumbnailLink' => $elements['thumbnailLink']->getAttribute('href'),
            'articleNameLink' => $elements['name']->getAttribute('href'),
            'articleDetailsButtonLink' => $elements['detailsButton']->getAttribute('href')
        );
    }
}
