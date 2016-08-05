<?php
namespace Shopware\Tests\Mink\Page;

use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class AddressEdit extends Account implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/address/edit';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'addressForm' => 'div.address-form--panel',
        ];
    }

    public function getNamedSelectors()
    {
        return [
            'saveAddressButton' => ['de' => 'Adresse speichern', 'en' => 'Save address']
        ];
    }
}
