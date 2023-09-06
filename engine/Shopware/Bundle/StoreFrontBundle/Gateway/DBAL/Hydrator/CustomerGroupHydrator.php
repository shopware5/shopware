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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;

class CustomerGroupHydrator extends Hydrator
{
    private AttributeHydrator $attributeHydrator;

    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @return Group
     */
    public function hydrate(array $data)
    {
        $customerGroup = new Group();

        $customerGroup->setId((int) $data['__customerGroup_id']);

        $customerGroup->setName($data['__customerGroup_description']);

        $customerGroup->setDisplayGrossPrices((bool) $data['__customerGroup_tax']);

        $customerGroup->setInsertedGrossPrices((bool) $data['__customerGroup_taxinput']);

        $customerGroup->setKey($data['__customerGroup_groupkey']);

        $customerGroup->setMinimumOrderValue((float) $data['__customerGroup_minimumorder']);

        $customerGroup->setPercentageDiscount((float) $data['__customerGroup_discount']);

        $customerGroup->setSurcharge((float) $data['__customerGroup_minimumordersurcharge']);

        $customerGroup->setUseDiscount((bool) $data['__customerGroup_mode']);

        if (!empty($data['__customerGroupAttribute_id'])) {
            $this->attributeHydrator->addAttribute($customerGroup, $data, 'customerGroupAttribute');
        }

        return $customerGroup;
    }
}
