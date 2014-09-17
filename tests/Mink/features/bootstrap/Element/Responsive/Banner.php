<?php

namespace Element\Responsive;

class Banner extends \Element\Emotion\Banner
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'li.emotion--element.banner-element');

    public $cssLocator = array(
        'image' => 'div.emotion--element-banner',
        'link' => 'a.element-banner--link',
        'mapping' => 'a.element-banner--mapping'
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