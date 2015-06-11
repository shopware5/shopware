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
    public function getNameProperty()
    {
        $elements = \Helper::findElements($this, ['name', 'link']);

        $names = [
            $elements['name']->getText(),
            $elements['link']->getAttribute('title')
        ];

        return \Helper::getUnique($names);
    }

    /**
     * @return array
     */
    public function getImageProperty()
    {
        $elements = \Helper::findElements($this, ['image']);
        return $elements['image']->getAttribute('style');
    }

    /**
     * @return array
     */
    public function getLinkProperty()
    {
        $elements = \Helper::findElements($this, ['link']);
        return $elements['link']->getAttribute('href');
    }
}