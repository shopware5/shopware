<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\SliderElement;

require_once 'tests/Mink/features/bootstrap/Element/Emotion/BannerSlider.php';

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
     * @param NodeElement $slide
     * @return string
     */
    public function getImageProperty(NodeElement $slide)
    {
        $selector = \Helper::getRequiredSelector($this, 'slideImage');

        return $slide->find('css', $selector)->getAttribute('src');
    }

    /**
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