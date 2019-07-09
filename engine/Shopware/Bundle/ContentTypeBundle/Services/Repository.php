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

namespace Shopware\Bundle\ContentTypeBundle\Services;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\ContentTypeBundle\Field\CheckboxField;
use Shopware\Bundle\ContentTypeBundle\Structs\Criteria;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;
use Shopware\Bundle\ContentTypeBundle\Structs\SearchResult;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Api\Exception\CustomValidationException;

class Repository implements RepositoryInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \Shopware_Components_Translation
     */
    private $translation;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var TypeFieldResolverInterface
     */
    private $fieldResolver;

    public function __construct(
        Connection $connection,
        Type $type,
        \Shopware_Components_Translation $translation,
        ContextServiceInterface $contextService,
        TypeFieldResolverInterface $fieldResolver
    ) {
        $this->connection = $connection;
        $this->translation = $translation;
        $this->contextService = $contextService;
        $this->type = $type;
        $this->fieldResolver = $fieldResolver;
    }

    public function findAll(Criteria $criteria): SearchResult
    {
        $query = $this->buildQuery($criteria->offset, $criteria->limit, $criteria->sort, $criteria->filter);

        $items = $query->execute()->fetchAll();

        $result = new SearchResult();
        $result->type = $this->type;

        if ($criteria->calculateTotal) {
            $result->total = (int) $this->connection->fetchColumn('SELECT FOUND_ROWS()');
        }

        if ($criteria->loadTranslations) {
            $items = $this->translateItems($query->execute()->fetchAll());
        }

        if ($criteria->loadAssociations) {
            $items = $this->fieldResolver->resolveFields($this->type, $items);
        }

        $result->items = $items;

        return $result;
    }

    public function save(array $data, ?int $id = null): int
    {
        $this->validateFields($data);
        $data = $this->filterFields($data);
        $data = $this->quoteFields($data);

        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($id) {
            $this->connection->update($this->type->getTableName(), $data, ['id' => $id]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->connection->insert($this->type->getTableName(), $data);
            $id = (int) $this->connection->lastInsertId();
        }

        return $id;
    }

    public function delete(int $id): bool
    {
        $this->connection->delete($this->type->getTableName(), ['id' => $id]);

        return true;
    }

    protected function getQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()->from($this->type->getTableName(), 'entity')->select('SQL_CALC_FOUND_ROWS entity.id, entity.*');
    }

    protected function buildQuery($offset = 0, $limit = 10, $sort = [], $filters = []): QueryBuilder
    {
        $query = $this->getQuery();

        if ($limit) {
            $query->setMaxResults($limit);
        }

        if ($offset) {
            $query->setFirstResult($offset);
        }

        if (!empty($sort)) {
            foreach ($sort as $item) {
                if ($item['property'] === 'RANDOM') {
                    $query->addOrderBy('RAND()');
                    continue;
                }

                $query->addOrderBy($item['property'], $item['direction']);
            }
        }

        $idSearch = null;
        if (!empty($filters)) {
            foreach ($filters as $item) {
                if ($item['property'] === 'search') {
                    $query->setParameter('search', '%' . $item['value'] . '%');

                    foreach ($this->type->getFields() as $field) {
                        if (!$field->isSearchAble()) {
                            continue;
                        }

                        $query->orWhere(sprintf('`%s` LIKE :search', $field->getName()));
                    }
                } else {
                    $where = $item['value'];
                    $expression = null;

                    if ($item['property'] === 'id') {
                        $idSearch = is_array($where) ? $where : [$where];
                    }

                    if (isset($item['expression'])) {
                        $expression = $item['expression'];
                    }

                    if ($expression === null) {
                        switch (true) {
                            case is_string($where):
                                $expression = 'LIKE';
                                break;
                            case is_array($where):
                                $expression = 'IN';
                                break;
                            case $where === null:
                                $expression = 'IS NULL';
                                $where = null;
                                break;
                            default:
                                $expression = '=';
                                break;
                        }
                    }

                    if ($where === null) {
                        $expression = 'IS NULL';
                    }

                    $cond = '`' . $item['property'] . '` ' . $expression;

                    if ($where) {
                        if ($expression === 'IN') {
                            $cond = $query->expr()->in('`' . $item['property'] . '`', $where);
                        } else {
                            $cond .= ' ' . $query->createNamedParameter($where);
                        }
                    }

                    if (isset($item['operator'])) {
                        $query->orWhere($cond);
                    } else {
                        $query->andWhere($cond);
                    }
                }
            }
        }

        // Sorting result by given ids
        if ($idSearch && count($filters) === 1) {
            $idSearch = array_map('intval', $idSearch);

            $orderBy = sprintf('FIELD(id, %s)', implode(',', $idSearch));

            $query->addOrderBy($orderBy);
        }

        return $query;
    }

    protected function translateItems(array $items): array
    {
        try {
            $context = $this->contextService->getShopContext()->getShop();
        } catch (\Exception $e) {
            return $items;
        }

        $ids = array_column($items, 'id');
        $translations = $this->translation->readBatchWithFallback($context->getId(), $context->getFallbackId(), $this->type->getTableName(), $ids, false);

        if (empty($translations)) {
            return $items;
        }

        foreach ($items as &$item) {
            foreach ($translations as $translation) {
                if ($translation['objectkey'] === $item['id']) {
                    $item = array_merge($item, $translation['objectdata']);
                }
            }
        }

        unset($item);

        return $items;
    }

    protected function filterFields(array $fields): array
    {
        $names = array_map(static function (Field $field) {
            return $field->getName();
        }, $this->type->getFields());
        $names[] = 'updated_at';
        $names[] = 'created_at';

        foreach ($fields as $key => $field) {
            if (!in_array($key, $names, true)) {
                unset($fields[$key]);
            }
        }

        return $fields;
    }

    private function validateFields(array $data): void
    {
        foreach ($this->type->getFields() as $field) {
            if (!$field->isRequired() || $field->getType() instanceof CheckboxField) {
                continue;
            }

            if (empty($data[$field->getName()])) {
                throw new CustomValidationException(sprintf('Field %s is required', $field->getName()));
            }
        }
    }

    private function quoteFields(array $data): array
    {
        foreach ($data as $key => $value) {
            unset($data[$key]);
            $data['`' . $key . '`'] = $value;
        }

        return $data;
    }
}
