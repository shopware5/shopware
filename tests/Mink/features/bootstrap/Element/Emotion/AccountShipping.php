<?php

namespace Element\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Element: AccountShipping
 * Location: Shipping address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class AccountShipping extends Element implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.shipping > div.inner_container'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return string[]
     */
    public function getCssSelectors()
    {
        return [
            'addressData' => 'p'
        ];
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array[]
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
        $elements = \Helper::findAllOfElements($this, ['addressData']);

        return $elements['addressData'];
    }
}
