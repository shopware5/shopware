<?php

namespace Element\Responsive;

use Behat\Mink\Element\NodeElement;

class FilterGroup extends \Element\Emotion\FilterGroup
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.filter--container label.filter-panel--title');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'properties' => 'label ~ div.filter-panel--content'
        );
    }

    /**
     * @param string $propertyName
     * @return bool
     */
    public function setProperty($propertyName)
    {
        $locator = array('properties');
        $elements = \Helper::findElements($this, $locator);

        /** @var NodeElement $propertyContainer */
        $propertyContainer = $elements['properties'];

        if(!$propertyContainer->hasField($propertyName)) {
            return false;
        }

        $propertyContainer->checkField($propertyName);
        return true;
    }
}
