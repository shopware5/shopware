<?php

namespace Element\Emotion;

class ManufacturerSlider extends BannerSlider
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.manufacturer-slider-element');

    public $cssLocator = array(
        'slideImage' => 'div.supplier img',
        'slideLink' => 'div.supplier > a'
    );

    /**
     * @return array
     */
    public function getNamesToCheck()
    {
        $locators = array('slideImage', 'slideLink');
        $elements = \Helper::findElements($this, $locators, null, true);

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