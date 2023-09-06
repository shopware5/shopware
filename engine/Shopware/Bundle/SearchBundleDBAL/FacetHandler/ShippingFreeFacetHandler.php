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

use Enlight_Components_Snippet_Namespace;
use PDO;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\ShippingFreeFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;
use Shopware_Components_Snippet_Manager;

class ShippingFreeFacetHandler implements PartialFacetHandlerInterface
{
    private QueryBuilderFactoryInterface $queryBuilderFactory;

    private Enlight_Components_Snippet_Namespace $snippetNamespace;

    private string $fieldName;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');
        $this->fieldName = $queryAliasMapper->getShortAlias('shippingFree') ?? 'shippingFree';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof ShippingFreeFacet;
    }

    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        return $this->getFacet($facet, $reverted, $criteria, $context);
    }

    private function getFacet(
        ShippingFreeFacet $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ): ?BooleanFacetResult {
        $query = $this->queryBuilderFactory->createQuery($reverted, $context);
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $query->select('product.id')
            ->andWhere('variant.shippingfree = 1')
            ->setMaxResults(1);

        $total = $query->execute()->fetch(PDO::FETCH_COLUMN);

        if ($total <= 0) {
            return null;
        }

        if (!empty($facet->getLabel())) {
            $label = $facet->getLabel();
        } else {
            $label = $this->snippetNamespace->get($facet->getName(), 'Shipping free');
        }

        return new BooleanFacetResult(
            $facet->getName(),
            $this->fieldName,
            $criteria->hasCondition($facet->getName()),
            $label
        );
    }
}
