<?php
namespace   Shopware\CustomModels\Ticket;
use         Shopware\Components\Model\ModelRepository;

/**
 *
 * Repository for the Ticket model (Shopware\CustomModels\Ticket\Support).
 * <br>
 * The Ticket repository is responsible to load all Ticket data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 *
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the tickets for the backend list
     * @param $employeeId
     * @param $statusId
     * @param $filter
     * @param null $order
     * @param null $offset
     * @param null $limit
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($employeeId, $statusId, $filter, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getListQueryBuilder($employeeId, $statusId, $filter, $order);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $employeeId
     * @param $statusId
     * @param $filter
     * @param $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder($employeeId, $statusId, $filter, $order)
    {

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            array(
                'ticket.id',
                'ticket.uniqueId',
                'customer.id as userId',
                'ticket.employeeId as employeeId',
                'type.id as ticketTypeId',
                'type.name as ticketTypeName',
                'type.gridColor as ticketTypeColor',
                'statusAlias.id as statusId',
                'statusAlias.description as status',
                'statusAlias.color as statusColor',
                'ticket.email as email',
                'ticket.subject as subject',
                'ticket.message as message',
                'ticket.receipt as receipt',
                'ticket.lastContact as lastContact',
                'ticket.isoCode as isoCode',
                'CONCAT(CONCAT(billing.firstName, \' \'), billing.lastName) as contact',
                'billing.company as company',
            ))
            ->from('Shopware\CustomModels\Ticket\Support', 'ticket')
            ->leftJoin('ticket.customer', 'customer')
            ->leftJoin('customer.billing', 'billing')
            ->leftJoin('ticket.status', 'statusAlias')
            ->leftJoin('ticket.type', 'type');

        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        if (!empty($employeeId)) {
            $builder->where('ticket.employeeId = ?1');
            $builder->setParameter(1,$employeeId);
        }

        if (!empty($statusId)) {
            $builder->andWhere('statusAlias.id = :status');
            $builder->setParameter("status", $statusId);
        }
        if (!empty($filter) && isset($filter[0]['property']) && $filter[0]['property'] == 'free') {

            $builder->andWhere(
                $builder->expr()->orX(
                    $builder->expr()->like('billing.firstName',':search'),
                    $builder->expr()->like('billing.lastName',':search'),
                    $builder->expr()->like('billing.company',':search'),
                    $builder->expr()->like('type.name',':search'),
                    $builder->expr()->like('statusAlias.description',':search'),
                    $builder->expr()->like('ticket.subject',':search'),
                    $builder->expr()->like('ticket.message',':search')
                )
            );
            $builder->setParameter("search", '%' . $filter[0]["value"] . '%');
        }
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the tickets for the frontend list
     * @param $customerId
     * @param $statusId
     * @param null $order
     * @param null $offset
     * @param null $limit
     * @internal param $filter
     * @internal param $employeeId
     * @return \Doctrine\ORM\Query
     */
    public function getFrontendTicketListQuery($customerId, $statusId = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getFrontendTicketListQueryBuilder($customerId, $statusId, $order);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getFrontendTicketListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $customerId
     * @param $statusId
     * @param $order
     * @internal param $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFrontendTicketListQueryBuilder($customerId, $statusId, $order)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            array(
                'ticket.id',
                'ticket.uniqueId',
                'customer.id as userId',
                'ticket.employeeId as employeeId',
                'type.id as ticketTypeId',
                'type.name as ticketTypeName',
                'type.gridColor as ticketTypeColor',
                'statusAlias.id as statusId',
                'statusAlias.description as status',
                'statusAlias.color as statusColor',
                'ticket.email as email',
                'ticket.subject as subject',
                'ticket.message as message',
                'ticket.receipt as receipt',
                'ticket.lastContact as lastContact',
                'ticket.isoCode as isoCode',
                'CONCAT(CONCAT(billing.firstName, \' \'), billing.lastName) as contact',
                'billing.company as company',
            ))
                ->from('Shopware\CustomModels\Ticket\Support', 'ticket')
                ->leftJoin('ticket.customer', 'customer')
                ->leftJoin('customer.billing', 'billing')
                ->leftJoin('ticket.status', 'statusAlias')
                ->leftJoin('ticket.type', 'type');

        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        if (!empty($customerId)) {
            $builder->where('customer.id = ?1');
            $builder->setParameter(1,$customerId);
        }

        if (!empty($statusId)) {
            $builder->andWhere('statusAlias.id = :status');
            $builder->setParameter("status", $statusId);
        }
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects a single ticket
     * @param $ticketId
     * @return \Doctrine\ORM\Query
     */
    public function getTicketDetailQuery($ticketId)
    {
        $builder = $this->getTicketDetailQueryBuilder($ticketId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTicketDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $ticketId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTicketDetailQueryBuilder($ticketId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            array(
                'ticket.id',
                'ticket.uniqueId',
                'ticket.userId as userId',
                'ticket.employeeId as employeeId',
                'type.id as ticketTypeId',
                'type.name as ticketTypeName',
                'type.gridColor as ticketTypeColor',
                'statusAlias.id as statusId',
                'statusAlias.closed as closed',
                'statusAlias.responsible as responsible',
                'statusAlias.description as status',
                'statusAlias.color as statusColor',
                'ticket.email as email',
                'ticket.subject as subject',
                'ticket.message as message',
                'ticket.receipt as receipt',
                'ticket.lastContact as lastContact',
                'ticket.isoCode as isoCode'
            ))
            ->from('Shopware\CustomModels\Ticket\Support', 'ticket')
            ->leftJoin('ticket.status', 'statusAlias')
            ->leftJoin('ticket.type', 'type')
            ->where('ticket.id = :ticketId')
            ->setParameter("ticketId", $ticketId)
            ->setFirstResult(0)
            ->setMaxResults(1);
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the ticket history
     * @param $ticketId
     * @return \Doctrine\ORM\Query
     */
    public function getTicketHistoryQuery($ticketId)
    {
        $builder = $this->getTicketHistoryQueryBuilder($ticketId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTicketHistoryQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $ticketId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTicketHistoryQueryBuilder($ticketId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();


        $builder->select(
            array(
                'history.id as id',
                'ticket.id as ticketId',
                'ticket.email as email',
                'history.swUser as swUser',
                'history.subject as subject',
                'history.message as message',
                'history.receipt as receipt',
                'history.supportType as supportType',
                'history.receiver as receiver',
                'history.direction as direction'
            ))
            ->from('Shopware\CustomModels\Ticket\Support', 'ticket')
            ->leftJoin('ticket.history', 'history')
            ->where('history.ticketId = :ticketId')
            ->setParameter('ticketId', $ticketId)
            ->orderBy('history.receipt','DESC');

        return $builder;
    }


    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the status list for the backend combobox
     * @return \Doctrine\ORM\Query
     */
    public function getStatusListQuery()
    {
        $builder = $this->getStatusListQueryBuilder();
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getStatusListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getStatusListQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('status'))
                ->from('Shopware\CustomModels\Ticket\Status', 'status');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the mail list for the backend module
     * @param null $locale
     * @param bool $onlyCustomSubmissions
     * @param bool $onlyDefaultSubmission
     * @return \Doctrine\ORM\Query
     */
    public function getMailListQuery($locale = null, $onlyCustomSubmissions = false, $onlyDefaultSubmission = false)
    {
        $builder = $this->getMailListQueryBuilder($locale, $onlyCustomSubmissions, $onlyDefaultSubmission);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getMailListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $locale
     * @param $onlyCustomSubmissions
     * @param $onlyDefaultSubmission
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getMailListQueryBuilder($locale, $onlyCustomSubmissions, $onlyDefaultSubmission)
    {

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
                    'mail.id as id',
                    'mail.name as name',
                    'mail.description as description',
                    'mail.fromMail as fromMail',
                    'mail.fromName as fromName',
                    'mail.subject as subject',
                    'mail.content as content',
                    'mail.contentHTML as contentHTML',
                    'mail.isHTML as isHTML',
                    'mail.attachment as attachment',
                    'mail.systemDependent as systemDependent',
                    'mail.isoCode as isoCode',
                    'shop.id as shopId',
                    'shop.name as shopName'
                ))
                ->from('Shopware\CustomModels\Ticket\Mail', 'mail')
                ->leftJoin('mail.shop', 'shop')
                ->orderBy('shop.name', 'ASC');

        if(!empty($onlyDefaultSubmission)) {
            $builder->where("mail.name = 'sSTANDARD'");
            return $builder;
        }

        if(!empty($locale)) {
            $builder->where("shop.id = :locale")
                    ->setParameter("locale",$locale);
        }
        if(!empty($onlyCustomSubmissions)) {
            $builder->andWhere("mail.systemDependent = 0");
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the right submissions for
     * sending the submission over the system
     *
     * @param $name
     * @param null $shopId
     * @return \Doctrine\ORM\Query
     */
    public function getSystemSubmissionQuery($name, $shopId = null)
    {
        $builder = $this->getSystemSubmissionQueryBuilder($name, $shopId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSystemSubmissionQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $name
     * @param $shopId
     * @internal param $onlyCustomSubmissions
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSystemSubmissionQueryBuilder($name, $shopId)
    {

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('mail'))
                ->from('Shopware\CustomModels\Ticket\Mail', 'mail')
                ->leftJoin('mail.shop', 'shop')
                ->where('mail.systemDependent = 1')
                ->andWhere('mail.name = :name')
                ->setParameter('name', $name)
                ->setFirstResult(0)
                ->setMaxResults(1);


        if(!empty($shopId)) {
            $builder->andWhere("shop.id = :shopId")
                    ->setParameter("shopId",$shopId);
        }
        else {
             $builder->andWhere("shop.id = 1");
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the mail list for the backend module
     * @param $filter
     * @param null $order
     * @param null $offset
     * @param null $limit
     * @return \Doctrine\ORM\Query
     */
    public function getTicketTypeListQuery($filter, $order = null, $offset, $limit)
    {
        $builder = $this->getTicketTypeListQueryBuilder($filter, $order);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTicketTypeListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $filter
     * @param $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTicketTypeListQueryBuilder($filter, $order)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'type.id as id',
            'type.name as name',
            'type.gridColor as gridColor'
        ))
        ->from('Shopware\CustomModels\Ticket\Type', 'type');

        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        if (!empty($filter) && isset($filter[0]['property']) && $filter[0]['property'] == 'free') {
            $builder->andWhere('type.name LIKE :search')
                    ->orWhere('type.gridColor LIKE :search')
                    ->setParameter("search", '%' . $filter[0]["value"] . '%');
        }


        return $builder;
    }
}