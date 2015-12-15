<?php

namespace Shopware\Tests\Mink\Element;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

/**
 * Element: ManufacturerSlider
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class ManufacturerSlider extends SliderElement implements HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--manufacturer'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'slide' => '.manufacturer--item',
            'slideImage' => '.manufacturer--image',
            'slideLink' => '.manufacturer--link'
        ];
    }

    /**
     * @param NodeElement $slide
     * @return string
     */
    public function getImageProperty(NodeElement $slide)
    {
        $selector = Helper::getRequiredSelector($this, 'slideImage');

        return $slide->find('css', $selector)->getAttribute('src');
    }

    /**
     * @param NodeElement $slide
     * @return string
     */
    public function getLinkProperty(NodeElement $slide)
    {
        $selector = Helper::getRequiredSelector($this, 'slideLink');

        return $slide->find('css', $selector)->getAttribute('href');
    }

    /**
     * @param NodeElement $slide
     * @return string
     */
    public function getNameProperty(NodeElement $slide)
    {
        $selectors = Helper::getRequiredSelectors($this, ['slideImage', 'slideLink']);

        $names = [
            $slide->find('css', $selectors['slideImage'])->getAttribute('alt'),
            $slide->find('css', $selectors['slideLink'])->getAttribute('title')
        ];

        return Helper::getUnique($names);
    }
}
