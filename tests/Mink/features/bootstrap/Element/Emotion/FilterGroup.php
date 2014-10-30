<?php

namespace Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;

require_once 'tests/Mink/features/bootstrap/Element/MultipleElement.php';

class FilterGroup extends MultipleElement
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.filter_properties > div > div:not(.slideContainer)');

    public $cssLocator = array(
        'properties' => 'div + div.slideContainer'
    );

    /**
     * @param string $propertyName
     * @return bool
     */
    public function setProperty($propertyName)
    {
        $elements = \Helper::findElements($this);

        /** @var NodeElement $propertyContainer */
        $propertyContainer = $elements['properties'];

        if(!$propertyContainer->hasLink($propertyName)) {
            return false;
        }

        $propertyContainer->clickLink($propertyName);
        return true;
    }
}
