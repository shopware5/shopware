<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Models
 * @subpackage Emotion
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */
namespace   Shopware\Models\Emotion;
use         Shopware\Components\Model\ModelRepository;

/**
 * Repository for the \Shopware\Models\Emotion\Emotion model.
 * The repository is responsible for all CRUD function around the emotion model.
 * It contains all queries which executed in shopware to read, save, delete or update an shopware
 * emotion.
 *
 * @category   Shopware
 * @package    Shopware_Models
 * @subpackage Emotion
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 */
class Repository extends ModelRepository
{

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $filter
     * @param $orderBy
     * @param $offset
     * @param $limit
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($filter = null, $orderBy = null, $offset = null, $limit = null)
    {
        $builder = $this->getListQueryBuilder($filter, $orderBy);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param null $filter
     * @param null $orderBy
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder($filter = null, $orderBy = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('emotions', 'categories'))
                ->from('Shopware\Models\Emotion\Emotion', 'emotions')
                ->leftJoin('emotions.categories', 'categories');

        //filter the displayed columns with the passed filter string
        if (!empty($filter)) {
            $builder->where('category.name LIKE ?2')
                    ->where('emotions.name LIKE ?2')
                    ->orWhere('emotions.rows = ?1')
                    ->orWhere('emotions.modified LIKE ?2')
                    ->setParameter(1, $filter)
                    ->setParameter(2, '%' . $filter . '%');
        }
        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $emotionId
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
     * @param $emotionId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEmotionDetailQueryBuilder($emotionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('emotions', 'elements', 'component', 'fields', 'attribute','categories'))
                ->from('Shopware\Models\Emotion\Emotion', 'emotions')
                ->leftJoin('emotions.elements', 'elements')
                ->leftJoin('emotions.attribute', 'attribute')
                ->leftJoin('elements.component', 'component')
                ->leftJoin('component.fields', 'fields')
                ->leftJoin('emotions.categories', 'categories')
                ->where('emotions.id = ?1')
                ->setParameter(1, $emotionId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $elementId
     * @param $componentId
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
     * @param $elementId
     * @param $componentId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getElementDataQueryBuilder($elementId, $componentId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('data.value', 'field.name', 'field.id', 'field.valueType'))
                ->from('Shopware\Models\Emotion\Data', 'data')
                ->join('data.field', 'field')
                ->leftJoin('field.component', 'component')
                ->where('component.id = ?1')
                ->andWhere('data.elementId = ?2')
                ->setParameter(1, $componentId)
                ->setParameter(2, $elementId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $emotionId
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
     * @param $emotionId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEmotionAttributesQueryBuilder($emotionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('attribute'))
                      ->from('Shopware\Models\Attribute\Emotion', 'attribute')
                      ->where('attribute.emotionId = ?1')
                      ->setParameter(1, $emotionId);
        return $builder;
    }

    /**
     * @param $categoryId
     * @return \Doctrine\ORM\Query
     */
    public function getCategoryEmotionsQuery($categoryId)
    {
        $builder = $this->getCategoryEmotionsQueryBuilder($categoryId);
        return $builder->getQuery();
    }

    /**
     * @param $categoryId
     * @return \Doctrine\ORM\Query
     */
    public function getCategoryEmotionsQueryBuilder($categoryId)
    {

        $builder = $this->createQueryBuilder('emotions');
        $builder->select(array('emotions', 'elements', 'component'))
                ->leftJoin('emotions.elements', 'elements')
                ->leftJoin('elements.component', 'component')
                ->innerJoin('emotions.categories','categories')
                ->where('categories.id = ?1')
                ->andWhere('(emotions.validFrom <= CURRENT_TIMESTAMP() OR emotions.validFrom IS NULL)')
                ->andWhere('(emotions.validTo >= CURRENT_TIMESTAMP() OR emotions.validTo IS NULL)')
                ->andWhere('emotions.isLandingPage = 0 ')
                ->andWhere('emotions.active = 1 ')
                ->setParameter(1, $categoryId);

        return $builder;
    }

    /**
     * @param $categoryId
     * @return \Doctrine\ORM\Query
     */
    public function getCampaignByCategoryQuery($categoryId)
    {
        $builder = $this->createQueryBuilder('emotions');
        $builder->select(array('emotions'))
                ->innerJoin('emotions.categories','categories')
                ->where('categories.id = ?1')
                ->andWhere('(emotions.validFrom <= CURRENT_TIMESTAMP() OR emotions.validFrom IS NULL)')
                ->andWhere('(emotions.validTo >= CURRENT_TIMESTAMP() OR emotions.validTo IS NULL)')
                ->andWhere('emotions.isLandingPage = 1 ')
                ->andWhere('emotions.active = 1 ')
                ->setParameter(1, $categoryId);
        return $builder->getQuery();
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function getCampaigns()
    {
        $builder = $this->createQueryBuilder('emotions');
        $builder->select(array('emotions','categories.id AS categoryId'))
                ->innerJoin('emotions.categories','categories')
                ->where('emotions.isLandingPage = 1 ')
                ->andWhere('emotions.active = 1 ');

        return $builder;
    }

    /**
     * @param $id
     * @return \Doctrine\ORM\Query
     */
    public function getEmotionById($id)
    {

        $builder = $this->createQueryBuilder('emotions');
        $builder->select(array('emotions', 'elements', 'component'))
                ->leftJoin('emotions.elements', 'elements')
                ->leftJoin('elements.component', 'component')
                ->where('emotions.id = ?1')
                ->andWhere('(emotions.validFrom <= CURRENT_TIMESTAMP() OR emotions.validFrom IS NULL)')
                ->andWhere('(emotions.validTo >= CURRENT_TIMESTAMP() OR emotions.validTo IS NULL)')
                ->setParameter(1, $id);

        return $builder;
    }

}
