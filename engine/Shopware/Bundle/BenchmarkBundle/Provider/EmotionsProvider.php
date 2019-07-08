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

namespace Shopware\Bundle\BenchmarkBundle\Provider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class EmotionsProvider implements BenchmarkProviderInterface
{
    private const NAME = 'emotions';

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var ShopContextInterface
     */
    private $shopContext;

    /**
     * @var array
     */
    private $emotionIds = [];

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        $this->shopContext = $shopContext;
        $this->emotionIds = [];

        return [
            'total' => $this->getTotalEmotions(),
            'landingPages' => $this->getLandingPageEmotions(),
            'timed' => $this->getTimedEmotions(),
            'elementUsages' => $this->getElementUsages(),
            'viewportUsages' => $this->getViewportUsages(),
        ];
    }

    /**
     * @return int
     */
    private function getTotalEmotions()
    {
        $queryBuilder = $this->getBasicEmotionCountQueryBuilder();

        return (int) $queryBuilder->execute()->fetchColumn();
    }

    /**
     * @return int
     */
    private function getLandingPageEmotions()
    {
        $queryBuilder = $this->getBasicEmotionCountQueryBuilder();

        return (int) $queryBuilder->andWhere('emotion.is_landingpage = 1')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return int
     */
    private function getTimedEmotions()
    {
        $queryBuilder = $this->getBasicEmotionCountQueryBuilder();

        return (int) $queryBuilder->andWhere('emotion.valid_from IS NOT NULL OR emotion.valid_to IS NOT NULL')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return array
     */
    private function getElementUsages()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $emotionIds = $this->getEmotionIds();

        $data = $queryBuilder->select('COUNT(element.id) as elementCount, element.x_type as elementName')
            ->from('s_emotion_element', 'elementRelation')
            ->innerJoin('elementRelation', 's_library_component', 'element', 'element.id = elementRelation.componentID')
            ->where('elementRelation.emotionID IN (:emotionIds)')
            ->groupBy('elementRelation.componentID')
            ->setParameter(':emotionIds', $emotionIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll();

        $data = array_map(function ($item) {
            $item['elementCount'] = (int) $item['elementCount'];

            return $item;
        }, $data);

        return $data;
    }

    /**
     * @return array
     */
    private function getViewportUsages()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();
        $emotionIds = $this->getEmotionIds();

        $devicesUsed = $queryBuilder->select("GROUP_CONCAT(emotion.device SEPARATOR ',') as devicesUsed")
            ->from('s_emotion', 'emotion')
            ->where('emotion.id IN (:emotionIds)')
            ->setParameter(':emotionIds', $emotionIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchColumn();

        $devicesUsed = array_filter(explode(',', $devicesUsed), static function ($element) {
            return $element !== '';
        });

        $deviceCounts = [0, 0, 0, 0, 0];
        foreach ($devicesUsed as $device) {
            if (array_key_exists($device, $deviceCounts)) {
                ++$deviceCounts[$device];
                continue;
            }

            $deviceCounts[$device] = 1;
        }

        return $deviceCounts;
    }

    /**
     * @return QueryBuilder
     */
    private function getBasicEmotionCountQueryBuilder()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $emotionIds = $this->getEmotionIds();

        return $queryBuilder->select('COUNT(emotion.id)')
            ->from('s_emotion', 'emotion')
            ->where('emotion.id IN (:emotionId)')
            ->setParameter(':emotionId', $emotionIds, Connection::PARAM_INT_ARRAY);
    }

    /**
     * @return array
     */
    private function getEmotionIds()
    {
        $shopId = $this->shopContext->getShop()->getId();
        if (array_key_exists($shopId, $this->emotionIds)) {
            return $this->emotionIds[$shopId];
        }

        $emoCategoriesQb = $this->dbalConnection->createQueryBuilder();
        $emoShopsQb = $this->dbalConnection->createQueryBuilder();

        $categoryId = $this->shopContext->getShop()->getCategory()->getId();

        $emoCategoryIds = $emoCategoriesQb->select('emotion.id')
            ->from('s_emotion', 'emotion')
            ->innerJoin('emotion', 's_emotion_categories', 'emoCategories', 'emotion.id = emoCategories.emotion_id')
            ->innerJoin('emoCategories', 's_categories', 'category', 'category.id = emoCategories.category_id')
            ->where('emotion.is_landingpage = 0 AND (category.path LIKE :categoryIdPath OR category.id = :categoryId)')
            ->setParameter(':categoryId', $categoryId)
            ->setParameter(':categoryIdPath', '%|' . $categoryId . '|%')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $emoShopIds = $emoShopsQb->select('emotion.id')
            ->from('s_emotion', 'emotion')
            ->innerJoin('emotion', 's_emotion_shops', 'emoShops', 'emotion.id = emoShops.emotion_id')
            ->where('emotion.is_landingpage = 1 AND emoShops.shop_id = :shopId')
            ->setParameter(':shopId', $this->shopContext->getShop()->getId())
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $this->emotionIds[$shopId] = array_merge($emoShopIds, $emoCategoryIds);

        return $this->emotionIds[$shopId];
    }
}
