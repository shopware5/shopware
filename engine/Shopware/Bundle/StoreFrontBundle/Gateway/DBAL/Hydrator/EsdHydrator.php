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

class EsdHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @return Struct\Product\Esd
     */
    public function hydrate(array $data)
    {
        $esd = new Struct\Product\Esd();

        if (isset($data['__esd_id'])) {
            $esd->setId((int) $data['__esd_id']);
        }

        if (isset($data['__esd_datum'])) {
            $esd->setCreatedAt(new \DateTime($data['__esd_datum']));
        }

        if (isset($data['__esd_file'])) {
            $esd->setFile($data['__esd_file']);
        }

        if (isset($data['__esd_serials'])) {
            $esd->setHasSerials((bool) $data['__esd_serials']);
        }

        if (isset($data['__esdAttribute_id'])) {
            $this->attributeHydrator->addAttribute($esd, $data, 'esdAttribute');
        }

        return $esd;
    }
}
