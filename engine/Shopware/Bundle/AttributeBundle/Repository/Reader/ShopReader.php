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

namespace Shopware\Bundle\AttributeBundle\Repository\Reader;

use Shopware\Models\Shop\Shop;

class ShopReader extends GenericReader
{
    protected function createListQuery()
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->select([
            'entity.id',
            'entity.name',
            'entity.host',
            'entity.basePath',
            'entity.baseUrl',
            'entity.default',
            'entity.active',
            'c.name as category',
            'main.name as mainShopName',
        ]);
        $query->from(Shop::class, 'entity', $this->getIdentifierField());
        $query->leftJoin('entity.category', 'c');
        $query->leftJoin('entity.main', 'main');

        return $query;
    }
}
