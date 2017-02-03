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

namespace Shopware\Bundle\SearchBundleDBAL\FacetHandler;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundleDBAL\FacetHandlerInterface;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\FacetHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ShippingFreeFacetHandler implements FacetHandlerInterface
{
    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $snippetNamespace;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @param QueryBuilderFactoryInterface $queryBuilderFactory
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper $queryAliasMapper
     */
    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');

        if (!$this->fieldName = $queryAliasMapper->getShortAlias('shippingFree')) {
            $this->fieldName = 'shippingFree';
        }
    }

    /**
     * Generates the facet data for the passed query, criteria and context object.
     *
     * @param FacetInterface|Facet\ShippingFreeFacet $facet
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     * @return BooleanFacetResult
     */
    public function generateFacet(
        FacetInterface $facet,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $queryCriteria = clone $criteria;
        $queryCriteria->resetConditions();
        $queryCriteria->resetSorting();

        $query = $this->queryBuilderFactory->createQuery($queryCriteria, $context);

        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $query->select('product.id')
            ->andWhere('variant.shippingfree = 1')
            ->setMaxResults(1);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $total = $statement->fetch(\PDO::FETCH_COLUMN);

        if ($total <= 0) {
            return null;
        }

        return new BooleanFacetResult(
            $facet->getName(),
            $this->fieldName,
            $criteria->hasCondition($facet->getName()),
            $this->snippetNamespace->get($facet->getName(), 'Shipping free')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof Facet\ShippingFreeFacet);
    }
}
