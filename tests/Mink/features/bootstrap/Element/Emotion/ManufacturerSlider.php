<?php

namespace Element\Emotion;

class ManufacturerSlider extends BannerSlider implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.manufacturer-slider-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'slideImage' => 'div.supplier img',
            'slideLink' => 'div.supplier > a'
        );
    }

    /**
     * @return array
     */
    public function getNamesToCheck()
    {
        $locators = array('slideImage', 'slideLink');
        $elements = \Helper::findAllOfElements($this, $locators);

        $names = array();

        foreach ($elements['slideImage'] as $key => $image) {
            $names[] = array(
                $image->getAttribute('alt'),
                $elements['slideLink'][$key]->getAttribute('title')
            );
        }

        return $names;
    }
}