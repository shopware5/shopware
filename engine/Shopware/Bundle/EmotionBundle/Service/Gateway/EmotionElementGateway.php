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
use Shopware\Bundle\EmotionBundle\Service\Gateway\Hydrator\EmotionElementHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class EmotionElementGateway
{
    /**
     * @var EmotionElementHydrator
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

    public function __construct(EmotionElementHydrator $hydrator, FieldHelper $fieldHelper, Connection $connection)
    {
        $this->hydrator = $hydrator;
        $this->fieldHelper = $fieldHelper;
        $this->connection = $connection;
    }

    /**
     * @param int[] $emotionIds
     *
     * @return array
     */
    public function getList(array $emotionIds, ShopContextInterface $context)
    {
        $data = $this->getQuery($emotionIds)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $elementIds = array_column($data, '__emotionElement_id');

        $elementConfigs = $this->getConfigQuery($elementIds, $context)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);

        $elementViewports = $this->getViewportsQuery($elementIds)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP);

        $elements = [];
        foreach ($data as $row) {
            $emotionKey = $row['__emotionElement_emotion_id'];
            $key = $row['__emotionElement_id'];

            $config = [];
            if (array_key_exists($key, $elementConfigs)) {
                $config = $elementConfigs[$key];
            }

            $viewports = [];
            if (array_key_exists($key, $elementViewports)) {
                $viewports = $elementViewports[$key];
            }

            $elements[$emotionKey][$key] = $this->hydrator->hydrate($row, $config, $viewports);
        }

        return $elements;
    }

    /**
     * @param int[] $emotionIds
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQuery(array $emotionIds)
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select('emotionElement.emotionID')
                ->addSelect($this->fieldHelper->getEmotionElementFields())
                ->addSelect($this->fieldHelper->getEmotionElementLibraryFields());

        $builder->from('s_emotion_element', 'emotionElement')
                ->innerJoin('emotionElement', 's_library_component', 'emotionLibraryComponent', 'emotionElement.componentID = emotionLibraryComponent.id');

        $builder->where('emotionElement.emotionID IN (:emotionIds)')
                ->setParameter('emotionIds', $emotionIds, Connection::PARAM_INT_ARRAY);

        $builder->addOrderBy('emotionElement.start_row', 'ASC')
                ->addOrderBy('emotionElement.start_col', 'ASC');

        return $builder;
    }

    /**
     * @param int[] $elementIds
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getConfigQuery(array $elementIds, ShopContextInterface $context)
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select(['emotionElementValue.elementID'])
                ->addSelect($this->fieldHelper->getEmotionElementValueFields())
                ->addSelect($this->fieldHelper->getEmotionElementLibraryFieldFields());

        $builder->from('s_emotion_element_value', 'emotionElementValue')
                ->innerJoin('emotionElementValue', 's_library_component_field', 'emotionLibraryComponentField', 'emotionElementValue.fieldID = emotionLibraryComponentField.id');

        $builder->where('emotionElementValue.elementID IN (:elementIds)')
            ->setParameter('elementIds', $elementIds, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addEmotionElementTranslation($builder, $context);

        return $builder;
    }

    /**
     * @param int[] $elementIds
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getViewportsQuery(array $elementIds)
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select(['emotionElementViewport.elementID'])
            ->addSelect($this->fieldHelper->getEmotionElementViewportFields());

        $builder->from('s_emotion_element_viewports', 'emotionElementViewport');

        $builder->where('emotionElementViewport.elementID IN (:elementIds)')
            ->setParameter('elementIds', $elementIds, Connection::PARAM_INT_ARRAY);

        return $builder;
    }
}
