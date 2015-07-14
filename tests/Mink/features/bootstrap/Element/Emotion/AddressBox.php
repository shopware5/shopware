<?php

namespace Element\Emotion;

use Element\MultipleElement;

/**
 * Element: AddressBox
 * Location: Billing/Shipping address boxes on address selections
 *
 * Available retrievable properties:
 * -
 */
class AddressBox extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.select_billing');

    /**
     * Returns an array of all css selectors of the element/page
     * @return string[]
     */
    public function getCssSelectors()
    {
        return [
            'title' => '.bold'
        ];
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return [
            'chooseButton' => ['de' => 'Auswählen', 'en' => 'Select']
        ];
    }
}
