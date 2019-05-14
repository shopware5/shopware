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

namespace Shopware\Bundle\SitemapBundle\Repository;

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Emotion\Emotion;

class LandingPageRepository implements LandingPageRepositoryInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @return array
     */
    public function getLandingPages(ShopContextInterface $shopContext)
    {
        $builder = $this->getQueryBuilder($shopContext);

        return $builder->getQuery()->getArrayResult();
    }

    /**
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getQueryBuilder(ShopContextInterface $shopContext)
    {
        $shopId = $shopContext->getShop()->getId();

        $builder = $this->modelManager->createQueryBuilder();
        $builder->select(['emotion', 'attribute', 'shops'])
            ->from(Emotion::class, 'emotion')
            ->innerJoin('emotion.shops', 'shops')
            ->leftJoin('emotion.attribute', 'attribute')
            ->where('emotion.isLandingPage = 1')
            ->andWhere('(emotion.validTo >= CURRENT_TIMESTAMP() OR emotion.validTo IS NULL)')
            ->andWhere('(emotion.validFrom <= CURRENT_TIMESTAMP() OR emotion.validFrom IS NULL)')
            ->andWhere('emotion.active = 1');

        $builder->andWhere('shops.id = :shopId')
            ->andWhere('emotion.parentId IS NULL')
            ->setParameter('shopId', $shopId);

        return $builder;
    }
}
