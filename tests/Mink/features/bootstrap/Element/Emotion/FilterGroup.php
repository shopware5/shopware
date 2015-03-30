<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class FilterGroup extends MultipleElement
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.filter_properties > div > div:not(.slideContainer)');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'properties' => 'div + div.slideContainer'
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

        if(!$propertyContainer->hasLink($propertyName)) {
            return false;
        }

        $propertyContainer->clickLink($propertyName);
        return true;
    }
}
