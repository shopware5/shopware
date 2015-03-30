<?php

namespace Element\Emotion;

use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class CategoryTeaser extends MultipleElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.category-teaser-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'name' => 'div.teaser_headline > h3',
            'image' => 'div.teaser_img',
            'link' => 'div.teaser_box > a'
        );
    }

    /**
     * @return array
     */
    public function getNamesToCheck()
    {
        $locators = array('name', 'link');
        $elements = \Helper::findElements($this, $locators);

        return array(
            $elements['name']->getText(),
            $elements['link']->getAttribute('title')
        );
    }

    /**
     * @return array
     */
    public function getImagesToCheck()
    {
        $locators = array('image');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'image' => $elements['image']->getAttribute('style')
        );
    }

    /**
     * @return array
     */
    public function getLinksToCheck()
    {
        $locators = array('link');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'link' => $elements['link']->getAttribute('href')
        );
    }
}