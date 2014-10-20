<?php

namespace Element\Responsive;

class BannerSlider extends \Element\Emotion\BannerSlider
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion--element.banner-slider-element');

    public $cssLocator = array(
        'slideImage' => 'div.image-slider--item',
        'slideLink' => 'div.image-slider--item > a'
    );

    /**
     * @return array
     */
    public function getImagesToCheck()
    {
        $locators = array('slideImage');
        $elements = \Helper::findElements($this, $locators, null, true);

        $images = array();

        foreach ($elements['slideImage'] as $image) {
            $images[] = array($image->getAttribute('style'));
        }

        return $images;
    }
}