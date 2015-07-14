<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\SliderElement;

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
class ArticleSlider extends SliderElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.article-slider-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'slide' => 'div.article_box',
            'slideImage' => 'a.article-thumb-wrapper > img',
            'slideLink' => 'a.article-thumb-wrapper',
            'slideName' => 'a.title',
            'slidePrice' => 'p.price'
        );
    }

    /**
     * Returns the image source path
     * @param NodeElement $slide
     * @return string
     */
    public function getImageProperty(NodeElement $slide)
    {
        $selector = \Helper::getRequiredSelector($this, 'slideImage');

        return $slide->find('css', $selector)->getAttribute('src');
    }

    /**
     * Returns the link
     * @param NodeElement $slide
     * @return string
     */
    public function getLinkProperty(NodeElement $slide)
    {
        $selectors = \Helper::getRequiredSelectors($this, ['slideLink', 'slideName']);

        $links = [
            'slideLink' => $slide->find('css', $selectors['slideLink'])->getAttribute('href'),
            'nameLink' => $slide->find('css', $selectors['slideName'])->getAttribute('href')
        ];

        return \Helper::getUnique($links);
    }

    /**
     * Returns the name
     * @param NodeElement $slide
     * @return string
     */
    public function getNameProperty(NodeElement $slide)
    {
        $selectors = \Helper::getRequiredSelectors($this, ['slideImage', 'slideLink', 'slideName']);
        $nameElement = $slide->find('css', $selectors['slideName']);

        $names = [
            'imageTitle' => $slide->find('css', $selectors['slideImage'])->getAttribute('title'),
            'linkTitle' => $slide->find('css', $selectors['slideLink'])->getAttribute('title'),
            'name' => $nameElement->getText(),
            'nameTitle' => $nameElement->getAttribute('title'),
        ];

        return \Helper::getUnique($names);
    }

    /**
     * Returns the price
     * @param NodeElement $slide
     * @return float
     */
    public function getPriceProperty(NodeElement $slide)
    {
        $selector = \Helper::getRequiredSelector($this, 'slidePrice');
        $price = $slide->find('css', $selector)->getText();

        return \Helper::floatValue($price);
    }
}