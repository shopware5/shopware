<?php

namespace Element\Responsive;

/**
 * Element: AddressBox
 * Location: Billing/Shipping address boxes on address selections
 *
 * Available retrievable properties:
 * -
 */
class AddressBox extends \Element\Emotion\AddressBox
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.address--container .panel'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [
            'title' => '.panel--title'
        ];
    }
}
