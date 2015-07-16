<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: ManufacturerSlider
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class ManufacturerSlider extends \Shopware\Tests\Mink\Element\Emotion\ManufacturerSlider
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion--manufacturer'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return string[]
     */
    public function getCssSelectors()
    {
        return [
            'slide' => '.manufacturer--item',
            'slideImage' => '.manufacturer--image',
            'slideLink' => '.manufacturer--link'
        ];
    }
}
