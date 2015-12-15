<?php

namespace Shopware\Tests\Mink\Element;

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
    protected $selector = ['css' => 'div.address--container .panel'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'title' => '.panel--title'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'chooseButton' => ['de' => 'Auswählen', 'en' => 'Select']
        ];
    }
}
