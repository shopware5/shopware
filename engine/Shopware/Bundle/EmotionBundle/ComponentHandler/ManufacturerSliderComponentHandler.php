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

namespace Shopware\Bundle\EmotionBundle\ComponentHandler;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\StoreFrontBundle\Service\ManufacturerServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ManufacturerSliderComponentHandler implements ComponentHandlerInterface
{
    const TYPE_STATIC = 'selected_manufacturers';
    const TYPE_BY_CATEGORY = 'manufacturers_by_cat';

    const LEGACY_CONVERT_FUNCTION = 'getManufacturerSlider';
    const COMPONENT_NAME = 'emotion-components-manufacturer-slider';

    /**
     * @var ManufacturerServiceInterface
     */
    private $manufacturerService;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(ManufacturerServiceInterface $manufacturerService, Connection $connection)
    {
        $this->manufacturerService = $manufacturerService;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === self::COMPONENT_NAME
            || $element->getComponent()->getConvertFunction() === self::LEGACY_CONVERT_FUNCTION;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $type = $element->getConfig()->get('manufacturer_type');

        if (empty($type)) {
            return;
        }

        $manufacturerIds = [];

        switch ($type) {
            case self::TYPE_BY_CATEGORY:
                $categoryId = (int) $element->getConfig()->get('manufacturer_category');
                $manufacturerIds = $this->getManufacturerIdsByCategoryId($categoryId, $context);
                break;

            case self::TYPE_STATIC:
                $selectedManufacturers = $element->getConfig()->get('selected_manufacturers', []);
                $manufacturerIds = array_column($selectedManufacturers, 'supplierId');
                break;
        }

        $manufacturers = $this->manufacturerService->getList($manufacturerIds, $context);

        $element->getData()->set('manufacturers', $manufacturers);
    }

    /**
     * @param int $categoryId
     *
     * @return int[]
     */
    private function getManufacturerIdsByCategoryId($categoryId, ShopContextInterface $context)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select('manufacturer.id')
                ->from('s_articles', 'article')
                ->innerJoin('article', 's_articles_categories_ro', 'article_cat_ro', 'article_cat_ro.articleID = article.id AND article_cat_ro.categoryID = :categoryId')
                ->innerJoin('article_cat_ro', 's_categories', 'category', 'category.id = article_cat_ro.categoryID AND category.active = 1')
                ->innerJoin('article', 's_articles_supplier', 'manufacturer', 'article.supplierID = manufacturer.id')
                ->leftJoin('article', 's_articles_avoid_customergroups', 'article_avoid_group', 'article.id = article_avoid_group.articleID AND article_avoid_group.customergroupID = :customerGroupId')
                ->where('article.active = 1')
                ->andWhere('article_avoid_group.articleID IS NULL')
                ->groupBy('manufacturer.id')
                ->orderBy('manufacturer.name')
                ->setMaxResults(12)
                ->setParameter('categoryId', $categoryId)
                ->setParameter('customerGroupId', $context->getCurrentCustomerGroup()->getId())
        ;

        return $builder->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }
}
