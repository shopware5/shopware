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

namespace Shopware\Models\Mail;

use Doctrine\ORM\Query;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Attribute\Mail as MailAttribute;

/**
 * @extends ModelRepository<Mail>
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects a list of all mails.
     *
     * @param int $mailId
     *
     * @return Query
     */
    public function getMailQuery($mailId)
    {
        $builder = $this->getMailQueryBuilder($mailId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getMailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $mailId
     *
     * @return QueryBuilder
     */
    public function getMailQueryBuilder($mailId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['mails', 'attribute'])
                ->from(Mail::class, 'mails')
                ->leftJoin('mails.attribute', 'attribute')
                ->where('mails.id = ?1')
                ->setParameter(1, $mailId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attribute model
     * for the passed mail id.
     *
     * @param int $mailId
     *
     * @return Query
     */
    public function getAttributesQuery($mailId)
    {
        $builder = $this->getAttributesQueryBuilder($mailId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $mailId
     *
     * @return QueryBuilder
     */
    public function getAttributesQueryBuilder($mailId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['attribute'])
                      ->from(MailAttribute::class, 'attribute')
                      ->where('attribute.mailId = ?1')
                      ->setParameter(1, $mailId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search for mails with
     * the passed name. The passed mail id will be excluded.
     *
     * @param string   $name
     * @param int|null $mailId
     *
     * @return Query
     */
    public function getValidateNameQuery($name, $mailId = null)
    {
        return $this->getValidateNameQueryBuilder($name, $mailId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getValidateNameQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string   $name
     * @param int|null $mailId
     *
     * @return QueryBuilder
     */
    public function getValidateNameQueryBuilder($name, $mailId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['mail'])
                ->from(Mail::class, 'mail')
                ->where('mail.name = :name')
                ->setParameter('name', $name);

        if ($mailId) {
            $builder->andWhere('mail.id != :id');
            $builder->setParameter('id', $mailId);
        }

        return $builder;
    }

    /**
     * @deprecated - Will be removed without replacement in 5.8
     *
     * @param array[]      $filter
     * @param array[]|null $order
     * @param int|null     $offset
     * @param int|null     $limit
     *
     * @return Query
     */
    public function getMailListQuery(array $filter = [], array $order = null, $offset = null, $limit = null)
    {
        return $this->getMailsListQueryBuilder($filter, $order, $offset, $limit)->getQuery();
    }

    /**
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return QueryBuilder
     */
    public function getMailsListQueryBuilder(array $filter = [], array $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder
            ->setAlias('mail')
            ->select('mail')
            ->from(Mail::class, 'mail');

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }
        if ($offset !== null) {
            $builder->setFirstResult($offset);
            $builder->setMaxResults($limit);
        }

        return $builder;
    }
}
