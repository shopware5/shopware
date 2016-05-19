<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use Shopware\Tests\Mink\Element\MultipleElement;

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
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'title' => '.bold'
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
