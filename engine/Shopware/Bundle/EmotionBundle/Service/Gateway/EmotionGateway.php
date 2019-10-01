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

namespace Shopware\Bundle\EmotionBundle\Service\Gateway;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\EmotionBundle\Service\Gateway\Hydrator\EmotionHydrator;
use Shopware\Bundle\EmotionBundle\Struct\Emotion;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class EmotionGateway
{
    /**
     * @var EmotionHydrator
     */
    private $hydrator;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(EmotionHydrator $hydrator, FieldHelper $fieldHelper, Connection $connection)
    {
        $this->hydrator = $hydrator;
        $this->fieldHelper = $fieldHelper;
        $this->connection = $connection;
    }

    /**
     * @param int[] $emotionIds
     *
     * @return Emotion[]
     */
    public function getList(array $emotionIds, ShopContextInterface $context)
    {
        $query = $this->getQuery($emotionIds);
        $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $emotions = [];
        foreach ($data as $row) {
            $key = $row['__emotion_id'];
            $emotions[$key] = $this->hydrator->hydrate($row);
        }

        return $emotions;
    }

    /**
     * @param int[] $emotionIds
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQuery(array $emotionIds)
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select($this->fieldHelper->getEmotionFields())
                ->addSelect("GROUP_CONCAT(emotion_categories.category_id SEPARATOR ',') as __emotion_category_ids")
                ->addSelect("GROUP_CONCAT(emotion_shops.shop_id SEPARATOR ',') as __emotion_shop_ids")
                ->addSelect($this->fieldHelper->getEmotionTemplateFields());

        $builder->from('s_emotion', 'emotion')
                ->leftJoin('emotion', 's_emotion_attributes', 'emotionAttribute', 'emotion.id = emotionAttribute.emotionID')
                ->leftJoin('emotion', 's_emotion_categories', 'emotion_categories', 'emotion.id = emotion_categories.emotion_id')
                ->leftJoin('emotion', 's_emotion_shops', 'emotion_shops', 'emotion.id = emotion_shops.emotion_id')
                ->leftJoin('emotion', 's_emotion_templates', 'emotionTemplate', 'emotion.template_id = emotionTemplate.id');

        $builder->where('emotion.id IN (:emotionIds)')
                ->setParameter('emotionIds', $emotionIds, Connection::PARAM_INT_ARRAY);

        $builder->groupBy('emotion.id');

        return $builder;
    }
}
