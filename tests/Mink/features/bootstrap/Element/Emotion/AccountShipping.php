<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;

/**
 * Element: AccountShipping
 * Location: Shipping address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class AccountShipping extends Element implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.shipping > div.inner_container'];

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
            'otherButton' => ['de' => 'Andere wählen', 'en' => 'Select other'],
            'changeButton' => ['de' => 'Lieferadresse ändern', 'en' => 'Change shipping address']
        ];
    }

    /**
     * Returns the address elements
     * @return Element[]
     */
    public function getAddressProperty()
    {
        $elements = Helper::findAllOfElements($this, ['addressData']);

        return $elements['addressData'];
    }
}
