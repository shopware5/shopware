<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Element\MultipleElement;
use Shopware\Tests\Mink\Helper;

/**
 * Element: FilterGroup
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class FilterGroup extends MultipleElement
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.filter_properties > div > div:not(.slideContainer)');

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'properties' => 'div + div.slideContainer'
        ];
    }

    /**
     * @param string $propertyName
     * @return bool
     */
    public function setProperty($propertyName)
    {
        $elements = Helper::findElements($this, ['properties']);

        /** @var NodeElement $propertyContainer */
        $propertyContainer = $elements['properties'];

        if (!$propertyContainer->hasLink($propertyName)) {
            return false;
        }

        $propertyContainer->clickLink($propertyName);
        return true;
    }
}
