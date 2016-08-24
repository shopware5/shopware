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

namespace Shopware\Components\MultiEdit\Resource\Product;

/**
 * The batch process resource handles the batch processes for updating articles
 *
 * Class BatchProcess
 */
class BatchProcess
{
    /**
     * Reference to an instance of the DqlHelper
     *
     * @var DqlHelper
     */
    protected $dqlHelper;

    /**
     * Reference to an instance of the filterResource
     *
     * @var Filter
     */
    protected $filterResource;

    /**
     * Reference to the config instance
     *
     * @var \Shopware_Components_Config
     */
    protected $configResource;

    /**
     * @return DqlHelper
     */
    public function getDqlHelper()
    {
        return $this->dqlHelper;
    }

    /**
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filterResource;
    }

    /***
     * @return Queue
     */
    public function getQueue()
    {
        return $this->queueResource;
    }

    /**
     * @return \Shopware_Components_Config
     */
    public function getConfig()
    {
        return $this->configResource;
    }

    /**
     * @param $dqlHelper DqlHelper
     * @param $filter Filter
     * @param $queue Queue
     * @param $config \Shopware_Components_Config
     */
    public function __construct($dqlHelper, $filter, $queue, $config)
    {
        $this->dqlHelper = $dqlHelper;
        $this->filterResource = $filter;
        $this->queueResource = $queue;
        $this->configResource = $config;
    }

    /**
     * Generates a list of editable columns and the known operators
     *
     * @return array
     * @throws \RuntimeException When the column was not defined
     */
    public function getEditableColumns()
    {
        $columns = $this->getDqlHelper()->getColumnsForProductListing();

        foreach ($columns as $key => $config) {
            $attribute = $config['entity'] . '.' . $config['field'];

            if (!$config['editable']) {
                continue;
            }

            // Do not allow overriding
            if (!isset($columns[$attribute])) {
                $columns[$attribute] = $config;
            }
            unset($columns[$key]);
        }

        $attributes = array();
        foreach ($columns as $attribute => $config) {
            $type = $config['type'];

            // Disallow any ID field to be set.
            if (!$config['editable']) {
                continue;
            }

            switch ($type) {
                case 'integer':
                case 'bigint':
                case 'decimal':
                case 'float':
                    $attributes[$attribute] = array('set', 'add', 'subtract', 'devide', 'multiply');
                    break;
                case 'text':
                case 'string':
                    $attributes[$attribute] = array('set', 'prepend', 'append', 'removeString');
                    break;
                case 'boolean':
                    $attributes[$attribute] = array('set');
                    break;
                case 'date':
                    $attributes[$attribute] = array('set');
                    break;
                case 'datetime':
                    $attributes[$attribute] = array('set');
                    break;
                default:
                    throw new \RuntimeException("Column with type {$type} was not configured, yet");

            }
            // Technically we're able to process DQL here. This should not be enabled by default and is quite limited
            if (false) {
                $attributes[$attribute][] = 'dql';
            }
        }

        return $attributes;
    }

