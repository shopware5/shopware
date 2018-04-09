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
use Shopware\Bundle\BenchmarkBundle\Repository\ConfigRepositoryInterface;

class Shopware_Controllers_Backend_Benchmark extends Shopware_Controllers_Backend_ExtJs
{
    public function loadSettingsAction()
    {
        /** @var ConfigRepositoryInterface $configRepository */
        $configRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $settings = $configRepository->loadSettings();

        $settings['business'] = (int) $settings['business'];

        if ($settings['lastOrderId']) {
            $settings['lastOrderNumber'] = $this->getOrderNumberFromOrderId($settings['lastOrderId']);
        }

        $this->View()->assign('data', $settings);
    }

    public function saveSettingsAction()
    {
        try {
            /** @var ConfigRepositoryInterface $configRepository */
            $configRepository = $this->get('shopware.benchmark_bundle.repository.config');

            $configRepository->saveSettings((int) $this->request->getParam('ordersBatchSize'));

            $this->View()->assign('success', true);
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveBusinessAction()
    {
        try {
            /** @var ConfigRepositoryInterface $configRepository */
            $configRepository = $this->get('shopware.benchmark_bundle.repository.config');

            $configRepository->saveBusiness((int) $this->request->getParam('business'));

            $this->View()->assign('success', true);
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function setActiveAction()
    {
        try {
            /** @var ConfigRepositoryInterface $configRepository */
            $configRepository = $this->get('shopware.benchmark_bundle.repository.config');

            $configRepository->setActive((bool) $this->request->getParam('active'));

            $this->View()->assign('success', true);
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @param int $orderId
     *
     * @return string
     */
    private function getOrderNumberFromOrderId($orderId)
    {
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();

        return $queryBuilder->select('orders.ordernumber')
            ->from('s_order', 'orders')
            ->where('orders.id = :orderId')
            ->setParameter(':orderId', $orderId)
            ->execute()
            ->fetchColumn();
    }
}
