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

namespace Shopware\Bundle\EsBackendBundle;

use Elasticsearch\Client;
use Shopware\Bundle\ESIndexingBundle\Console\EvaluationHelperInterface;
use Shopware\Bundle\ESIndexingBundle\Console\ProgressHelperInterface;
use Shopware\Bundle\ESIndexingBundle\Struct\IndexConfiguration;

class EsBackendIndexer
{
    /**
     * @deprecated Use IndexNameBuilderInterface instead
     */
    const INDEX_NAME = 'backend_index';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var EsAwareRepository[]|\IteratorAggregate
     */
    private $repositories;

    /**
     * @var EvaluationHelperInterface
     */
    private $evaluation;

    /**
     * @var string
     */
    private $esVersion;

    /**
     * @var IndexFactoryInterface
     */
    private $indexFactory;

    public function __construct(
        Client $client,
        \IteratorAggregate $repositories,
        EvaluationHelperInterface $evaluation,
        string $esVersion,
        IndexFactoryInterface $indexFactory
    ) {
        $this->client = $client;
        $this->repositories = $repositories;
        $this->evaluation = $evaluation;
        $this->esVersion = $esVersion;
        $this->indexFactory = $indexFactory;
    }

    public function index(ProgressHelperInterface $helper)
    {
        foreach ($this->repositories as $repository) {
            $index = $this->indexFactory->createIndexConfiguration($repository->getDomainName());

            $this->createIndex($index);
            $this->createMapping($repository, $index->getName());
            $this->populateEntity($index->getName(), $repository, $helper);
            $this->createAlias($index->getName(), $index->getAlias());
        }
    }

    /**
     * @deprecated since 5.6, will be removed with 5.7. Use IndexFactory service instead
     *
     * @param string $domainName
     *
     * @return string
     */
    public static function buildAlias($domainName)
    {
        trigger_error(sprintf('%s:%s is deprecated since 5.6 and will be removed with 5.7, use IndexNameBuilderInterface instead', __CLASS__, __FUNCTION__), E_USER_DEPRECATED);

        return Shopware()->Container()->get(IndexFactoryInterface::class)->createIndexConfiguration($domainName)->getAlias();
    }

    /**
     * @param string $index
     */
    public function indexEntities($index, EsAwareRepository $repository, array $ids)
    {
        $data = $repository->getList($ids);

        $remove = array_column($data, 'id');
        $remove = array_diff($ids, $remove);

        $booleanFields = [];
        foreach ($repository->getMapping()['properties'] as $key => $mapping) {
            if ($mapping['type'] === 'boolean') {
                $booleanFields[] = $key;
            }
        }

        $documents = [];
        foreach ($data as $row) {
            $documents[] = ['index' => ['_id' => $row['id']]];
            foreach ($row as $key => &$value) {
                if ($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d');
                }

                if (in_array($key, $booleanFields, true)) {
                    $value = (bool) $value;
                }

                if (is_string($value)) {
                    $value = mb_strtolower($value);
                }
            }
            unset($value);

            $documents[] = json_encode($row, JSON_PRESERVE_ZERO_FRACTION);
        }

        foreach ($remove as $id) {
            $documents[] = ['delete' => ['_id' => $id]];
        }

        $this->client->bulk(
            [
                'index' => $index,
                'type' => $repository->getDomainName(),
                'body' => $documents,
            ]
        );
    }

    /**
     * Removes unused indices
     */
    public function cleanupIndices()
    {
        $prefix = $this->indexFactory->getPrefix();
        $aliases = $this->client->indices()->getAliases();
        foreach ($aliases as $index => $indexAliases) {
            if (strpos($index, $prefix) !== 0) {
                continue;
            }

            if (empty($indexAliases['aliases'])) {
                $this->client->indices()->delete(['index' => $index]);
            }
        }
    }

    private function createIndex(IndexConfiguration $indexConfiguration): void
    {
        $indexName = $indexConfiguration->getName();
        $exist = $this->client->indices()->exists(['index' => $indexName]);
        if ($exist) {
            $this->client->indices()->delete(['index' => $indexName]);
        }

        $settings = [
            'settings' => $indexConfiguration->toArray(),
        ];

        $this->client->indices()->create([
            'index' => $indexName,
            'body' => $settings,
        ]);
    }

    private function populateEntity(string $index, EsAwareRepository $repository, ProgressHelperInterface $progress): void
    {
        $iterator = $repository->getIterator();

        $progress->start($iterator->fetchCount(), 'Start indexing: ' . $repository->getDomainName());

        while ($ids = $iterator->fetch()) {
            $this->indexEntities($index, $repository, $ids);

            $progress->advance(count($ids));
        }

        $this->client->indices()->refresh(['index' => $index]);

        $progress->finish();
        $this->evaluation->finish();
    }

    private function createAlias(string $index, string $alias): void
    {
        $exist = $this->client->indices()->existsAlias(['name' => $alias]);

        if ($exist) {
            $this->switchAlias($index, $alias);

            return;
        }

        $this->client->indices()->putAlias([
            'index' => $index,
            'name' => $alias,
        ]);
    }

    private function switchAlias(string $index, string $alias): void
    {
        $actions = [
            ['add' => ['index' => $index, 'alias' => $alias]],
        ];

        $current = $this->client->indices()->getAlias(['name' => $alias]);
        $current = array_keys($current);

        foreach ($current as $value) {
            $actions[] = ['remove' => ['index' => $value, 'alias' => $alias]];
        }
        $this->client->indices()->updateAliases(['body' => ['actions' => $actions]]);
    }

    private function createMapping(EsAwareRepository $entity, string $index): void
    {
        $mapping = [
            'properties' => [
                'id' => ['type' => 'long'],
            ],
        ];

        $own = $entity->getMapping();

        $merged = $mapping;
        if (is_array($own)) {
            $merged = array_replace_recursive($mapping, $own);
        }

        $arguments = [
            'index' => $index,
            'type' => $entity->getDomainName(),
            'body' => $merged,
        ];

        if (version_compare($this->esVersion, '7', '>=')) {
            $arguments = array_merge(
                $arguments,
                [
                    'include_type_name' => true,
                ]
            );
        }

        $this->client->indices()->putMapping(
            $arguments
        );
    }
}
