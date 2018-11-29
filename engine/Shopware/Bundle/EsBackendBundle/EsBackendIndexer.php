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
use Shopware\Bundle\ESIndexingBundle\Console\ProgressHelperInterface;

class EsBackendIndexer
{
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
     * @param Client             $client
     * @param \IteratorAggregate $repositories
     */
    public function __construct(Client $client, \IteratorAggregate $repositories)
    {
        $this->client = $client;
        $this->repositories = $repositories;
    }

    /**
     * @param ProgressHelperInterface $helper
     */
    public function index(ProgressHelperInterface $helper)
    {
        foreach ($this->repositories as $repository) {
            $index = self::INDEX_NAME . '_' . $repository->getDomainName() . '_' . (new \DateTime())->format('YmdHis');

            $alias = self::buildAlias($repository->getDomainName());

            $this->createIndex($index);
            $this->createMapping($repository, $index);
            $this->populateEntity($index, $repository, $helper);
            $this->createAlias($index, $alias);
        }
    }

    /**
     * @param string $domainName
     *
     * @return string
     */
    public static function buildAlias($domainName)
    {
        return self::INDEX_NAME . '_' . $domainName;
    }

    /**
     * @param string            $index
     * @param EsAwareRepository $repository
     * @param array             $ids
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
                    $value = strtolower($value);
                }
            }

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
        $prefix = self::INDEX_NAME;
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

    /**
     * @param string $index
     */
    private function createIndex($index)
    {
        $exist = $this->client->indices()->exists(['index' => $index]);
        if ($exist) {
            $this->client->indices()->delete(['index' => $index]);
        }

        $settings = [
            'settings' => [
                'number_of_shards' => null,
                'number_of_replicas' => null,
            ],
        ];

        $this->client->indices()->create([
            'index' => $index,
            'body' => $settings,
        ]);
    }

    /**
     * @param string                  $index
     * @param EsAwareRepository       $repository
     * @param ProgressHelperInterface $progress
     */
    private function populateEntity($index, EsAwareRepository $repository, ProgressHelperInterface $progress)
    {
        $iterator = $repository->getIterator();

        $progress->start($iterator->fetchCount(), 'Start indexing: ' . $repository->getDomainName());

        while ($ids = $iterator->fetch()) {
            $this->indexEntities($index, $repository, $ids);

            $progress->advance(count($ids));
        }

        $progress->finish();
    }

    private function createAlias($index, $alias)
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

    /**
     * @param string $index
     * @param string $alias
     */
    private function switchAlias($index, $alias)
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

    /**
     * @param EsAwareRepository $entity
     * @param string            $index
     */
    private function createMapping(EsAwareRepository $entity, $index)
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

        $this->client->indices()->putMapping([
            'index' => $index,
            'type' => $entity->getDomainName(),
            'body' => $merged,
        ]);
    }
}
