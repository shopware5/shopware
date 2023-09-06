<?php

declare(strict_types=1);
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

namespace Shopware\Bundle\AttributeBundle\Service;

interface DataPersisterInterface
{
    /**
     * Persists the provided data into the provided attribute table.
     * Only attribute tables supported.
     *
     * @param string     $table
     * @param array      $data
     * @param int|string $foreignKey
     *
     * @return void
     */
    public function persist($data, $table, $foreignKey);

    /**
     * @param string $table
     * @param int    $sourceForeignKey
     * @param int    $targetForeignKey
     *
     * @return void
     */
    public function cloneAttribute($table, $sourceForeignKey, $targetForeignKey);

    /**
     * @param string $table
     * @param int    $sourceForeignKey
     * @param int    $targetForeignKey
     *
     * @return void
     */
    public function cloneAttributeTranslations($table, $sourceForeignKey, $targetForeignKey);
}
