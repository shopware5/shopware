<?php

namespace Shopware\Tests\Mink\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Element: CheckoutShipping
 * Location: Billing address box on checkout confirm page
 *
 * Available retrievable properties:
 * - ???
 */
class CheckoutShipping extends Element implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.shipping--panel'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'addressData' => 'p'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'changeButton'  => ['de' => 'Ändern', 'en' => 'Change'],
            'otherButton'  => ['de' => 'Andere', 'en' => 'Others']
        ];
    }
}
