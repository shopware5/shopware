<?php

namespace Shopware\Tests\Mink\Element\Responsive;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Helper;

/**
 * Element: ArticleSlider
 * Location: Emotion element for product sliders
 *
 * Available retrievable properties (per slide):
 * - image (string, e.g. "beach1503f8532d4648.jpg")
 * - link (string, e.g. "/Campaign/index/emotionId/6")
 * - alt (string, e.g. "foo")
 * - title (string, e.g. "bar")
 */
class ArticleSlider extends \Shopware\Tests\Mink\Element\Emotion\ArticleSlider
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--product-slider'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return array(
            'slide' => '.product--box',
            'slideImage' => '.product--image img',
            'slideLink' => '.product--image',
            'slideName' => '.product--title',
            'slidePrice' => '.product--price'
        );
    }

    /**
     * Returns the name
     * @param NodeElement $slide
     * @return string
     */
    public function getNameProperty(NodeElement $slide)
    {
        $selectors = Helper::getRequiredSelectors($this, ['slideImage', 'slideLink', 'slideName']);
        $nameElement = $slide->find('css', $selectors['slideName']);

        $names = [
            'imageAlt' => $slide->find('css', $selectors['slideImage'])->getAttribute('alt'),
            'linkTitle' => $slide->find('css', $selectors['slideLink'])->getAttribute('title'),
            'name' => trim($nameElement->getHtml()),
            'nameTitle' => $nameElement->getAttribute('title'),
        ];

        return Helper::getUnique($names);
    }
}
