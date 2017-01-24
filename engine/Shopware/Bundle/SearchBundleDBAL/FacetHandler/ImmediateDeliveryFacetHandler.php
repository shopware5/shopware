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

use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandler\ImmediateDeliveryConditionHandler;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\FacetHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ImmediateDeliveryFacetHandler implements PartialFacetHandlerInterface
{
    /**
     * @var QueryBuilderFactory
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
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper $queryAliasMapper
     */
    public function __construct(
        QueryBuilderFactory $queryBuilderFactory,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');

        if (!$this->fieldName = $queryAliasMapper->getShortAlias('immediateDelivery')) {
            $this->fieldName = 'immediateDelivery';
        }
    }

    /**
     * @param FacetInterface $facet
     * @param Criteria $reverted
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     * @return FacetResultInterface
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $query = $this->queryBuilderFactory->createQuery($reverted, $context);
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        if (!$query->hasState(ImmediateDeliveryConditionHandler::STATE_INCLUDES_ALL_VARIANTS)) {
            $query->innerJoin(
                'product',
                's_articles_details',
                'allVariants',
                'allVariants.articleID = product.id
                 AND allVariants.active = 1
                 AND allVariants.instock >= allVariants.minpurchase'
            );

            $query->addState(ImmediateDeliveryConditionHandler::STATE_INCLUDES_ALL_VARIANTS);
        }

        $query->select('product.id')
            ->setMaxResults(1);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $total = $statement->fetch(\PDO::FETCH_COLUMN);

        if ($total <= 0) {
            return null;
        }

        /** @var Facet\ImmediateDeliveryFacet $facet */
        if (!empty($facet->getLabel())) {
            $label = $facet->getLabel();
        } else {
            $label = $this->snippetNamespace->get($facet->getName(), 'Immediate delivery');
        }

        return new BooleanFacetResult(
            $facet->getName(),
            $this->fieldName,
            $criteria->hasCondition($facet->getName()),
            $label
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof Facet\ImmediateDeliveryFacet);
    }
}