    /**
     * Will apply a operation list to a given $detailIds. As the operations are grouped by entity, we just need one
     * update query and are able to apply modifications within one query
     *
     * @param $operations
     * @param $detailIds
     */
    public function applyOperations($operations, $detailIds)
    {
        // Get prefix from first entry
        list($prefix, $column) = explode('.', $operations[0]['column']);

        $entity = $this->getDqlHelper()->getEntityForPrefix($prefix);

        $builder = $this->getDqlHelper()->getEntityManager()->createQueryBuilder()
            ->update($entity, $prefix);

        $columnInfo = $this->getDqlHelper()->getColumnsForProductListing();

        $ids = $this->getDqlHelper()->getIdForForeignEntity($prefix, $detailIds);
        $builder->where($builder->expr()->in($prefix.'.id', $ids));

        foreach ($operations as $operation) {
            list($prefix, $column) = explode('.', $operation['column']);

            $type = $columnInfo[ucfirst($prefix).ucfirst($column)]['type'];
            if ($operation['value'] &&  $type == 'decimal' || $type == 'integer' || $type == 'float') {
                $operation['value'] = str_replace(',', '.', $operation['value']);
            }

            // In set mode: If column is nullable and value is "" - set it to null
            if ($operation['operator'] == 'set' && $columnInfo[ucfirst($prefix).ucfirst($column)]['nullable'] && $operation['value'] == '') {
                $operationValue = 'NULL';
            } else {
                $operationValue = $builder->expr()->literal($operation['value']);
            }


            switch (strtolower($operation['operator'])) {
                case 'removestring':
                    $builder->set("{$prefix}.$column", new \Doctrine\ORM\Query\Expr\Literal("REPLACE({$prefix}.{$column}, '{$operation['value']}', '')"));
                    break;
                case 'devide':
                    $builder->set("{$prefix}.$column", $builder->expr()->quot("{$prefix}.$column", $operationValue));
                    break;
                case 'multiply':
                    $builder->set("{$prefix}.$column", $builder->expr()->prod("{$prefix}.$column", $operationValue));
                    break;
                case 'add':
                    $builder->set("{$prefix}.$column", $builder->expr()->sum("{$prefix}.$column", $operationValue));
                    break;
                case 'subtract':
                    $builder->set("{$prefix}.$column", $builder->expr()->diff("{$prefix}.$column", $operationValue));
                    break;
                case 'append':
                    $builder->set("{$prefix}.$column", $builder->expr()->concat("{$prefix}.$column", $operationValue));
                    break;
                case 'prepend':
                    $builder->set("{$prefix}.$column", $builder->expr()->concat($operationValue, "{$prefix}.$column"));
                    break;
                case 'dql':
                    // This is quite limited, as many sql features are note supported. Also the update-statements
                    // are limited to the current entity, so you will not be able to set an article's name
                    // to its details number because the detail cannot be joined here.
                    $builder->set("{$prefix}.$column", new \Doctrine\ORM\Query\Expr\Literal($operation['value']));
                    break;
                case 'set':
                default:
                    $builder->set("{$prefix}.$column", $operationValue);
                    break;
            }
        }
        $builder->getQuery()->execute();
    }

    /**
     * Updates a sine article details within batch mode
     *
     * @param $detailIds
     * @param $nestedOperations
     * @throws Exception
     */
    public function updateDetails($detailIds, $nestedOperations)
    {
        $entityManager = $this->getDqlHelper()->getEntityManager();

        $nestedOperations = $this->getDqlHelper()->groupOperations($nestedOperations);

        foreach ($nestedOperations as $prefix => $operations) {
            if (empty($prefix)) {
                continue;
            }

            $this->applyOperations($operations, $detailIds);
        }

        $entityManager->flush();

        // As of Shopware 4.1.3 clearing the cache via event is possible. As this is quite slow, however,
        // this function is optional and disabled by default.
        $clearCache = $this->getConfig()->getByNamespace('SwagMultiEdit', 'clearCache', false);
        if (!$clearCache) {
            return;
        }

        // Notify event - you might want register for this in order to clear the cache?
        foreach ($this->getDqlHelper()->getIdForForeignEntity('article', $detailIds) as $articleId) {
            $this->getDqlHelper()->getEventManager()->notify(
                'Shopware_Plugins_HttpCache_InvalidateCacheId',
                array('subject' => $this, 'cacheId' => 'a'.$articleId)
            );
        }
    }

    /**
     * Batch processes a given queue
     *
     * @param $queueId
     * @return Array
     * @throws \RuntimeException
     */
    public function batchProcess($queueId)
    {
        $entityManager = $this->getDqlHelper()->getEntityManager();
        $connection = $entityManager->getConnection();

        /** @var \Shopware\Models\MultiEdit\Queue $queue */
        $queue = $entityManager->find('\Shopware\Models\MultiEdit\Queue', $queueId);

        if (!$queue) {
            throw new \RuntimeException("Queue with ID {$queueId} not found");
        }

        $operations = json_decode($queue->getOperations(), true);

        // Wrap all this into a transaction in order to speed up the progress
        // and to be able to roll it back at some point
        $connection->beginTransaction();

        try {
            $details = $this->getQueue()->pop($queueId, $this->getConfig()->getByNamespace('SwagMultiEdit', 'batchItemsPerRequest', 512));

            if (!empty($details)) {
                $this->updateDetails($details, $operations);
                $connection->commit();
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \RuntimeException("Error updating details: {$e->getMessage()}", 0, $e);
        }
        $remaining = $queue->getArticleDetails()->count();

        if ($remaining == 0) {
            $entityManager->remove($queue);
            $entityManager->flush();
        }

        return array(
            'totalCount' => $queue->getInitialSize(),
            'remaining' => $remaining,
            'done' => $remaining == 0,
            'processed' => $queue->getInitialSize() - $remaining
        );
    }
}
