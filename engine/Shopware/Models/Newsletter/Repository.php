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

namespace   Shopware\Models\Newsletter;

use Shopware\Components\Model\ModelRepository;

/**
 * Repository for the mailing model
 *
 */
class Repository extends ModelRepository
{
    /**
     * Receives all known newsletter groups
     *
     * @param null $filter
     * @param null $order
     * @param null $limit
     * @param null $offset
     * @return \Doctrine\ORM\Query
     */
    public function getListGroupsQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        // get the query and prepare the limit statement
        $builder = $this->getListGroupsQueryBuilder($filter, $order);
        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                   ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListGroupsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param null $filter
     * @param null $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListGroupsQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'groups'
        ));
        $builder->from('Shopware\Models\Newsletter\Group', 'groups');

        if ($filter !== null) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Receives all known addresses
     *
     * @param null $filter
     * @param null $order
     * @param null $limit
     * @param null $offset
     * @return \Doctrine\ORM\Query
     */
    public function getListAddressesQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        // get the query and prepare the limit statement
        $builder = $this->getListAddressesQueryBuilder($filter, $order);
        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                   ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListAddressesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param null $filter
     * @param null $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListAddressesQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'address',
            'customer',
            'newsletterGroup',
            'lastNewsletter'
        ));
        $builder->from('Shopware\Models\Newsletter\Address', 'address')
            ->leftJoin('address.customer', 'customer', 'WITH', 'address.isCustomer = true')
            ->leftJoin('address.newsletterGroup', 'newsletterGroup')
            ->leftJoin('address.lastNewsletter', 'lastNewsletter');

        if ($filter !== null) {
            $builder->andWhere($builder->expr()->orX(
                'address.email LIKE :search',
                'newsletterGroup.name LIKE :search',
                'lastNewsletter.subject LIKE :search'
            ));
            $builder->setParameter('search', '%' . $filter[0]['value'] . '%');
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }



    /**
     * Receives all known senders
     *
     * @param null $filter
     * @param null $order
     * @param null $limit
     * @param null $offset
     * @return \Doctrine\ORM\Query
     */
    public function getListSenderQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        // get the query and prepare the limit statement
        $builder = $this->getListSenderQueryBuilder($filter, $order);
        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                   ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListSendersQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param null $filter
     * @param null $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListSenderQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'sender'
        ));
        $builder->from('Shopware\Models\Newsletter\Sender', 'sender');

        if ($filter !== null) {
            $builder->andWhere($builder->expr()->orX(
                'mailing.subject LIKE :search'
            ));
            $builder->setParameter('search', '%' . $filter[0]['value'] . '%');
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Receives all known mailings with status > -1
     *
     * @param null $filter
     * @param null $order
     * @param null $limit
     * @param null $offset
     * @return \Doctrine\ORM\Query
     */
    public function getListNewslettersQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        // get the query and prepare the limit statement
        $builder = $this->getListNewslettersQueryBuilder($filter, $order);
        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                   ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListNewslettersQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param null $filter
     * @param null $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListNewslettersQueryBuilder($filter = null, $order = null)
    {
        // Joining the addresses will have a massive impact on query time if many addresses needs to be joined
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'mailing',
            'container',
            'text',
            'articles',
            'links',
            'banner',
//            'addresses'
        ));
        $builder->from('Shopware\Models\Newsletter\Newsletter', 'mailing')
                ->leftJoin('mailing.containers', 'container')
                ->leftJoin('container.text', 'text')
                ->leftJoin('container.articles', 'articles')
                ->leftJoin('container.links', 'links')
                ->leftJoin('container.banner', 'banner')
//                ->leftJoin('mailing.addresses', 'addresses')
                ->where('mailing.status > -1');

        if ($filter !== null) {
            $builder->andWhere($builder->expr()->orX(
                'mailing.subject LIKE :search'
            ));
            $builder->setParameter('search', '%' . $filter[0]['value'] . '%');
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }
}
