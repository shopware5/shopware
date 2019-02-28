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

use Shopware\Components\MultiEdit\Resource\Product\Backup;
use Shopware\Components\MultiEdit\Resource\Product\BatchProcess;
use Shopware\Components\MultiEdit\Resource\Product\DqlHelper;
use Shopware\Components\MultiEdit\Resource\Product\Filter;
use Shopware\Components\MultiEdit\Resource\Product\Grammar;
use Shopware\Components\MultiEdit\Resource\Product\Queue;
use Shopware\Components\MultiEdit\Resource\Product\Value;
use Shopware\Models\Article\Detail;

/**
 * The main product resource will delegate the controller requests to the corresponding classes
 * and inject dependencies
 */
class Product implements ResourceInterface
{
    /**
     * @var Product\DqlHelper
     */
    private $dqlHelper;

    /**
     * @var Product\Grammar
     */
    private $grammar;

    /**
     * @var Product\Value
     */
    private $value;

    /**
     * @var Product\Filter
     */
    private $filter;

    /**
     * @var Product\BatchProcess
     */
    private $batchProcess;

    /**
     * @var Product\Queue
     */
    private $queue;

    /**
     * @var Product\Backup
     */
    private $backup;

    public function __construct(DqlHelper $dqlHelper, Grammar $grammar, Value $value, Filter $filter, BatchProcess $batchProcess, Queue $queue, Backup $backup)
    {
        $this->dqlHelper = $dqlHelper;
        $this->grammar = $grammar;
        $this->value = $value;
        $this->filter = $filter;
        $this->batchProcess = $batchProcess;
        $this->queue = $queue;
        $this->backup = $backup;
    }

    /**
     * {@inheritdoc}
     */
    public function getGrammar()
    {
        return $this->grammar->getGrammar();
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesFor($attribute, $operator, $queryConfig)
    {
        return $this->value->getValuesFor($attribute, $operator, $queryConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function filter($tokens, $offset, $limit, $orderBy = null)
    {
        return $this->filter->filter($tokens, $offset, $limit, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchColumns()
    {
        return $this->batchProcess->getEditableColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function batchProcess($queueId)
    {
        return $this->batchProcess->batchProcess($queueId);
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($filterArray, $operations, $offset, $limit, $queueId)
    {
        return $this->queue->create($filterArray, $operations, $offset, $limit, $queueId);
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnConfig()
    {
        return array_values($this->dqlHelper->getColumnsForProductListing());
    }

    /**
     * {@inheritdoc}
     */
    public function save($params)
    {
        $entityManager = $this->dqlHelper->getEntityManager();

        $primaryIdentifiers = [];

        // Group fields by entity, collect primary identifiers
        $groups = [];
        foreach ($params as $key => $info) {
            $prefix = strtolower($info['entity']);
            $groups[$prefix][] = $info;
            if ($info['field'] === 'id') {
                $primaryIdentifiers[$prefix] = $info['value'];
            }
        }

        $columnInfo = $this->dqlHelper->getColumnsForProductListing();

        // Loop through all entities, get corresponding models and set the values
        foreach ($groups as $prefix => $fields) {
            $entity = $this->dqlHelper->getEntityForPrefix($prefix);

            // All models except price
            if ($prefix !== 'price') {
                $model = $entityManager->find($entity, $primaryIdentifiers[$prefix]);
                foreach ($fields as $field) {
                    // Do not persist non-editable fields
                    $fieldInfo = $columnInfo[ucfirst($prefix) . ucfirst($field['field'])];
                    if (!$fieldInfo['editable']) {
                        continue;
                    }

                    $field['value'] = $this->dqlHelper->formatValue($prefix, $field, $field['value']);

                    $setter = 'set' . ucfirst($field['field']);
                    $model->$setter($field['value']);
                }
                $entityManager->persist($model);
            // price_model
            } else {
                $detailModel = $entityManager->find(Detail::class, $primaryIdentifiers['detail']);
                // store net prices
                $tax = $detailModel->getArticle()->getTax()->getTax() / 100 + 1;
                $priceModel = $entityManager->getRepository($entity)->findOneBy(
                    ['articleDetailsId' => $detailModel->getId(), 'customerGroupKey' => 'EK', 'from' => 1]
                );
                foreach ($fields as $field) {
                    // Do not persist non-editable fields
                    $fieldInfo = $columnInfo[ucfirst($prefix) . ucfirst($field['field'])];
                    if (!$fieldInfo['editable']) {
                        continue;
                    }

                    $price = str_replace(',', '.', $field['value']);
                    $price = ($tax != 0 ? $price / $tax : 0);
                    $setter = 'set' . ucfirst($field['field']);
                    $priceModel->$setter($price);
                }
                $entityManager->persist($priceModel);
            }
        }
        $entityManager->flush();

        return $this->dqlHelper->getProductForListing($primaryIdentifiers['detail']);
    }

    /**
     * {@inheritdoc}
     */
    public function listBackups($offset, $limit)
    {
        return $this->backup->getList($offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function restoreBackup($id, $offset)
    {
        return $this->backup->restore($id, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBackup($id)
    {
        return $this->backup->delete($id);
    }
}
