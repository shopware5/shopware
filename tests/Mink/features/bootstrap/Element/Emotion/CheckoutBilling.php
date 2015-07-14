<?php

namespace Element\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Element: CheckoutBilling
 * Location: Billing address box on checkout confirm page
 *
 * Available retrievable properties:
 * - ???
 */
class CheckoutBilling extends Element implements \HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = array('css' => 'div.invoice-address');

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
            'changeButton'  => ['de' => 'Ändern', 'en' => 'Change'],
            'otherButton'  => ['de' => 'Andere', 'en' => 'Change']
        ];
    }
}
