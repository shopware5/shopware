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

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Shopware\Bundle\AccountBundle\Form\Account\AddressFormType;
use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Customer\Address as AddressModel;
use Shopware\Models\Customer\Customer;

/**
 * @extends Shopware_Controllers_Backend_Application<AddressModel>
 */
class Shopware_Controllers_Backend_Address extends Shopware_Controllers_Backend_Application
{
    protected $model = AddressModel::class;

    protected $alias = 'address';

    /**
     * @var AddressServiceInterface
     */
    private $addressService;

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->addressService = $this->get(AddressServiceInterface::class);
    }

    /**
     * Transfers the address attributes to the according legacy tables.
     *
     * Intended to be called from shopware backend via ajax with request parameter `id`.
     *
     * @return void
     */
    public function syncAttributeAction()
    {
        $customerAddressId = $this->Request()->getParam('id');
        if (!$customerAddressId) {
            return;
        }

        $address = $this->getManager()->getRepository(AddressModel::class)->find($customerAddressId);
        if (!$address instanceof AddressModel) {
            return;
        }

        Shopware()->Container()->get(AddressServiceInterface::class)->update($address);
    }

    /**
     * Override save method to make use of symfony form and custom data mapping
     * If isDefaultBillingAddress or isDefaultShippingAddress, the appropriate action will be made
     */
    public function save($data)
    {
        if (!empty($data['id'])) {
            $model = $this->getRepository()->find($data['id']);
        } else {
            $model = new $this->model();
            $this->getManager()->persist($model);
        }

        $data['country'] = $data['countryId'];
        $data['state'] = $data['stateId'] ?? null;

        $form = $this->get('shopware.form.factory')->create(AddressFormType::class, $model);
        $form->submit($data);

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $violation) {
                $errors[] = [
                    'message' => $violation->getMessage(),
                    'property' => $violation->getOrigin()->getName(),
                ];
            }

            return ['success' => false, 'violations' => $errors];
        }

        $model = $form->getData();

        if ($model->getId()) {
            $this->addressService->update($model);
        } else {
            $customer = $this->get(ModelManager::class)->find(Customer::class, $data['user_id']);
            $this->addressService->create($model, $customer);
        }

        if (!empty($data['setDefaultBillingAddress'])) {
            $this->addressService->setDefaultBillingAddress($model);
        }

        if (!empty($data['setDefaultShippingAddress'])) {
            $this->addressService->setDefaultShippingAddress($model);
        }

        $detail = $this->getDetail($model->getId());

        return ['success' => true, 'data' => $detail['data']];
    }

    /**
     * Use address service for deletion
     */
    public function delete($id)
    {
        if (empty($id)) {
            return ['success' => false, 'error' => 'The id parameter contains no value.'];
        }

        $model = $this->getManager()->find($this->model, $id);

        if (!($model instanceof $this->model)) {
            return ['success' => false, 'error' => 'The passed id parameter exists no more.'];
        }

        try {
            $this->addressService->delete($model);
        } catch (\Exception $ex) {
            return ['success' => false, 'error' => $ex->getMessage()];
        }

        return ['success' => true];
    }

    /**
     * {@inheritdoc}
     */
    protected function getListQuery()
    {
        $customerId = (int) $this->Request()->get('customerId');

        if (!$customerId) {
            throw new \RuntimeException('You have to provide a valid customerId.');
        }

        $query = parent::getListQuery();
        $query = $this->addAssociations($query);
        $query->andWhere('IDENTITY(address.customer) = :customerId')
            ->setParameter('customerId', $customerId);

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    protected function getQueryPaginator(QueryBuilder $builder, $hydrationMode = AbstractQuery::HYDRATE_ARRAY)
    {
        /** @var Query<AddressModel> $query */
        $query = $builder->getQuery();
        $query->setHydrationMode($hydrationMode);
        $query->setHint(Query::HINT_INCLUDE_META_COLUMNS, true);

        return $this->getManager()->createPaginator($query);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDetailQuery($id)
    {
        $query = parent::getDetailQuery($id);

        return $this->addAssociations($query);
    }

    private function addAssociations(QueryBuilder $query): QueryBuilder
    {
        $query->addSelect(['country', 'state', 'PARTIAL customer.{id,email}'])
            ->join('address.customer', 'customer')
            ->join('address.country', 'country')
            ->leftJoin('address.state', 'state');

        return $query;
    }
}
