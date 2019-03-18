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
use Shopware\Models\Country\Country as CountryModel;
use Shopware\Models\Payment\Payment as PaymentModel;
use Shopware\Models\Plugin\Plugin;

/**
 * Payment API Resource
 */
class PaymentMethods extends Resource
{
    /**
     * @return \Shopware\Models\Payment\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(PaymentModel::class);
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array|PaymentModel
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $filters = [['property' => 'payment.id', 'expression' => '=', 'value' => $id]];
        $query = $this->getRepository()->getListQuery($filters, [], 0, 1);

        /** @var PaymentModel $media */
        $payment = $query->getOneOrNullResult($this->getResultMode());

        if (!$payment) {
            throw new ApiException\NotFoundException(sprintf('Payment by id %d not found', $id));
        }

        return $payment;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $filter = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

        $query = $this->getRepository()->getListQuery($filter, $orderBy, $offset, $limit);
        $query->setHydrationMode($this->resultMode);

        $paginator = $this->getManager()->createPaginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the category data
        $payments = $paginator->getIterator()->getArrayCopy();

        return ['data' => $payments, 'total' => $totalResult];
    }

    /**
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Exception
     *
     * @return PaymentModel
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $payment = new PaymentModel();

        $payment->setAdditionalDescription('');

        $params = $this->preparePaymentData($params);

        $payment->fromArray($params);

        $violations = $this->getManager()->validate($payment);

        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($payment);
        $this->flush();

        return $payment;
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     *
     * @return PaymentModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var PaymentModel|null $payment */
        $payment = $this->getRepository()->find($id);

        if (!$payment) {
            throw new ApiException\NotFoundException(sprintf('Payment by id "%d" not found', $id));
        }

        $params = $this->preparePaymentData($params);

        $payment->fromArray($params);

        $violations = $this->getManager()->validate($payment);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $payment;
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return PaymentModel
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var PaymentModel|null $payment */
        $payment = $this->getRepository()->find($id);

        if (!$payment) {
            throw new ApiException\NotFoundException("Payment by id $id not found");
        }

        $this->getManager()->remove($payment);
        $this->flush();

        return $payment;
    }

    /**
     * @param array $params
     *
     * @throws ApiException\NotFoundException
     *
     * @return array
     */
    protected function preparePaymentData($params)
    {
        $paymentWhiteList = [
            'name',
            'description',
            'template',
            'hide',
            'additionalDescription',
            'debitPercent',
            'surcharge',
            'surchargeString',
            'position',
            'active',
            'esdActive',
            'mobileInactive',
            'hideProspect',
            'action',
            'pluginId',
            'countries',
            'shops',
            'attribute',
        ];

        $params = array_intersect_key($params, array_flip($paymentWhiteList));

        if (isset($params['countries'])) {
            foreach ($params['countries'] as &$country) {
                $countryModel = $this->getContainer()->get('models')->find(CountryModel::class, $country['countryId']);
                if (!$countryModel) {
                    throw new ApiException\NotFoundException(sprintf(
                        'Country by id %d not found',
                        $country['countryId']
                    ));
                }

                $country = $countryModel;

                unset($country);
            }
        }

        if (isset($params['shops'])) {
            foreach ($params['shops'] as &$shop) {
                $shopModel = $this->getContainer()->get('models')->find(\Shopware\Models\Shop\Shop::class, $shop['shopId']);
                if (!$shopModel) {
                    throw new ApiException\NotFoundException(sprintf(
                        'Shop by id %d not found',
                        $shop['shopId']
                    ));
                }

                $shop = $shopModel;

                unset($shop);
            }
        }

        if (isset($params['pluginId'])) {
            $params['plugin'] = $this->getContainer()->get('models')->find(Plugin::class, $params['pluginId']);
            if (empty($params['plugin'])) {
                throw new ApiException\NotFoundException(sprintf(
                    'plugin by id %s not found',
                    $params['pluginId']
                ));
            }
        }

        return $params;
    }
}
