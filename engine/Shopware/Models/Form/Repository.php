<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\Form;

use Doctrine\ORM\Query;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Attribute\Form as FormAttribute;

/**
 * @extends ModelRepository<Form>
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all list of forms.
     *
     * @param array|null $filter
     * @param array|null $orderBy
     * @param int        $offset
     * @param int        $limit
     *
     * @return Query<Form>
     */
    public function getListQuery($filter, $orderBy, $offset, $limit)
    {
        $builder = $this->getListQueryBuilder($filter, $orderBy);
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array|null $filter
     * @param array|null $orderBy
     *
     * @return QueryBuilder
     */
    public function getListQueryBuilder($filter = null, $orderBy = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['form', 'attribute'])
            ->from($this->getEntityName(), 'form')
            ->leftJoin('form.attribute', 'attribute');

        if ($filter !== null) {
            $this->addFilter($builder, $filter);
        }

        if ($orderBy !== null) {
            $this->addOrderBy($builder, $orderBy);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select all data about a single form
     * for the passed form id.
     *
     * @param int      $formId
     * @param int|null $shopId
     *
     * @return Query<Form>
     */
    public function getFormQuery($formId, $shopId = null)
    {
        $builder = $this->getFormQueryBuilder($formId, $shopId);

        return $builder->getQuery();
    }

    /**
     * Get active forms query
     *
     * @param int      $formId
     * @param int|null $shopId
     *
     * @return Query<Form>
     */
    public function getActiveFormQuery($formId, $shopId = null)
    {
        return $this->getFormQueryBuilder($formId, $shopId)
            ->andWhere('forms.active = 1')
            ->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getFormQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int      $formId
     * @param int|null $shopId
     *
     * @return QueryBuilder
     */
    public function getFormQueryBuilder($formId, $shopId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['forms', 'fields', 'attribute'])
            ->from(Form::class, 'forms')
            ->leftJoin('forms.fields', 'fields')
            ->leftJoin('forms.attribute', 'attribute')
            ->where('forms.id = :form_id')
            ->orderBy('fields.position')
            ->setParameter('form_id', $formId);

        if ($shopId) {
            $builder->andWhere('(forms.shopIds LIKE :shopId OR forms.shopIds IS NULL)')
                ->setParameter('shopId', '%|' . $shopId . '|%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search defined attributes
     * for the passed form id.
     *
     * @param int $formId
     *
     * @return Query<FormAttribute>
     */
    public function getAttributesQuery($formId)
    {
        $builder = $this->getAttributesQueryBuilder($formId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $formId
     *
     * @return QueryBuilder
     */
    public function getAttributesQueryBuilder($formId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['attribute'])
            ->from(FormAttribute::class, 'attribute')
            ->where('attribute.formId = ?1')
            ->setParameter(1, $formId);

        return $builder;
    }
}
