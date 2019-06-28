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
 * Handles request for values suggested to the user
 */
class Value
{
    /**
     * @var DqlHelper
     */
    protected $dqlHelper;

    public function __construct(DqlHelper $dqlHelper)
    {
        $this->dqlHelper = $dqlHelper;
    }

    /**
     * @return DqlHelper
     */
    public function getDqlHelper()
    {
        return $this->dqlHelper;
    }

    /**
     * Suggests values for a given attribute. Optionally we'd be able to filter the suggested values
     * by the operator the user put in front.
     *
     * @param string $attribute
     * @param string $operator
     * @param array  $queryConfig
     *
     * @return array
     */
    public function getValuesFor($attribute, $operator, $queryConfig)
    {
        // Get the entity for the attribute, e.g. Shopware\Models\Article\Detail
        $entity = $this->getDqlHelper()->getEntityForAttribute($attribute);
        // Get the prefixed column, e.g. detail.number
        $column = $this->getDqlHelper()->getColumnForAttribute($attribute);

        // Alias for the entity, e.g. details
        $alias = $this->getDqlHelper()->getPrefixForEntity($entity);

        // Get column name without prefix
        list($prefix, $plainColumn) = explode('.', $column);
        // Column type might be needed for additional formatting
        $columnType = $this->getDqlHelper()->getEntityManager()->getClassMetadata($entity)->fieldMappings[$plainColumn]['type'];

        // Query
        $builder = $this->getDqlHelper()->getEntityManager()->createQueryBuilder()
            // Using distinct instead of groupBy is waaay faster - but will result in some pages having only one result
            ->select("DISTINCT {$column}")
            ->from($entity, $alias);
        // Limit to results matching the filter string
        if ($queryConfig['filter']) {
            $builder->where("{$column} LIKE ?1")
                ->setParameter(1, '%' . $queryConfig['filter'] . '%');
        }
        // Ignore empty results; add pagination
        $builder->andWhere("{$column} != ''")
            ->setFirstResult($queryConfig['offset'])
            ->setMaxResults($queryConfig['limit']);

        $query = $builder->getQuery();

        $paginator = Shopware()->Models()->createPaginator($query);
        $totalCount = $paginator->count();
        $results = [];

        // Iterate results, do some formatting if needed
        foreach ($paginator->getIterator()->getArrayCopy() as $item) {
            $item = array_pop($item);
            if ($item instanceof \DateTime) {
                if ($columnType === 'date') {
                    $item = $item->format('Y-m-d');
                } elseif ($columnType === 'datetime') {
                    $item = $item->format('Y-m-d H:i:s');
                }
            }
            $results[] = ['title' => $item];
        }

        return [
            'data' => $results,
            'total' => $totalCount,
        ];
    }
}
