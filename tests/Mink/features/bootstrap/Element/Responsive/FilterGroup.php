<?php

namespace Shopware\Tests\Mink\Element\Responsive;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Helper;

/**
 * Element: FilterGroup
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class FilterGroup extends \Shopware\Tests\Mink\Element\Emotion\FilterGroup
{
    /** @var array $selector */
    protected $selector = ['css' => 'div.filter--container label.filter-panel--title'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [
            'properties' => 'label ~ div.filter-panel--content'
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

        if(!$propertyContainer->hasField($propertyName)) {
            return false;
        }

        $propertyContainer->checkField($propertyName);
        return true;
    }
}
