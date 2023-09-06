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

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Link;

class LinkHydrator extends Hydrator
{
    private AttributeHydrator $attributeHydrator;

    public function __construct(AttributeHydrator $attributeHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @return Link
     */
    public function hydrate(array $data)
    {
        $link = new Link();
        $link->setId((int) $data['__link_id']);
        $link->setDescription($data['__link_description']);
        $link->setLink($data['__link_link']);
        $link->setTarget($data['__link_target']);

        if (!empty($data['__linkAttribute_id'])) {
            $this->attributeHydrator->addAttribute($link, $data, 'linkAttribute');
        }

        return $link;
    }
}
