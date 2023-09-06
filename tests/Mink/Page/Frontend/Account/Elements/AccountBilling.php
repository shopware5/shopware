<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Mink\Page\Frontend\Account\Elements;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\HelperSelectorInterface;

/**
 * Element: AccountBilling
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class AccountBilling extends Element implements HelperSelectorInterface
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.account--billing.account--box'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'addressData' => 'p',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [
            'otherButton' => ['de' => 'Andere wählen', 'en' => 'Select other'],
            'changeButton' => ['de' => 'Rechnungsadresse ändern', 'en' => 'Change billing address'],
        ];
    }

    /**
     * Returns the address elements
     *
     * @return Element[]
     */
    public function getAddressProperty()
    {
        $elements = Helper::findAllOfElements($this, ['addressData']);

        return $elements['addressData'];
    }
}
