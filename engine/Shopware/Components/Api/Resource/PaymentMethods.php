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

namespace Shopware\Components\Api\Resource;

use Exception;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country as CountryModel;
use Shopware\Models\Payment\Payment as PaymentModel;
use Shopware\Models\Payment\Repository;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop as ShopModel;

/**
 * Payment API Resource
 */
class PaymentMethods extends Resource
{
    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(PaymentModel::class);
    }

    /**
     * @param int $id
     *
     * @throws ParameterMissingException
     * @throws NotFoundException
     *
     * @return array|PaymentModel
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $filters = [['property' => 'payment.id', 'expression' => '=', 'value' => $id]];
        $query = $this->getRepository()->getListQuery($filters, [], 0, 1);

        /** @var PaymentModel|null $payment */
        $payment = $query->getOneOrNullResult($this->getResultMode());

        if (!$payment) {
            throw new NotFoundException(sprintf('Payment by id %d not found', $id));
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

        // returns the total count of the query
        $totalResult = $paginator->count();

        // returns the category data
        $payments = iterator_to_array($paginator);

        return ['data' => $payments, 'total' => $totalResult];
    }

    /**
     * @throws ValidationException
     * @throws Exception
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
            throw new ValidationException($violations);
        }

        $this->getManager()->persist($payment);
        $this->flush();

        return $payment;
    }

    /**
     * @param int $id
     *
     * @throws ValidationException
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return PaymentModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        /** @var PaymentModel|null $payment */
        $payment = $this->getRepository()->find($id);

        if (!$payment) {
            throw new NotFoundException(sprintf('Payment by id "%d" not found', $id));
        }

        $params = $this->preparePaymentData($params);

        $payment->fromArray($params);

        $violations = $this->getManager()->validate($payment);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->flush();

        return $payment;
    }

    /**
     * @param int $id
     *
     * @throws ParameterMissingException
     * @throws NotFoundException
     *
     * @return PaymentModel
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        /** @var PaymentModel|null $payment */
        $payment = $this->getRepository()->find($id);

        if (!$payment) {
            throw new NotFoundException("Payment by id $id not found");
        }

        $this->getManager()->remove($payment);
        $this->flush();

        return $payment;
    }

    /**
     * @param array $params
     *
     * @throws NotFoundException
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
                $countryModel = $this->getContainer()->get(ModelManager::class)->find(CountryModel::class, $country['countryId']);
                if (!$countryModel) {
                    throw new NotFoundException(sprintf('Country by id %d not found', $country['countryId']));
                }

                $country = $countryModel;

                unset($country);
            }
        }

        if (isset($params['shops'])) {
            foreach ($params['shops'] as &$shop) {
                $shopModel = $this->getContainer()->get(ModelManager::class)->find(ShopModel::class, $shop['shopId']);
                if (!$shopModel) {
                    throw new NotFoundException(sprintf('Shop by id %d not found', $shop['shopId']));
                }

                $shop = $shopModel;

                unset($shop);
            }
        }

        if (isset($params['pluginId'])) {
            $params['plugin'] = $this->getContainer()->get(ModelManager::class)->find(Plugin::class, $params['pluginId']);
            if (empty($params['plugin'])) {
                throw new NotFoundException(sprintf('plugin by id %s not found', $params['pluginId']));
            }
        }

        return $params;
    }
}
