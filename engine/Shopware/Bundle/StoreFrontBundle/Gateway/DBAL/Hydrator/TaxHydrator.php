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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\StoreFrontBundle\Struct;

class TaxHydrator extends Hydrator
{
    /**
     * Creates a new tax struct and assigns the passed
     * data array.
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Tax
     */
    public function hydrate(array $data)
    {
        $tax = new Struct\Tax();

        $tax->setId((int) $data['__tax_id']);
        $tax->setName($data['__tax_description']);
        $tax->setTax((float) $data['__tax_tax']);

        return $tax;
    }

    /**
     * Creates a new tax struct and assigns the passed
     * data array.
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Tax
     */
    public function hydrateRule(array $data)
    {
        $tax = new Struct\Tax();

        $tax->setId((int) $data['__taxRule_groupID']);
        $tax->setName($data['__taxRule_name']);
        $tax->setTax((float) $data['__taxRule_tax']);

        return $tax;
    }
}
