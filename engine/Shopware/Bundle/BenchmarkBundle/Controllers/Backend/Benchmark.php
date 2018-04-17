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
use Shopware\Models\Benchmark\Repository as BenchmarkRepository;

class Shopware_Controllers_Backend_Benchmark extends Shopware_Controllers_Backend_ExtJs
{
    public function loadSettingsAction()
    {
        /** @var BenchmarkRepository $benchmarkRepository */
        $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $benchmarkConfig = $benchmarkRepository->getMainConfig();

        $settings = [
            'lastOrderNumber' => null,
            'active' => $benchmarkConfig->isActive() ? 1 : 0,
            'lastSent' => $benchmarkConfig->getLastSent()->format('Y-m-d H:i:s'),
            'lastReceived' => $benchmarkConfig->getLastReceived()->format('Y-m-d H:i:s'),
            'ordersBatchSize' => $benchmarkConfig->getOrdersBatchSize(),
            'industry' => $benchmarkConfig->getIndustry(),
            'termsAccepted' => $benchmarkConfig->isTermsAccepted() ? 1 : 0,
        ];

        if ($benchmarkConfig->getLastOrderId()) {
            $settings['lastOrderNumber'] = $this->getOrderNumberFromOrderId($benchmarkConfig->getLastOrderId());
        }

        $this->View()->assign('data', $settings);
    }

    public function saveSettingsAction()
    {
        try {
            /** @var BenchmarkRepository $benchmarkRepository */
            $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');

            $benchmarkConfig = $benchmarkRepository->getMainConfig();
            $benchmarkConfig->setOrdersBatchSize((int) $this->request->getParam('ordersBatchSize'));

            $benchmarkRepository->save($benchmarkConfig);

            $this->View()->assign('success', true);
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveIndustryAction()
    {
        try {
            /** @var BenchmarkRepository $benchmarkRepository */
            $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');

            $benchmarkConfig = $benchmarkRepository->getMainConfig();
            $benchmarkConfig->setIndustry((int) $this->request->getParam('industry'));

            $benchmarkRepository->save($benchmarkConfig);

            $this->View()->assign('success', true);
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function setActiveAction()
    {
        try {
            /** @var BenchmarkRepository $benchmarkRepository */
            $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');

            $benchmarkConfig = $benchmarkRepository->getMainConfig();
            $benchmarkConfig->setActive((bool) $this->request->getParam('active'));

            $benchmarkRepository->save($benchmarkConfig);

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
