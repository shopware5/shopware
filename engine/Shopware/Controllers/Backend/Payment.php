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

use Shopware\Models\Country\Country;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Shop\Shop;

class Shopware_Controllers_Backend_Payment extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var Shopware\Models\Payment\Repository
     */
    protected $repository;

    /**
     * @var \Shopware\Models\Country\Repository
     */
    protected $countryRepository;

    /**
     * Disable template engine for all actions
     */
    public function preDispatch()
    {
        if (!in_array($this->Request()->getActionName(), ['index', 'load'])) {
            $this->Front()->Plugins()->Json()->setRenderer(true);
        }
    }

    public function initAcl()
    {
        $this->addAclPermission('getPayments', 'read', "You're not allowed to see the payments.");
        $this->addAclPermission('createPayments', 'create', "You're not allowed to create a payment.");
        $this->addAclPermission('updatePayments', 'update', "You're not allowed to update the payment.");
        $this->addAclPermission('deletePayment', 'delete', "You're not allowed to delete the payment.");
    }

    /**
     * Main-Method to get all payments and its countries and subshops
     * The data is additionally formatted, so additional-information are also given
     */
    public function getPaymentsAction()
    {
        $this->repository = Shopware()->Models()->getRepository(Payment::class);

        $query = $this->repository->getListQuery();
        $results = $query->getArrayResult();

        // Translate payments
        // The standard $translationComponent->translatePayments can not be used here since the
        // description may not be overridden. The field is edible and if the translation is
        // shown in the edit field, there is a high chance of a user saving the translation as description.
        $translator = $this->get('translation')->getObjectTranslator('config_payment');
        $results = array_map(function ($payment) use ($translator) {
            return $translator->translateObjectProperty($payment, 'description', 'translatedDescription', $payment['description']);
        }, $results);

        $results = $this->formatResult($results);

        $this->View()->assign(['success' => true, 'data' => $results]);
    }

    /**
     * Function to get all inactive and active countries
     */
    public function getCountriesAction()
    {
        $result = $this->getCountryRepository()
            ->getCountriesQuery(null, $this->Request()->getParam('sort', []))
            ->getArrayResult();
        $this->View()->assign(['success' => true, 'data' => $result]);
    }

    /**
     * Function to create a new payment
     */
    public function createPaymentsAction()
    {
        try {
            $params = $this->Request()->getParams();
            unset($params['action']);
            $repository = Shopware()->Models()->getRepository(\Shopware\Models\Payment\Payment::class);
            $existingModel = $repository->findByName($params['name']);

            if ($existingModel) {
                throw new \Doctrine\ORM\ORMException('The name is already in use.');
            }
            if ($params['source'] == 0) {
                $params['source'] = null;
            }

            $paymentModel = new Payment();
            $countries = $params['countries'];
            $countryArray = [];
            foreach ($countries as $country) {
                $countryArray[] = Shopware()->Models()->find(\Shopware\Models\Country\Country::class, $country['id']);
            }
            $params['countries'] = $countryArray;

            $shops = $params['shops'];
            $shopArray = [];
            foreach ($shops as $shop) {
                $shopArray[] = Shopware()->Models()->find(\Shopware\Models\Shop\Shop::class, $shop['id']);
            }
            $params['shops'] = $shopArray;

            $paymentModel->fromArray($params);

            Shopware()->Models()->persist($paymentModel);
            Shopware()->Models()->flush();

            $params['id'] = $paymentModel->getId();
            $this->View()->assign(['success' => true, 'data' => $params]);
        } catch (\Doctrine\ORM\ORMException $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Function to update a payment with its countries, shops and surcharges
     * The mapping for the mapping-tables is automatically created
     */
    public function updatePaymentsAction()
    {
        try {
            $id = $this->Request()->getParam('id');
            /** @var Payment $payment */
            $payment = Shopware()->Models()->find(Payment::class, $id);
            $action = $payment->getAction();
            $data = $this->Request()->getParams();
            $data['surcharge'] = str_replace(',', '.', $data['surcharge']);
            $data['debitPercent'] = str_replace(',', '.', $data['debitPercent']);

            $countries = new \Doctrine\Common\Collections\ArrayCollection();
            if (!empty($data['countries'])) {
                // Clear all countries, to save the old and new ones then
                $payment->getCountries()->clear();
                foreach ($data['countries'] as $country) {
                    $model = Shopware()->Models()->find(Country::class, $country['id']);
                    $countries->add($model);
                }
                $data['countries'] = $countries;
            }

            $shops = new \Doctrine\Common\Collections\ArrayCollection();
            if (!empty($data['shops'])) {
                // Clear all shops, to save the old and new ones then
                $payment->getShops()->clear();
                foreach ($data['shops'] as $shop) {
                    $model = Shopware()->Models()->find(Shop::class, $shop['id']);
                    $shops->add($model);
                }
                $data['shops'] = $shops;
            }
            $data['surchargeString'] = $this->filterSurchargeString($data['surchargeString'], $data['countries']);

            $payment->fromArray($data);

            // A default parameter "action" is sent
            // To prevent "updatePayment" written into the database
            if (empty($action)) {
                $payment->setAction('');
            } else {
                $payment->setAction($action);
            }

            // ExtJS transforms null to 0
            if ($payment->getSource() == 0) {
                $payment->setSource(null);
            }
            if ($payment->getPluginId() == 0) {
                $payment->setPluginId(null);
            }

            Shopware()->Models()->persist($payment);
            Shopware()->Models()->flush();

            if ($data['active']) {
                $data['iconCls'] = 'sprite-tick';
            } else {
                $data['iconCls'] = 'sprite-cross';
            }

            $this->View()->assign(['success' => true, 'data' => $data]);
        } catch (\Doctrine\ORM\ORMException $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    public function deletePaymentAction()
    {
        if (!$this->Request()->isPost()) {
            $this->View()->assign(['success' => false, 'errorMsg' => 'Empty Post Request']);

            return;
        }
        $repository = Shopware()->Models()->getRepository(Payment::class);
        $id = $this->Request()->get('id');
        /** @var Payment $model */
        $model = $repository->find($id);
        if ($model->getSource() == 1) {
            try {
                Shopware()->Models()->remove($model);
                Shopware()->Models()->flush();
                $this->View()->assign(['success' => true]);
            } catch (Exception $e) {
                $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
            }
        } else {
            $this->View()->assign(['success' => false, 'errorMsg' => 'Default payments can not be deleted']);
        }
    }

    /**
     * Internal helper function to get access to the country repository.
     *
     * @return \Shopware\Models\Country\Repository
     */
    private function getCountryRepository()
    {
        if ($this->countryRepository === null) {
            $this->countryRepository = Shopware()->Models()->getRepository(Country::class);
        }

        return $this->countryRepository;
    }

    /**
     * Helper method to
     * - set the correct icon
     * - match the surcharges to the countries
     *
     * @param array $results
     *
     * @return array
     */
    private function formatResult($results)
    {
        $surchargeCollection = [];
        foreach ($results as &$result) {
            if ($result['active'] == 1) {
                $result['iconCls'] = 'sprite-tick-small';
            } else {
                $result['iconCls'] = 'sprite-cross-small';
            }
            $result['text'] = $result['translatedDescription'] . ' (' . $result['id'] . ')';
            $result['leaf'] = true;

            // Matches the surcharges with the countries
            if (!empty($result['surchargeString'])) {
                $surchargeString = $result['surchargeString'];
                $surcharges = explode(';', $surchargeString);
                $specificSurcharges = [];
                foreach ($surcharges as $surcharge) {
                    $specificSurcharges[] = explode(':', $surcharge);
                }
                $surchargeCollection[$result['name']] = $specificSurcharges;
            }
            if (empty($surchargeCollection[$result['name']])) {
                $surchargeCollection[$result['name']] = [];
            }
            foreach ($result['countries'] as &$country) {
                foreach ($surchargeCollection[$result['name']] as $singleSurcharge) {
                    if ($country['iso'] == $singleSurcharge[0]) {
                        $country['surcharge'] = $singleSurcharge[1];
                    }
                }
            }
        }

        return $results;
    }

    /**
     * @param string                             $surchargeString
     * @param \Shopware\Models\Country\Country[] $countries
     *
     * @return string
     */
    private function filterSurchargeString($surchargeString, $countries)
    {
        $buffer = [];
        $surcharges = explode(';', $surchargeString);
        $isoCodes = [];

        foreach ($countries as $country) {
            $isoCodes[] = $country->getIso();
        }

        foreach ($surcharges as $surcharge) {
            $keys = explode(':', $surcharge);
            if (in_array($keys[0], $isoCodes)) {
                $buffer[] = $surcharge;
            }
        }

        return implode(';', $buffer);
    }
}
