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

namespace Shopware\Components\HttpCache\UrlProvider;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Routing\Context;

class EmotionProvider extends CategoryProvider
{
    const NAME = 'emotion';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Context $context, $limit = null, $offset = null)
    {
        $qb = $this->getBaseQuery()
            ->addSelect(['emo.id', 'emo.is_landingpage', '(cat.path IS NULL) AS isIndex'])
            ->orderBy('emo.id')
            ->setParameter(':shop', $context->getShopId());

        if ($limit !== null && $offset !== null) {
            $qb->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $result = $qb->execute()->fetchAll();

        if (!count($result)) {
            return [];
        }

        return $this->router->generateList(
            array_map(
                function ($emotion) {
                    if ($emotion['isIndex']) {
                        $controllerName = 'index';
                    } elseif ($emotion['is_landingpage']) {
                        $controllerName = 'campaign';
                    } else {
                        $controllerName = 'listing';
                    }

                    return ['module' => 'widgets', 'sViewport' => 'emotion', 'emotionId' => $emotion['id'], 'fullPath' => true, 'controllerName' => $controllerName];
                },
                $result
            ),
            $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(Context $context)
    {
        return (int) $this->getBaseQuery()
            ->addSelect(['COUNT(emo.id)'])
            ->setParameter(':shop', $context->getShopId())
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return QueryBuilder
     */
    protected function getBaseQuery()
    {
        return $this->connection->createQueryBuilder()
            ->from('s_emotion', 'emo')
            ->join(
                'emo',
                's_emotion_categories',
                'emo_cat',
                'emo.id = emo_cat.emotion_id')
            ->leftJoin('emo_cat', 's_categories', 'cat', 'emo_cat.category_id = cat.id')
            ->where('emo.active = 1')
            ->andWhere(sprintf('emo_cat.category_id IN (%s)', $this->prepareSubQuery()->getSQL()))
            ->andWhere('emo.valid_to > NOW() OR emo.valid_to IS NULL');
    }

    /**
     * @return QueryBuilder
     */
    private function prepareSubQuery()
    {
        return parent::getBaseQuery()
            ->addSelect(['cat.id']);
    }
}
