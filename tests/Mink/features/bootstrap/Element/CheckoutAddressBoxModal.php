<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Mink\Element;

use Shopware\Tests\Mink\Helper;

/**
 * Element: CheckoutAddressBox
 * Location: Checkout address boxes
 *
 * Available retrievable properties:
 * -
 */
class CheckoutAddressBoxModal extends MultipleElement
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.address--box'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'panelTitle' => '.panel--title',
            'panelBody' => '.panel--body',
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
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [
            'orChooseOtherAddress' => ['de' => 'oder andere Adresse wählen', 'en' => 'or use another address'],
            'useThisAddress' => ['de' => 'Diese Adresse verwenden'],
        ];
    }

    public function hasTitle($title)
    {
        if ($this->has('css', $this->getCssSelectors()['panelTitle'])) {
            return $this->getPanelTitleProperty() === $title;
        }

        return false;
    }

    /**
     * @param array $givenAddress
     *
     * @return bool
     */
    public function containsAdress($givenAddress)
    {
        $testAddress = [];

        if ($this->has('css', $this->getCssSelectors()['firstname']) === false) {
            return false;
        }

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
}
