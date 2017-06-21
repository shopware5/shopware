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

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Helper;

/**
 * Element: AccountPayment
 * Location: Payment box on account dashboard
 *
 * Available retrievable properties:
 * -
 */
class AccountPayment extends Element implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'div.account--payment.account--box'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'currentMethod' => 'p',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [
            'changeButton' => ['de' => 'Zahlungsart ändern', 'en' => 'Change payment method'],
        ];
    }

    /**
     * Returns the name of the current payment method
     *
     * @return string
     */
    public function getPaymentMethodProperty()
    {
        $element = Helper::findElements($this, ['currentMethod']);

        $currentMethod = $element['currentMethod']->getText();
        $currentMethod = str_word_count($currentMethod, 1);

        return current($currentMethod);
    }
}
