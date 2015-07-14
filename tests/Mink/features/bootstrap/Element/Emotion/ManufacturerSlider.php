<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\SliderElement;

/**
 * Element: ManufacturerSlider
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class ManufacturerSlider extends SliderElement implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.emotion-element > div.manufacturer-slider-element');

    /**
     * Returns an array of all css selectors of the element/page
     * @return string[]
     */
    public function getCssSelectors()
    {
        return [
            'slide' => 'div.supplier',
            'slideImage' => 'div img',
            'slideLink' => 'div > a'
        ];
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
     * @return string
     */
    public function getNameProperty(NodeElement $slide)
    {
        $selectors = \Helper::getRequiredSelectors($this, ['slideImage', 'slideLink']);

        $names = [
            $slide->find('css', $selectors['slideImage'])->getAttribute('alt'),
            $slide->find('css', $selectors['slideLink'])->getAttribute('title')
        ];

        return \Helper::getUnique($names);
    }
}