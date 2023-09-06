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

namespace Shopware\Components\Api;

/**
 * The BatchInterface enables batch mode for your resource.
 *
 * This will allow the user, to create/update multiple entities in one request.
 * If an entity needs an update or a creation, will be determined automatically
 */
interface BatchInterface
{
    /**
     * This methods needs to return an ID for the current resource.
     * The ID needs to be the primary ID of the resource (in most cases `id`).
     * If your resource supports other kinds of IDs, too, you should identify
     * your entity by these IDs and return the primary ID of that entity.
     *
     * @param array<string, mixed> $data
     *
     * @return int|false Return the primary ID of the entity, if it exists
     *                   Return false, if no existing entity matches $data
     */
    public function getIdByData($data);
}
