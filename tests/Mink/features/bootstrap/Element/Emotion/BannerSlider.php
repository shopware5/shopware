<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;
use Element\SliderElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';
require_once 'tests/Mink/features/bootstrap/Element/SliderElement.php';

class BannerSlider extends SliderElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.banner-slider-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'slide' => 'div.slide',
            'slideImage' => 'div.slide img',
            'slideLink' => 'div.slide > a'
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
        $selector = \Helper::getRequiredSelector($this, 'slideLink');

        return $slide->find('css', $selector)->getAttribute('href');
    }

    /**
     * @param NodeElement $slide
     * @return string|null
     */
    protected function getAltProperty(NodeElement $slide)
    {
        $selector = \Helper::getRequiredSelector($this, 'slideImage');

        return $slide->find('css', $selector)->getAttribute('alt');
    }

    /**
     * @param NodeElement $slide
     * @return string|null
     */
    protected function getTitleProperty(NodeElement $slide)
    {
        $selector = \Helper::getRequiredSelector($this, 'slideImage');

        return $slide->find('css', $selector)->getAttribute('title');
    }
}