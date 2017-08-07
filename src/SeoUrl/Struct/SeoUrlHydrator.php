<?php declare(strict_types=1);
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

namespace Shopware\SeoUrl\Struct;

use Shopware\Framework\Struct\Hydrator;

class SeoUrlHydrator extends Hydrator
{
    public function hydrate(array $row): SeoUrl
    {
        return new SeoUrl(
            (int) $row['__seoUrl_id'],
            (int) $row['__seoUrl_shop_id'],
            $row['__seoUrl_name'],
            (int) $row['__seoUrl_foreign_key'],
            $row['__seoUrl_path_info'],
            $row['__seoUrl_url'],
            new \DateTime($row['__seoUrl_created_at']),
            (bool) $row['__seoUrl_is_canonical']
        );
    }
}
