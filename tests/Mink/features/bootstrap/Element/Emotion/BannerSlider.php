<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\SliderElement;

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
class BannerSlider extends SliderElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion-element > div.banner-slider-element'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [
            'slide' => 'div.slide',
            'slideImage' => 'div.slide img',
            'slideLink' => 'div.slide > a'
        ];
    }

    /**
     * Returns the slide image
     * @param NodeElement $slide
     * @return string
     */
    public function getImageProperty(NodeElement $slide)
    {
        $selector = \Helper::getRequiredSelector($this, 'slideImage');
        return $slide->find('css', $selector)->getAttribute('src');
    }

    /**
     * Returns the slide link
     * @param NodeElement $slide
     * @return string|null
     */
    public function getLinkProperty(NodeElement $slide)
    {
        $selector = \Helper::getRequiredSelector($this, 'slideLink');
        return $slide->find('css', $selector)->getAttribute('href');
    }

    /**
     * Returns the alt-attribute of the slide image
     * @param NodeElement $slide
     * @return string|null
     */
    protected function getAltProperty(NodeElement $slide)
    {
        $selector = \Helper::getRequiredSelector($this, 'slideImage');
        return $slide->find('css', $selector)->getAttribute('alt');
    }

    /**
     * Returns the title-attribute of the slide
     * @param NodeElement $slide
     * @return string|null
     */
    protected function getTitleProperty(NodeElement $slide)
    {
        $selector = \Helper::getRequiredSelector($this, 'slideImage');
        return $slide->find('css', $selector)->getAttribute('title');
    }
}