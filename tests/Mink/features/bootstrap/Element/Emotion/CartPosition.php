<?php

namespace Emotion;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class CartPosition extends \MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.table_row');

    public $cssLocator = array(
        'name' => 'div.basket_details > a.title',
        'number' => 'div.basket_details > p.ordernumber',
        'thumbnailLink' => 'a.thumb_image',
        'thumbnailImage' => 'a.thumb_image > img'
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

    /**
     * @param string $name
     * @param string $language
     */
    public function clickActionLink($name, $language)
    {
        $this->clickLink($this->namedSelectors[$name][$language]);
    }
}
