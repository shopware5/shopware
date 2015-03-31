<?php

namespace Element\Emotion;

use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class BannerSlider extends MultipleElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.banner-slider-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'slideImage' => 'div.slide img',
            'slideLink' => 'div.slide > a'
        );
    }

    /**
     * @return array
     */
    public function getImagesToCheck()
    {
        $locators = array('slideImage');
        $elements = \Helper::findAllOfElements($this, $locators);

        $images = array();

        foreach ($elements['slideImage'] as $image) {
            $images[] = array($image->getAttribute('src'));
        }

        return $images;
    }

    /**
     * @return array
     */
    public function getLinksToCheck()
    {
        $locators = array('slideLink');
        $elements = \Helper::findAllOfElements($this, $locators);

        $links = array();

        foreach ($elements['slideLink'] as $link) {
            $links[] = array($link->getAttribute('href'));
        }

        return $links;
    }
}