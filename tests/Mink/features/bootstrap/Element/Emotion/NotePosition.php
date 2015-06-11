<?php

namespace Element\Emotion;

class NotePosition extends CartPosition implements \HelperSelectorInterface
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.table_row');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'name' => 'a.title',
        	'supplier' => 'div.supplier',
        	'number' => 'p.ordernumber',
            'thumbnailLink' => 'a.thumb_image',
            'thumbnailImage' => 'a.thumb_image > img',
        	'description' => 'p.desc',
        	'price' => 'strong.price',
        	'detailLink' => 'a.detail'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
        	'remove'  => array('de' => 'Löschen',           'en' => 'Delete'),
        	'order'   => array('de' => 'In den Warenkorb',  'en' => 'Add to cart'),
        	'compare' => array('de' => 'Vergleichen',       'en' => 'Compare'),
        	'details' => array('de' => 'Zum Produkt',       'en' => 'View product')
        );
    }

    /**
     * @return string
     */
    public function getNameProperty()
    {
        $locators = array('name', 'thumbnailLink', 'thumbnailImage', 'detailLink');
        $elements = \Helper::findElements($this, $locators);

        $names = array(
            'articleName' => $elements['name']->getText(),
            'articleTitle' => $elements['name']->getAttribute('title'),
            'articleThumbnailLinkTitle' => $elements['thumbnailLink']->getAttribute('title'),
            'articleThumbnailImageAlt' => $elements['thumbnailImage']->getAttribute('alt'),
            'articleDetailLinkTitle' => $elements['detailLink']->getAttribute('title')
        );

        return \Helper::getUnique($names);
    }

    /**
     * @return string
     */
    public function getImageProperty()
    {
        $locators = array('thumbnailImage');
        $element = \Helper::findElements($this, $locators);

        return $element['thumbnailImage']->getAttribute('src');
    }

    /**
     * @return string
     */
    public function getLinkProperty()
    {
        $locators = array('name', 'thumbnailLink', 'detailLink');
        $elements = \Helper::findElements($this, $locators);

        $names = array(
            'articleNameLink' => $elements['name']->getAttribute('href'),
            'articleThumbnailLink' => $elements['thumbnailLink']->getAttribute('href'),
            'articleDetailLink' => $elements['detailLink']->getAttribute('href')
        );

        return \Helper::getUnique($names);
    }
}
