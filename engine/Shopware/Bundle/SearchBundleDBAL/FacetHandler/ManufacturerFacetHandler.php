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
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\SearchBundle\Condition;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\SearchBundleDBAL\FacetHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ManufacturerServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Components\QueryAliasMapper;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\FacetHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ManufacturerFacetHandler implements FacetHandlerInterface
{
    /**
     * @var ManufacturerServiceInterface
     */
    private $manufacturerService;

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
     * @param ManufacturerServiceInterface $manufacturerService
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper $queryAliasMapper
     */
    public function __construct(
        ManufacturerServiceInterface $manufacturerService,
        QueryBuilderFactory $queryBuilderFactory,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->manufacturerService = $manufacturerService;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');

        if (!$this->fieldName = $queryAliasMapper->getShortAlias('sSupplier')) {
            $this->fieldName = 'sSupplier';
        }
    }

    /**
     * @param FacetInterface|Facet\PriceFacet $facet
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     * @return ValueListFacetResult
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

        $query->resetQueryPart('groupBy');
        $query->resetQueryPart('orderBy');

        $query->groupBy('product.id');
        $query->select('DISTINCT product.supplierID as id');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $ids = $statement->fetchAll(\PDO::FETCH_COLUMN);
        $ids = array_filter($ids);

        if (empty($ids)) {
            return null;
        }

        $manufacturers = $this->manufacturerService->getList($ids, $context);

        $activeManufacturers = $this->getActiveIds($criteria);

        return $this->createFacetResult($manufacturers, $activeManufacturers);
    }

    /**
     * @param Manufacturer[] $manufacturers
     * @param int[] $activeIds
     * @return ValueListFacetResult
     */
    private function createFacetResult($manufacturers, $activeIds)
    {
        $listItems = [];

        foreach ($manufacturers as $manufacturer) {
            $listItem = new ValueListItem(
                $manufacturer->getId(),
                $manufacturer->getName(),
                in_array($manufacturer->getId(), $activeIds)
            );

            $listItems[] = $listItem;
        }

        usort($listItems, function (ValueListItem $a, ValueListItem $b) {
            return strcasecmp($a->getLabel(), $b->getLabel());
        });

        return new ValueListFacetResult(
            'manufacturer',
            !empty($activeIds),
            $this->snippetNamespace->get('manufacturer', 'Manufacturer'),
            $listItems,
            $this->fieldName
        );
    }

    /**
     * @param Criteria $criteria
     * @return int[]
     */
    private function getActiveIds($criteria)
    {
        if (!$criteria->hasCondition('manufacturer')) {
            return [];
        }

        /**@var $condition Condition\ManufacturerCondition*/
        $condition = $criteria->getCondition('manufacturer');

        return $condition->getManufacturerIds();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof Facet\ManufacturerFacet);
    }
}
