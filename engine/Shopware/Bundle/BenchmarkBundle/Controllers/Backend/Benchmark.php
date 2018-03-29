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
class Shopware_Controllers_Backend_Benchmark extends Shopware_Controllers_Backend_ExtJs
{
    public function renderAction()
    {
        echo '<html><body>Placeholder!</body></html>';
        exit();
    }

    public function loadSettingsAction()
    {
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();
        $settings = $queryBuilder->select([
                'settings.active',
                'settings.last_sent as lastSent',
                'settings.last_order_id as lastOrderId',
                'settings.orders_batch_size as ordersBatchSize',
                'settings.business',
            ])
            ->from('s_benchmark_config', 'settings')
            ->execute()
            ->fetch();

        $settings['business'] = (int) $settings['business'];

        if ($settings['lastOrderId']) {
            $settings['lastOrderNumber'] = $this->getOrderNumberFromOrderId($settings['lastOrderId']);
        }

        $this->View()->assign('data', $settings);
    }

    public function saveSettingsAction()
    {
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();

        try {
            $queryBuilder->update('s_benchmark_config', 'config')
                ->set('config.orders_batch_size', ':ordersBatchSize')
                ->setParameters([
                    ':ordersBatchSize' => $this->request->getParam('ordersBatchSize'),
                ])
                ->execute();

            $this->View()->assign('success', true);
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveBusinessAction()
    {
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();

        try {
            $queryBuilder->update('s_benchmark_config', 'config')
                ->set('config.business', ':business')
                ->setParameters([
                    ':business' => $this->request->getParam('business'),
                ])
                ->execute();

            $this->View()->assign('success', true);
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function setActiveAction()
    {
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();

        try {
            $queryBuilder->update('s_benchmark_config', 'config')
                ->set('config.active', ':active')
                ->setParameters([
                    ':active' => $this->request->getParam('active'),
                ])
                ->execute();

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
