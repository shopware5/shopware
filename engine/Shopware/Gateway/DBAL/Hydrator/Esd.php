<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Gateway\DBAL\Hydrator;

use Shopware\Struct;

/**
 * @package Shopware\Gateway\DBAL\Hydrator
 */
class Esd extends Hydrator
{
    /**
     * @var Attribute
     */
    private $attributeHydrator;

    /**
     * @param Attribute $attributeHydrator
     */
    function __construct(Attribute $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @param array $data
     * @return \Shopware\Struct\Product\Esd
     */
    public function hydrate(array $data)
    {
        $esd = new \Shopware\Struct\Product\Esd();

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

        return $esd;
    }

}
