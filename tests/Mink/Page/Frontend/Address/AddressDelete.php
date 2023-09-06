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

namespace Shopware\Tests\Mink\Page\Frontend\Address;

use Shopware\Tests\Mink\Page\Frontend\Account\Account;

class AddressDelete extends Account
{
    /**
     * @var string
     */
    protected $path = '/address/delete';

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors(): array
    {
        return [
            'panelTitle' => 'h1.panel--title',
        ];
    }

    public function getNamedSelectors(): array
    {
        return [
            'confirmDeleteButton' => ['de' => 'Bestätigen', 'en' => 'Confirm'],
        ];
    }
}
