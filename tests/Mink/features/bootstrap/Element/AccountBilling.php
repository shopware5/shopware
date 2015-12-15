<?php

namespace Shopware\Tests\Mink\Element;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;

/**
 * Element: AccountBilling
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class AccountBilling extends Element implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.account--billing.account--box'];

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
            'changeButton' => ['de' => 'Rechnungsadresse ändern', 'en' => 'Change billing address']
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
