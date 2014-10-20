<?php

namespace Element\Responsive;

class Banner extends \Element\Emotion\Banner
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion--element.banner-element');

    public $cssLocator = array(
        'image' => 'div.emotion--element-banner',
        'link' => 'a.emotion--element-link',
        'mapping' => 'a.emotion--element-mapping'
    );

    /**
     * @return array
     */
    public function getImagesToCheck()
    {
        $locators = array('image');
        $elements = \Helper::findElements($this, $locators);

        return array(
            'image' => $elements['image']->getAttribute('data-image-src')
        );
    }
}