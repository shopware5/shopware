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

namespace Shopware\Components\Api\Resource;

use Shopware\Components\Api\Exception as ApiException;

/**
 * Order API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Order extends Resource
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Order\Order');
    }


    /**
     * Little helper function for the ...ByNumber methods
     * @param $number
     * @return int
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function getIdFromNumber($number)
    {
        if (empty($number)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $orderModel \Shopware\Models\Order\Order */
        $orderModel = $this->getRepository()->findOneBy(array('number' => $number));

        if (!$orderModel) {
            throw new ApiException\NotFoundException("Order by number {$number} not found");
        }

        return $orderModel->getId();
    }

    /**
     * @param string $number
     * @return array|\Shopware\Models\Order\Order
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOneByNumber($number)
    {
        $id = $this->getIdFromNumber($number);
        return $this->getOne($id);
    }

    /**
     * @param int $id
     * @return array|\Shopware\Models\Order\Order
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $filters = array(array('property' => 'orders.id','expression' => '=','value' => $id));
        $builder = $this->getRepository()->getOrdersQueryBuilder($filters);
        /** @var $order \Shopware\Models\Order\Order */
        $order = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$order) {
            throw new ApiException\NotFoundException("Order by id $id not found");
        }

        if (is_array($order)) {
            $order['paymentStatusId'] = $order['cleared'];
            $order['orderStatusId'] = $order['status'];
            unset($order['cleared']);
            unset($order['status']);
        }

        return $order;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param array $criteria
     * @param array $orderBy
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = array(), array $orderBy = array())
    {
        $this->checkPrivilege('read');

        $builder = $this->getRepository()->createQueryBuilder('orders');

        $builder->addFilter($criteria);
        $builder->addOrderBy($orderBy);
        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = $this->getManager()->createPaginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the order data
        $orders = $paginator->getIterator()->getArrayCopy();

        foreach ($orders as &$order) {
            if (is_array($order)) {
                $order['paymentStatusId'] = $order['cleared'];
                $order['orderStatusId'] = $order['status'];
                unset($order['cleared']);
                unset($order['status']);
            }
        }

        return array('data' => $orders, 'total' => $totalResult);
    }

    /**
     * @param string $number
     * @param array $params
     * @return \Shopware\Models\Order\Order
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function updateByNumber($number, $params)
    {
        $id = $this->getIdFromNumber($number);
        return $this->update($id, $params);
    }

    /**
     * @param int $id
     * @param array $params
     * @return \Shopware\Models\Order\Order
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $order \Shopware\Models\Order\Order */
        $order = $this->getRepository()->find($id);

        if (!$order) {
            throw new ApiException\NotFoundException("Order by id $id not found");
        }

        $params = $this->prepareOrderData($params);

        $order->fromArray($params);

        $violations = $this->getManager()->validate($order);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $order;
    }

    /**
     * Helper method to prepare the order data
     *
     * @param array $params
     * @return array
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function prepareOrderData(array $params)
    {
        $params = $this->prepareOrderDetailsData($params);

        $orderWhiteList = array(
            'paymentStatusId',
            'orderStatusId',
            'trackingCode',
            'comment',
            'customerComment',
            'internalComment',
            'transactionId',
            'clearedDate',
            'attribute',
            'details'
        );

        $params = array_intersect_key($params, array_flip($orderWhiteList));

        if (isset($params['orderStatusId'])) {
            $params['orderStatus'] = Shopware()->Models()->getRepository('Shopware\Models\Order\Status')->findOneBy(
                array(
                    'id' => $params['orderStatusId'],
                    'group' => 'state',
                )
            );

            if (empty($params['orderStatus'])) {
                throw new ApiException\NotFoundException(sprintf(
                    "OrderStatus by id %s not found",
                    $params['orderStatusId']
                ));
            }
        }

        if (isset($params['paymentStatusId'])) {
            $params['paymentStatus'] = Shopware()->Models()->getRepository('Shopware\Models\Order\Status')->findOneBy(
                array(
                    'id' => $params['paymentStatusId'],
                    'group' => 'payment',
                )
            );

            if (empty($params['paymentStatus'])) {
                throw new ApiException\NotFoundException(sprintf(
                    "PaymentStatus by id %s not found",
                    $params['paymentStatusId']
                ));
            }
            return $params;
        }

        return $params;
    }

    /**
     * Helper method to prepare the order detail data
     *
     * @param $params
     * @return mixed
     * @throws \Shopware\Components\Api\Exception\NotFoundException| ApiException\CustomValidationException(
     */
    public function prepareOrderDetailsData($params)
    {
        $detailWhiteList = array(
            'status',
            'shipped',
            'id'
        );

        $details = $params['details'];

        if (empty($details)) {
            unset($params['details']);
            return $params;
        }

        foreach ($details as &$detail) {
            // Apply whiteList
            $detail = array_intersect_key($detail, array_flip($detailWhiteList));

            // Technically "articleID" and "articleordernumber" are not unique per orderId,
            // so we cannot use those to identify order positions.
            if (!isset($detail['id']) || empty($detail['id'])) {
                throw new ApiException\CustomValidationException('You need to specify the id of the order positions you want to modify');
            }

            // Check order detail model
            /** @var \Shopware\Models\Order\Detail $detailModel */
            $detailModel = Shopware()->Models()->find('Shopware\Models\Order\Detail', $detail['id']);
            if (!$detailModel) {
                throw new ApiException\NotFoundException(sprintf(
                    "Detail by id %s not found",
                    $detail['id']
                ));
            }

            if (isset($detail['status'])) {
                /** @var $status \Shopware\Models\Order\DetailStatus */
                $status = Shopware()->Models()->find('Shopware\Models\Order\DetailStatus', $detail['status']);

                if (!$status) {
                    throw new ApiException\NotFoundException(sprintf(
                        "DetailStatus by id %s not found",
                        $detail['status']
                    ));
                }

                $detailModel->setStatus($status);
            }

            // Set shipped flag
            if (isset($detail['shipped'])) {
                $detailModel->setShipped($detail['shipped']);
            }

            $detail = $detailModel;
        }

        $params['details'] = $details;
        return $params;
    }
}
