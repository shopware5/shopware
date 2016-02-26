<?php

namespace Shopware\Tests\Mink\Element\Responsive;

use Shopware\Tests\Mink\Helper;

/**
 * Element: CategoryTeaser
 * Location: Emotion element for category teasers
 *
 * Available retrievable properties:
 * - name (string, e.g. "Tees und Zubehör")
 * - image (string, e.g. "genuss_tees_banner.jpg")
 * - link (string, e.g. "/genusswelten/tees-und-zubehoer/")
 */
class CategoryTeaser extends \Shopware\Tests\Mink\Element\Emotion\CategoryTeaser
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--category-teaser'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'name' => '.category-teaser--title',
            'image' => 'style',
            'link' => '.category-teaser--link'
        ];
    }

    /**
     * Returns the category image
     * @return array
     */
    public function getImageProperty()
    {
        $elements = Helper::findElements($this, ['image']);
        return $elements['image']->getHtml();
    }
}
