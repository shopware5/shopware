<?php

namespace Element\Responsive;

class ManufacturerSlider extends \Element\Emotion\ManufacturerSlider
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion--element.manufacturer-slider-element');

    public $cssLocator = array(
        'slideImage' => 'div.manufacturer--item img',
        'slideLink' => 'div.manufacturer--item > a'
    );
}