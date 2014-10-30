<?php

namespace Element\Responsive;

use Behat\Mink\Element\NodeElement;
use Element\MultipleElement;

class FilterGroup extends \Element\Emotion\FilterGroup
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.filter--container label.filter-panel--title');

    public $cssLocator = array(
        'properties' => 'label ~ div.filter-panel--content'
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

        if(!$propertyContainer->hasField($propertyName)) {
            return false;
        }

        $propertyContainer->checkField($propertyName);
        return true;
    }
}
