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
     * @return string
     */
    public function getImageProperty()
    {
        $locators = array('thumbnailImage');
        $elements = \Helper::findElements($this, $locators);

        return $elements['thumbnailImage']->getAttribute('src');
    }

    /**
     * @return string
     */
    public function getNameProperty()
    {
        $locators = array('thumbnailImage', 'thumbnailLink', 'name', 'detailsButton');
        $elements = \Helper::findElements($this, $locators);

        $names = array(
            'articleThumbnailImageAlt' => $elements['thumbnailImage']->getAttribute('alt'),
            'articleThumbnailLinkTitle' => $elements['thumbnailLink']->getAttribute('title'),
            'articleName' => $elements['name']->getText(),
            'articleTitle' => $elements['name']->getAttribute('title'),
            'articleDetailsButtonTitle' => $elements['detailsButton']->getAttribute('title')
        );

        return \Helper::getUnique($names);
    }

    /**
     * @return string
     */
    public function getRankingProperty()
    {
        $locators = array('stars');
        $elements = \Helper::findElements($this, $locators);

        return $elements['stars']->getAttribute('class');
    }

    /**
     * @return string
     */
    public function getLinkProperty()
    {
        $locators = array('thumbnailLink', 'name', 'detailsButton');
        $elements = \Helper::findElements($this, $locators);

        $links = array(
            'articleThumbnailLink' => $elements['thumbnailLink']->getAttribute('href'),
            'articleNameLink' => $elements['name']->getAttribute('href'),
            'articleDetailsButtonLink' => $elements['detailsButton']->getAttribute('href')
        );

        return \Helper::getUnique($links);
    }
}
