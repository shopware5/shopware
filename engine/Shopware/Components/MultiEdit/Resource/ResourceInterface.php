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

namespace Shopware\Components\MultiEdit\Resource;

interface ResourceInterface
{
    /**
     * Needs to return the grammar for out lexer
     */
    public function getGrammar();

    /**
     * Returns values to be suggested for the current attribute
     *
     * @param string $attribute
     * @param string $operator
     * @param array  $queryConfig
     */
    public function getValuesFor($attribute, $operator, $queryConfig);

    /**
     * Needs to return an array of entities matching the given filter
     *
     * @param array $tokens
     * @param int   $offset
     * @param int   $limit
     */
    public function filter($tokens, $offset, $limit, $orderBy = null);

    /**
     * Returns columns to be shown in the batchProcess window
     */
    public function getBatchColumns();

    /**
     * The actual batch processing
     *
     * @param int $queueId
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
     */
    public function createQueue($filterArray, $operations, $offset, $limit, $queueId);

    /**
     * Needs to return the columns that can be shown as well as their data type.
     */
    public function getColumnConfig();

    /**
     * Saves a single modified instance of the entity
     *
     * @param array $params
     */
    public function save($params);

    /**
     * Returns a list of available backups
     *
     * @param int $offset
     * @param int $limit
     */
    public function listBackups($offset, $limit);

    /**
     * Restores a given backup
     *
     * @param int $id
     * @param int $offset
     */
    public function restoreBackup($id, $offset);

    /**
     * Delete a given backup
     *
     * @param int $id
     */
    public function deleteBackup($id);
}
