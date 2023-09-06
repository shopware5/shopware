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

namespace Shopware\Bundle\SearchBundleDBAL\FacetHandler;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\MediaListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\MediaListItem;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\PriceHelperInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ConfiguratorOptionsGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

class VariantFacetHandler implements PartialFacetHandlerInterface
{
    private QueryBuilderFactoryInterface $queryBuilderFactory;

    private string $fieldName;

    private PriceHelperInterface $helper;

    private ConfiguratorOptionsGatewayInterface $gateway;

    public function __construct(
        ConfiguratorOptionsGatewayInterface $gateway,
        QueryBuilderFactoryInterface $queryBuilderFactory,
        QueryAliasMapper $queryAliasMapper,
        PriceHelperInterface $helper
    ) {
        $this->fieldName = $queryAliasMapper->getShortAlias('variants') ?? 'var';
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->helper = $helper;
        $this->gateway = $gateway;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof VariantFacet;
    }

    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $options = $this->getOptions($context, $reverted, $facet);

        if ($options === null) {
            return null;
        }
        $actives = $this->getFilteredValues($criteria);

        return $this->createCollectionResult($facet, $options, $actives);
    }

    /**
     * @deprecated - Will be private with Shopware 5.8
     *
     * @return Group[]|null
     */
    protected function getOptions(ShopContextInterface $context, Criteria $queryCriteria, VariantFacet $facet)
    {
        if (empty($facet->getGroupIds())) {
            return null;
        }

        $query = $this->queryBuilderFactory->createQuery($queryCriteria, $context);
        $this->rebuildQuery($queryCriteria, $query, $facet);

        $valueIds = $query->execute()->fetchAll(PDO::FETCH_COLUMN);

        if (empty($valueIds)) {
            return null;
        }

        return $this->gateway->getOptions($valueIds, $context);
    }

    /**
     * Modifies the query reading products from the database to reflect the selected options
     */
    private function rebuildQuery(Criteria $criteria, QueryBuilder $query, VariantFacet $facet): void
    {
        $conditions = $criteria->getConditionsByClass(VariantCondition::class);
        $conditions = array_filter($conditions, function (VariantCondition $condition) {
            return $condition->expandVariants();
        });

        $variantAlias = 'variant';
        if (empty($conditions)) {
            $this->helper->joinAvailableVariant($query);
            $variantAlias = 'availableVariant';
        }

        $query->innerJoin($variantAlias, 's_article_configurator_option_relations', 'variantOptions', 'variantOptions.article_id = ' . $variantAlias . '.id');

        $query->resetQueryPart('orderBy');
        $query->innerJoin('variantOptions', 's_article_configurator_options', 'options', 'options.id = variantOptions.option_id AND options.group_id IN (:variantGroupIds)');
        $query->addGroupBy('variantOptions.option_id');
        $query->select('variantOptions.option_id as id');
        $query->setParameter('variantGroupIds', $facet->getGroupIds(), Connection::PARAM_INT_ARRAY);
    }

    /**
     * @return array<int>
     */
    private function getFilteredValues(Criteria $criteria): array
    {
        $values = [];
        foreach ($criteria->getConditions() as $condition) {
            if ($condition instanceof VariantCondition) {
                foreach ($condition->getOptionIds() as $id) {
                    $values[] = $id;
                }
            }
        }

        return $values;
    }

    /**
     * @param Group[] $groups
     * @param int[]   $actives
     */
    private function createCollectionResult(
        VariantFacet $facet,
        array $groups,
        array $actives
    ): FacetResultGroup {
        $results = [];

        foreach ($groups as $group) {
            $items = [];
            $useMedia = false;
            $isActive = false;

            foreach ($group->getOptions() as $option) {
                $listItem = new MediaListItem(
                    $option->getId(),
                    $option->getName(),
                    \in_array($option->getId(), $actives, true),
                    $option->getMedia(),
                    $option->getAttributes()
                );

                $isActive = $isActive || $listItem->isActive();
                $useMedia = $useMedia || $listItem->getMedia() !== null;

                $items[] = $listItem;
            }

            if ($useMedia) {
                $results[] = new MediaListFacetResult(
                    $facet->getName(),
                    $isActive,
                    $group->getName(),
                    $items,
                    $this->fieldName,
                    $group->getAttributes()
                );
            } else {
                $results[] = new ValueListFacetResult(
                    $facet->getName(),
                    $isActive,
                    $group->getName(),
                    $items,
                    $this->fieldName,
                    $group->getAttributes()
                );
            }
        }

        return new FacetResultGroup($results, null, $facet->getName());
    }
}
