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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\ORMException;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\Repository;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Shop\Shop;

class Shopware_Controllers_Backend_Payment extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var Shopware\Models\Payment\Repository
     */
    protected $repository;

    /**
     * @var Repository
     */
    protected $countryRepository;

    /**
     * Disable template engine for all actions
     */
    public function preDispatch()
    {
        if (!\in_array($this->Request()->getActionName(), ['index', 'load'])) {
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
        $this->repository = $this->get('models')->getRepository(Payment::class);

        $query = $this->repository->getListQuery(null, [
            ['property' => 'payment.active', 'direction' => 'DESC'],
            ['property' => 'payment.position'],
        ]);
        $results = $query->getArrayResult();

        // Translate payments
        // The standard $translationComponent->translatePayments can not be used here since the
        // description may not be overridden. The field is edible and if the translation is
        // shown in the edit field, there is a high chance of a user saving the translation as description.
        $translator = $this->get(Shopware_Components_Translation::class)->getObjectTranslator('config_payment');
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
            $repository = $this->get('models')->getRepository(Payment::class);
            $existingModel = $repository->findByName($params['name']);
            if ($existingModel) {
                throw new ORMException('The name is already in use.');
            }
            if ($params['source'] == 0) {
                $params['source'] = null;
            }

            $paymentModel = new Payment();
            $countries = $params['countries'] ?? [];
            $countryArray = [];
            foreach ($countries as $country) {
                $countryArray[] = $this->get('models')->find(Country::class, $country['id']);
            }
            $params['countries'] = $countryArray;

            $shops = $params['shops'] ?? [];
            $shopArray = [];
            foreach ($shops as $shop) {
                $shopArray[] = $this->get('models')->find(Shop::class, $shop['id']);
            }
            $params['shops'] = $shopArray;

            $paymentModel->fromArray($params);

            $this->get('models')->persist($paymentModel);
            $this->get('models')->flush();

            $params['id'] = $paymentModel->getId();
            $this->View()->assign(['success' => true, 'data' => $params]);
        } catch (ORMException $e) {
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
            $payment = $this->get('models')->find(Payment::class, $id);
            $action = $payment->getAction();
            $data = $this->Request()->getParams();
            $data['surcharge'] = str_replace(',', '.', $data['surcharge'] ?? '');
            $data['debitPercent'] = str_replace(',', '.', $data['debitPercent'] ?? '');

            $countries = new ArrayCollection();
            if (!empty($data['countries'])) {
                // Clear all countries, to save the old and new ones then
                $payment->getCountries()->clear();
                foreach ($data['countries'] as $country) {
                    $model = $this->get('models')->find(Country::class, $country['id']);
                    $countries->add($model);
                }
                $data['countries'] = $countries;
            }

            $shops = new ArrayCollection();
            if (!empty($data['shops'])) {
                // Clear all shops, to save the old and new ones then
                $payment->getShops()->clear();
                foreach ($data['shops'] as $shop) {
                    $model = $this->get('models')->find(Shop::class, $shop['id']);
                    $shops->add($model);
                }
                $data['shops'] = $shops;
            }
            $data['surchargeString'] = $this->filterSurchargeString($data['surchargeString'] ?? '', $data['countries'] ?? []);

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

            $this->get('models')->persist($payment);
            $this->get('models')->flush();

            if (!empty($data['active'])) {
                $data['iconCls'] = 'sprite-tick';
            } else {
                $data['iconCls'] = 'sprite-cross';
            }

            $this->View()->assign(['success' => true, 'data' => $data]);
        } catch (ORMException $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    public function deletePaymentAction()
    {
        if (!$this->Request()->isPost()) {
            $this->View()->assign(['success' => false, 'errorMsg' => 'Empty Post Request']);

            return;
        }
        $repository = $this->get('models')->getRepository(Payment::class);
        $id = $this->Request()->get('id');
        $model = $repository->find($id);
        if ($model->getSource() == 1) {
            try {
                $this->get('models')->remove($model);
                $this->get('models')->flush();
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
     * @return Repository
     */
    private function getCountryRepository()
    {
        if ($this->countryRepository === null) {
            $this->countryRepository = $this->get('models')->getRepository(Country::class);
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
            $result['text'] = $result['translatedDescription'];
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
     * @param Country[] $countries
     */
    private function filterSurchargeString(string $surchargeString, array $countries): string
    {
        $buffer = [];
        $surcharges = explode(';', $surchargeString);
        $isoCodes = [];

        foreach ($countries as $country) {
            $isoCodes[] = $country->getIso();
        }

        foreach ($surcharges as $surcharge) {
            $keys = explode(':', $surcharge);
            if (\in_array($keys[0], $isoCodes)) {
                $buffer[] = $surcharge;
            }
        }

        return implode(';', $buffer);
    }
}
