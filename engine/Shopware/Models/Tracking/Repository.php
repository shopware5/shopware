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

namespace Shopware\Models\Tracking;

use Shopware\Components\Model\ModelRepository;

/**
 * Shopware Tracking Model
 */
class Repository extends ModelRepository
{
    /**
     * Returns an Banner Statistic Model.Either a new one or an existing one. If no date given
     * the current date will be used.
     *
     * @param int $bannerId
     *
     * @return Banner
     */
    public function getOrCreateBannerStatsModel($bannerId, \DateTimeInterface $date = null)
    {
        if ($date === null) {
            $date = new \DateTime();
        }
        /** @var Banner|null $bannerStatistics */
        $bannerStatistics = $this->findOneBy(['bannerId' => $bannerId, 'displayDate' => $date]);

        // If no Entry for this day exists - create a new one
        if (!$bannerStatistics) {
            $bannerStatistics = new \Shopware\Models\Tracking\Banner($bannerId, $date);

            $bannerStatistics->setClicks(0);
            $bannerStatistics->setViews(0);
        }

        return $bannerStatistics;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the article impression
     *
     * @param int                     $articleId
     * @param int                     $shopId
     * @param \DateTimeInterface|null $date
     * @param string|null             $deviceType
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleImpressionQuery($articleId, $shopId, $date = null, $deviceType = null)
    {
        if ($date == null) {
            $date = new \DateTime();
        }
        $builder = $this->getArticleImpressionQueryBuilder($articleId, $shopId, $date, $deviceType);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleImpressionQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int                $articleId
     * @param int                $shopId
     * @param \DateTimeInterface $date
     * @param string|null        $deviceType
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleImpressionQueryBuilder($articleId, $shopId, $date, $deviceType = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('articleImpression')
                ->from(\Shopware\Models\Tracking\ArticleImpression::class, 'articleImpression')
                ->where('articleImpression.articleId = :articleId')
                ->andWhere('articleImpression.shopId = :shopId')
                ->andWhere('articleImpression.date = :fromDate')
                ->setParameter('articleId', $articleId)
                ->setParameter('shopId', $shopId)
                ->setParameter('fromDate', $date->format('Y-m-d'));

        if ($deviceType) {
            $builder->andWhere('articleImpression.deviceType = :deviceType')
                ->setParameter('deviceType', $deviceType);
        }

        return $builder;
    }
}
