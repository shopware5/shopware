<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: BannerSlider
 * Location: Emotion element for image banner sliders
 *
 * Available retrievable properties (per slide):
 * - image (string, e.g. "beach1503f8532d4648.jpg")
 * - link (string, e.g. "/Campaign/index/emotionId/6")
 * - alt (string, e.g. "foo")
 * - title (string, e.g. "bar")
 */
class BannerSlider extends \Shopware\Tests\Mink\Element\Emotion\BannerSlider
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--banner-slider'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [
            'slide' => 'div.banner-slider--item',
            'slideImage' => 'img.banner-slider--image',
            'slideLink' => 'div.image-slider--item > a'
        ];
    }
}
