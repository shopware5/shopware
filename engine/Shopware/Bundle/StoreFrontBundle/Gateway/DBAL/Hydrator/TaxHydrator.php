<?php
declare(strict_types=1);
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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class TaxHydrator extends Hydrator
{
    /**
     * Creates a new tax struct and assigns the passed
     * data array.
     *
     * @param array $data
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Tax
     */
    public function hydrate(array $data): Struct\Tax
    {
        return new Struct\Tax(
            (int) $data['__tax_id'],
            (string) $data['__tax_description'],
            (float) $data['__tax_tax']
        );
    }

    /**
     * Creates a new tax struct and assigns the passed
     * data array.
     *
     * @param array $data
     *
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Tax
     */
    public function hydrateRule(array $data): Struct\Tax
    {
        return new Struct\Tax(
            (int) $data['__taxRule_groupID'],
            (string) $data['__taxRule_name'],
            (float) $data['__taxRule_tax']
        );
    }
}
