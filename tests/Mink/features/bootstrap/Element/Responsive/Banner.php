<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: Banner
 * Location: Emotion element for image banners
 *
 * Available retrievable properties:
 * - image (string, e.g. "deli_teaser503886c2336e3.jpg")
 * - link (string, e.g. "/Campaign/index/emotionId/6")
 * - mapping (array[])
 */
class Banner extends \Shopware\Tests\Mink\Element\Emotion\Banner
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--banner'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [
            'image' => '.banner--image',
            'link' => '.banner--link',
            'mapping' => '.banner--mapping-link'
        ];
    }
}
