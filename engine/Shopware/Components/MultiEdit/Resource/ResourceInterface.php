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

namespace Shopware\Components\MultiEdit\Resource;

use Shopware\Components\Model\ModelEntity;

interface ResourceInterface
{
    /**
     * Needs to return the grammar for out lexer
     *
     * @return array<string, array<int|string, string|array<string>>>
     */
    public function getGrammar();

    /**
     * Returns values to be suggested for the current attribute
     *
     * @param class-string<ModelEntity> $attribute
     * @param string                    $operator
     * @param array                     $queryConfig
     *
     * @return array{data: array<array{title: mixed}>, total: int}
     */
    public function getValuesFor($attribute, $operator, $queryConfig);

    /**
     * Needs to return an array of entities matching the given filter
     *
     * @param array $tokens
     * @param int   $offset
     * @param int   $limit
     *
     * @return array{data: array<array<string, mixed>>, total: int}
     */
    public function filter($tokens, $offset, $limit, $orderBy = null);

    /**
     * Returns columns to be shown in the batchProcess window
     *
     * @return array<string, array<string>>
     */
    public function getBatchColumns();

    /**
     * The actual batch processing
     *
     * @param int $queueId
     *
     * @return array{totalCount: int, remaining: int, done: bool, processed: int}
     */
    public function batchProcess($queueId);

    /**
     * Create a queue from a given filterArray
     *
     * @param array $filterArray
     * @param array $operations
     * @param int   $offset
     * @param int   $limit
     * @param int   $queueId
     *
     * @return array{totalCount: int, offset: int, queueId: int, done: bool}
     */
    public function createQueue($filterArray, $operations, $offset, $limit, $queueId);

    /**
     * Needs to return the columns that can be shown as well as their data type.
     *
     * @return array<array<string, mixed>>
     */
    public function getColumnConfig();

    /**
     * Saves a single modified instance of the entity
     *
     * @param array $params
     *
     * @return array<string, mixed>|null
     */
    public function save($params);

    /**
     * Returns a list of available backups
     *
     * @param int $offset
     * @param int $limit
     *
     * @return array{totalCount: int, data: array<array<string, mixed>>}
     */
    public function listBackups($offset, $limit);

    /**
     * Restores a given backup
     *
     * @param int $id
     * @param int $offset
     *
     * @return array{totalCount: int, offset: int, done: bool}
     */
    public function restoreBackup($id, $offset);

    /**
     * Delete a given backup
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteBackup($id);
}
