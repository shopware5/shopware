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

namespace Shopware\Models\Emotion;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Attribute\Emotion as EmotionAttribute;

class Repository extends ModelRepository
{
    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string $filter
     * @param array  $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder($filter = null, $orderBy = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['emotions', 'categories'])
            ->from(\Shopware\Models\Emotion\Emotion::class, 'emotions')
            ->leftJoin('emotions.categories', 'categories');

        // Filter the displayed columns with the passed filter string
        if (!empty($filter)) {
            $builder->where('categories.name LIKE ?2')
                ->orWhere('emotions.name LIKE ?2')
                ->orWhere('emotions.modified LIKE ?2')
                ->setParameter(2, '%' . $filter . '%');
        }
        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }

    /**
     * @param array|null  $filter
     * @param string|null $filterBy
     * @param int|null    $categoryId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getListingQuery($filter = null, $filterBy = null, $categoryId = null)
    {
        $builder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $builder->select([
            'SQL_CALC_FOUND_ROWS emotions.id',
            'emotions.name',
            'emotions.active',
            'emotions.mode',
            'emotions.device',
            'emotions.position',
            'emotions.is_landingpage as isLandingPage',
            'emotions.parent_id as parentId',
            'emotions.modified',
            'emotions.listing_visibility as listingVisibility',
            'GROUP_CONCAT(categories.description ORDER BY categories.description ASC) AS categoriesNames',
        ]);

        $builder->from('s_emotion', 'emotions')
            ->leftJoin('emotions', 's_emotion_categories', 'emotionCategories', 'emotions.id = emotionCategories.emotion_id')
            ->leftJoin('emotions', 's_categories', 'categories', 'categories.id = emotionCategories.category_id')
            ->leftJoin('emotions', 's_emotion', 'parent', 'parent.id = emotions.parent_id');

        $builder->groupBy('emotions.id')
            ->addOrderBy('emotionGroup')
            ->addOrderBy('emotions.position')
            ->addOrderBy('emotions.id');

        // Filter by search
        if (!empty($filter) && $filter[0]['property'] === 'filter' && !empty($filter[0]['value'])) {
            $builder->andWhere('emotions.name LIKE :search OR categories.description LIKE :search')
                ->setParameter(':search', '%' . $filter[0]['value'] . '%');
        }

        // Filter by desktop devices
        if (isset($filterBy) && $filterBy === 'onlyDesktop') {
            $builder->andWhere("emotions.device LIKE '%0%'");
        }

        // Filter by tablet landscape devices
        if (isset($filterBy) && $filterBy === 'onlyTabletLandscape') {
            $builder->andWhere("emotions.device LIKE '%1%'");
        }

        // Filter by tablet devices
        if (isset($filterBy) && $filterBy === 'onlyTablet') {
            $builder->andWhere("emotions.device LIKE '%2%'");
        }

        // Filter by mobile landscape devices
        if (isset($filterBy) && $filterBy === 'onlyMobileLandscape') {
            $builder->andWhere("emotions.device LIKE '%3%'");
        }

        // Filter by mobile devices
        if (isset($filterBy) && $filterBy === 'onlyMobile') {
            $builder->andWhere("emotions.device LIKE '%4%'");
        }

        // Filter by active emotion worlds
        if (isset($filterBy) && $filterBy === 'active') {
            $builder->andWhere('emotions.active = 1');
        }

        // Filter by landingpages
        if (isset($filterBy) && $filterBy === 'onlyLandingpage') {
            $builder->andWhere('emotions.is_landingpage = 1');
        }

        // Filter by landing page masters
        if (isset($filterBy) && $filterBy === 'onlyLandingPageMasters') {
            $builder->andWhere('emotions.is_landingpage = 1')
                ->andWhere('emotions.parent_id IS NULL');
        }

        // Filter by shopping worlds
        if (isset($filterBy) && $filterBy === 'onlyWorld') {
            $builder->andWhere('emotions.is_landingpage = 0');
        }

        // Filter by categoryId
        if (!empty($categoryId) && $categoryId !== 'NaN') {
            $path = '%|' . $categoryId . '|%';

            $builder
                ->addSelect([
                    '(
                    CASE
                        WHEN (selectedCategory.id IS NOT NULL AND emotions.is_landingpage = 0) THEN -10
                        WHEN (emotions.is_landingpage = 1 AND emotions.parent_id IS NOT NULL) THEN parent.name
                        WHEN (emotions.is_landingpage = 1 AND emotions.parent_id IS NULL)     THEN emotions.name
                        ELSE -5
                    END
                    ) as emotionGroup',
                ])
                ->leftJoin('emotions', 's_emotion_categories', 'selectedCategory', 'emotions.id = selectedCategory.emotion_id AND selectedCategory.category_id = :categoryId')
                ->andWhere('categories.path LIKE :category OR categories.id = :categoryId')
                ->andWhere('emotions.is_landingpage = 0')
                ->setParameter(':category', $path)
                ->setParameter(':categoryId', $categoryId);
        } else {
            $builder->addSelect('(
                CASE
                    WHEN (emotions.is_landingpage = 1 AND emotions.parent_id IS NOT NULL) THEN parent.name
                    WHEN (emotions.is_landingpage = 1 AND emotions.parent_id IS NULL)     THEN emotions.name
                    WHEN (emotions.is_landingpage = 1) THEN -10
                    ELSE -15
                END
                ) as emotionGroup'
            );
        }

        // Skip preview entries
        $builder->andWhere('emotions.preview_id IS NULL');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object
     *
     * @param bool|null $filter
     * @param array     $orderBy
     * @param int       $offset
     * @param int       $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getNameListQuery($filter = null, $orderBy = null, $offset = null, $limit = null)
    {
        $builder = $this->getNameListQueryBuilder($filter, $orderBy);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getLandingPageListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param bool  $filter
     * @param array $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNameListQueryBuilder($filter = null, $orderBy = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['emotions.id', 'emotions.name'])
            ->from(\Shopware\Models\Emotion\Emotion::class, 'emotions');

        if ($filter === true) {
            $builder->where('emotions.isLandingPage = :isLandingPage')
                ->andWhere('emotions.parentId IS NULL')
                ->setParameter('isLandingPage', 1);
        } else {
            $builder->where('emotions.isLandingPage = :isLandingPage')
                ->setParameter('isLandingPage', 0);
        }

        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object
     *
     * @param int $emotionId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getEmotionDetailQuery($emotionId)
    {
        $builder = $this->getEmotionDetailQueryBuilder($emotionId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getEmotionDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $emotionId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEmotionDetailQueryBuilder($emotionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['emotions', 'elements', 'component', 'fields', 'attribute', 'categories', 'shops', 'template'])
            ->from(\Shopware\Models\Emotion\Emotion::class, 'emotions')
            ->leftJoin('emotions.template', 'template')
            ->leftJoin('emotions.elements', 'elements')
            ->leftJoin('emotions.attribute', 'attribute')
            ->leftJoin('elements.component', 'component')
            ->leftJoin('component.fields', 'fields')
            ->leftJoin('emotions.categories', 'categories')
            ->leftJoin('emotions.shops', 'shops')
            ->where('emotions.id = ?1')
            ->setParameter(1, $emotionId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object
     *
     * @param int $elementId
     * @param int $componentId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getElementDataQuery($elementId, $componentId)
    {
        $builder = $this->getElementDataQueryBuilder($elementId, $componentId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getElementDataQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $elementId
     * @param int $componentId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getElementDataQueryBuilder($elementId, $componentId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['data.id', 'data.value', 'field.id as fieldId', 'field.name', 'field.valueType'])
            ->from(Data::class, 'data')
            ->join('data.field', 'field')
            ->leftJoin('field.component', 'component')
            ->where('component.id = ?1')
            ->andWhere('data.elementId = ?2')
            ->setParameter(1, $componentId)
            ->setParameter(2, $elementId);

        return $builder;
    }

    /**
     * @param int[] $elementIds
     *
     * @return array[] indexed by elementId
     */
    public function getElementsViewports($elementIds)
    {
        $viewportsQuery = $this->getElementViewportsQueryBuilder($elementIds);
        $viewportsData = $viewportsQuery->getQuery()->getArrayResult();

        $viewports = [];

        foreach ($viewportsData as $viewport) {
            $elementId = $viewport['elementId'];
            $viewports[$elementId][] = $viewport;
        }

        return $viewports;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object
     *
     * @param int $emotionId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getEmotionAttributesQuery($emotionId)
    {
        $builder = $this->getEmotionAttributesQueryBuilder($emotionId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getEmotionAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $emotionId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEmotionAttributesQueryBuilder($emotionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['attribute'])
            ->from(EmotionAttribute::class, 'attribute')
            ->where('attribute.emotionId = ?1')
            ->setParameter(1, $emotionId);

        return $builder;
    }

    /**
     * @param int $categoryId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCategoryBaseEmotionsQuery($categoryId)
    {
        $builder = $this->getCategoryBaseEmotionsQueryBuilder($categoryId);

        return $builder->getQuery();
    }

    /**
     * @param int $categoryId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCategoryBaseEmotionsQueryBuilder($categoryId)
    {
        $builder = $this->createQueryBuilder('emotions');
        $builder->select(['emotions'])
            ->innerJoin('emotions.categories', 'categories')
            ->where('categories.id = ?1')
            ->andWhere('(emotions.validFrom <= :now OR emotions.validFrom IS NULL)')
            ->andWhere('(emotions.validTo >= :now OR emotions.validTo IS NULL)')
            ->andWhere('emotions.isLandingPage = 0 ')
            ->andWhere('emotions.active = 1 ')
            ->setParameter(1, $categoryId)
            ->setParameter('now', new \DateTime());

        return $builder;
    }

    /**
     * This function selects all elements and components of the passed emotion id.
     *
     * @param int $emotionId
     *
     * @return QueryBuilder
     */
    public function getEmotionElementsQuery($emotionId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['elements', 'component']);
        $builder->from(Element::class, 'elements');
        $builder->leftJoin('elements.component', 'component');
        $builder->where('elements.emotionId = :emotionId');
        $builder->addOrderBy([['property' => 'elements.startRow', 'direction' => 'ASC']]);
        $builder->addOrderBy([['property' => 'elements.startCol', 'direction' => 'ASC']]);
        $builder->setParameter('emotionId', $emotionId);

        return $builder;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCampaigns($offset = null, $limit = null)
    {
        $builder = $this->createQueryBuilder('emotions');
        $builder->select(['emotions', 'attribute', 'shops'])
            ->innerJoin('emotions.shops', 'shops')
            ->leftJoin('emotions.attribute', 'attribute')
            ->where('emotions.isLandingPage = 1')
            ->andWhere('emotions.active = 1');

        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder;
    }

    /**
     * @param int $shopId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCampaignsByShopId($shopId)
    {
        $builder = $this->getCampaigns();
        $builder->andWhere('shops.id = :shopId')
            ->andWhere('emotions.parentId IS NULL')
            ->setParameter('shopId', $shopId);

        return $builder;
    }

    /**
     * @param int $id
     *
     * @return QueryBuilder
     */
    public function getEmotionById($id)
    {
        $builder = $this->createQueryBuilder('emotions');
        $builder->select(['emotions', 'elements', 'component', 'template'])
            ->leftJoin('emotions.elements', 'elements')
            ->leftJoin('elements.component', 'component')
            ->leftJoin('emotions.template', 'template')
            ->where('emotions.id = ?1')
            ->andWhere('(emotions.validFrom <= :now OR emotions.validFrom IS NULL)')
            ->andWhere('(emotions.validTo >= :now OR emotions.validTo IS NULL)')
            ->setParameter(1, $id)
            ->setParameter('now', new \DateTime());

        return $builder;
    }

    /**
     * @param int[] $elementIds
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getElementViewportsQueryBuilder($elementIds)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['viewports'])
            ->from(ElementViewport::class, 'viewports')
            ->where('viewports.elementId IN (?1)')
            ->setParameter(1, $elementIds, Connection::PARAM_INT_ARRAY);

        return $builder;
    }
}
