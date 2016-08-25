<?php

namespace Shopware\Tests\Mink\Element;

use Shopware\Tests\Mink\Helper;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Element: AddressManagementAddressBox
 * Location: Account address boxes
 *
 * Available retrievable properties:
 * -
 */
class AddressManagementAddressBox extends MultipleElement
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.address--box'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'title' => '.panel--title',
            'company' => '.address--company',
            'address' => '.address--address',
            'salutation' => '.address--salutation',
            'customerTitle' => '.address--title',
            'firstname' => '.address--firstname',
            'lastname' => '.address--lastname',
            'street' => '.address--street',
            'addLineOne' => '.address--additional-one',
            'addLineTwo' => '.address--additional-two',
            'zipcode' => '.address--zipcode',
            'city' => '.address--city',
            'stateName' => '.address--statename',
            'countryName' => '.address--countryname',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'changeLink' => ['de' => 'Bearbeiten', 'en' => 'Edit'],
            'deleteLink' => ['de' => 'Löschen', 'en' => 'Delete'],
            'setDefaultShippingButton' => ['de' => 'Als Standard-Lieferadresse verwenden', 'en' => 'Set as default shipping address'],
            'setDefaultBillingButton' => ['de' => 'Als Standard-Rechnungsadresse verwenden', 'en' => 'Set as default billing address'],
        ];
    }

    /**
     * @param $givenAddress
     * @return bool
     */
    public function containsAdress($givenAddress)
    {
        $testAddress = [];
        if (count($givenAddress) === 5) {
            $testAddress[] = $this->getCompanyOrNull();
        }

        $testAddress[] = Helper::getElementProperty($this, 'firstname') . ' ' . Helper::getElementProperty($this, 'lastname');
        $testAddress[] = Helper::getElementProperty($this, 'street');
        $testAddress[] = Helper::getElementProperty($this, 'zipcode') . ' ' . Helper::getElementProperty($this, 'city');
        $testAddress[] = Helper::getElementProperty($this, 'countryName');
        
        return Helper::compareArrays($givenAddress, $testAddress) === true;
    }

    private function getCompanyOrNull()
    {
        if ($this->has('css', $this->getCssSelectors()['company'])) {
            return $this->getCompanyProperty();
        }

        return null;
    }

    public function hasTitle($title)
    {
        if ($this->has('css', $this->getCssSelectors()['title'])) {
            return $this->getTitleProperty() === $title;
        }

        return false;
    }
}
