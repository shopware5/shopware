<?php
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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax;

interface TaxGatewayInterface
{
    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Tax requires the following data:
     * - Tax rule data
     *
     * Required conditions for the selection:
     * - The tax rule is selected according to the following criteria
     *  - Customer group
     *  - Area
     *  - Country
     *  - State
     * - The above rules are prioritized, from first to last.
     *
     * @return array<string, Tax> Indexed by 'tax_' + id
     */
    public function getRules(Group $customerGroup, ?Area $area = null, ?Country $country = null, ?State $state = null);
}
