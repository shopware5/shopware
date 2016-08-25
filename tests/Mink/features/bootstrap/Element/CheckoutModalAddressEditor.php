<?php

namespace Shopware\Tests\Mink\Element;

use Shopware\Tests\Mink\Helper;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Element: CheckoutModalAddressEditor
 * Location: Checkout modal address editor
 *
 * Available retrievable properties:
 * -
 */
class CheckoutModalAddressEditor extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.address-manager--editor'];

    public function getCssSelectors()
    {
        return [
            'addressForm' => 'form[name="frmAddresses"]',
            'actions' => '.address--form-actions',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'saveAsNewAddress' => ['de' => 'Als neue Adresse speichern'],
        ];
    }
}
