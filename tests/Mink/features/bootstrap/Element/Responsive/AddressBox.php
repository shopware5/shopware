<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: AddressBox
 * Location: Billing/Shipping address boxes on address selections
 *
 * Available retrievable properties:
 * -
 */
class AddressBox extends \Shopware\Tests\Mink\Element\Emotion\AddressBox
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
}
