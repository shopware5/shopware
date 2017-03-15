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

use Shopware\Bundle\AccountBundle\Form\Account\AddressFormType;
use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Models\Customer\Customer;

class Shopware_Controllers_Backend_Address extends Shopware_Controllers_Backend_Application
{
    protected $model = '\Shopware\Models\Customer\Address';
    protected $alias = 'address';

    /**
     * @var AddressServiceInterface
     */
    private $addressService;

    /**
     * @inheritdoc
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->addressService = $this->get('shopware_account.address_service');
    }

    /**
     * @inheritdoc
     */
    protected function getListQuery()
    {
        $customerId = (int) $this->Request()->get('customerId');

        if (!$customerId) {
            throw new \RuntimeException("You have to provide a valid customerId.");
        }

        $query = parent::getListQuery();
        $query = $this->addAssociations($query);
        $query->andWhere('IDENTITY(address.customer) = :customerId')
            ->setParameter('customerId', $customerId);

        return $query;
    }

    /**
     * @inheritdoc
     */
    protected function getQueryPaginator(
        \Doctrine\ORM\QueryBuilder $builder,
        $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY
    ) {
        $query = $builder->getQuery();
        $query->setHydrationMode($hydrationMode);
        $query->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true);
        return $this->getManager()->createPaginator($query);
    }

    /**
     * @param \Shopware\Components\Model\QueryBuilder $query
     * @return \Shopware\Components\Model\QueryBuilder
     */
    private function addAssociations(\Shopware\Components\Model\QueryBuilder $query)
    {
        $query
            ->addSelect(['country', 'state', 'PARTIAL customer.{id,email}'])
            ->join('address.customer', 'customer')
            ->join('address.country', 'country')
            ->leftJoin('address.state', 'state');

        return $query;
    }

    /**
     * @inheritdoc
     */
    protected function getDetailQuery($id)
    {
        $query = parent::getDetailQuery($id);
        $query = $this->addAssociations($query);

        return $query;
    }

    /**
     * Transfers the address attributes to the according legacy tables.
     *
     * Intended to be called from shopware backend via ajax with request parameter `id`.
     */
    public function syncAttributeAction()
    {
        $customerAddressId = $this->Request()->getParam('id');
        if (!$customerAddressId) {
            return;
        }
        $address = $this->getManager()->getRepository('Shopware\Models\Customer\Address')->find($customerAddressId);
        $this->container->get('shopware_account.address_service')->update($address);
    }

    /**
     * Override save method to make use of symfony form and custom data mapping
     * If isDefaultBillingAddress or isDefaultShippingAddress, the appropriate action will be made
     *
     * @param array $data
     * @return array
     */
    public function save($data)
    {
        /**@var $model \Shopware\Models\Customer\Address */
        if (!empty($data['id'])) {
            $model = $this->getRepository()->find($data['id']);
        } else {
            $model = new $this->model();
            $this->getManager()->persist($model);
        }

        $data['country'] = $data['country_id'];
        $data['state'] = $data['state_id'];

        /** @var \Symfony\Component\Form\FormInterface $form */
        $form = $this->get('shopware.form.factory')->create(AddressFormType::class, $model);
        $form->submit($data);

        if (!$form->isValid()) {
            $errors = array();
            foreach ($form->getErrors(true) as $violation) {
                $errors[] = array(
                    'message' => $violation->getMessage(),
                    'property' => $violation->getOrigin()->getName()
                );
            }

            return array('success' => false, 'violations' => $errors);
        }

        $model = $form->getData();

        if ($model->getId()) {
            $this->addressService->update($model);
        } else {
            /** @var Customer $customer */
            $customer = $this->get('models')->find(Customer::class, $data['user_id']);
            $this->addressService->create($model, $customer);
        }

        if (!empty($data['setDefaultBillingAddress'])) {
            $this->addressService->setDefaultBillingAddress($model);
        }

        if (!empty($data['setDefaultShippingAddress'])) {
            $this->addressService->setDefaultShippingAddress($model);
        }

        $detail = $this->getDetail($model->getId());

        return array('success' => true, 'data' => $detail['data']);
    }

    /**
     * Use address service for deletion
     *
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        if (empty($id)) {
            return array('success' => false, 'error' => 'The id parameter contains no value.');
        }

        $model = $this->getManager()->find($this->model, $id);

        if (!($model instanceof $this->model)) {
            return array('success' => false, 'error' => 'The passed id parameter exists no more.');
        }

        try {
            $this->addressService->delete($model);
        } catch (\Exception $ex) {
            return array('success' => false, 'error' => $ex->getMessage());
        }

        return array('success' => true);
    }
}
