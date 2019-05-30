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

use Shopware\Bundle\BenchmarkBundle\Struct\BenchmarkDataResult;
use Shopware\Components\CacheManager;
use Shopware\Models\Benchmark\Repository as BenchmarkRepository;

class Shopware_Controllers_Backend_Benchmark extends Shopware_Controllers_Backend_ExtJs
{
    public function getShopConfigsAction()
    {
        try {
            /** @var BenchmarkRepository $benchmarkRepository */
            $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');
            $benchmarkRepository->synchronizeShops();

            $this->View()->assign([
                'success' => true,
                'data' => array_values($benchmarkRepository->getShopConfigs(true)),
            ]);
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveIndustryAction()
    {
        try {
            /** @var BenchmarkRepository $benchmarkRepository */
            $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');

            $benchmarkConfig = $benchmarkRepository->getConfigForShop($this->request->getParam('shopId'));
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

            $benchmarkConfig = $benchmarkRepository->getConfigForShop($this->request->getParam('shopId'));
            $benchmarkConfig->setActive((bool) $this->request->getParam('active'));

            $benchmarkRepository->save($benchmarkConfig);

            $this->View()->assign('success', true);
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveTypeAction()
    {
        try {
            /** @var BenchmarkRepository $benchmarkRepository */
            $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');

            $benchmarkConfig = $benchmarkRepository->getConfigForShop($this->request->getParam('shopId'));
            $benchmarkConfig->setType($this->request->getParam('type'));

            $benchmarkRepository->save($benchmarkConfig);

            $this->View()->assign('success', true);
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function disableBenchmarkTeaserAction()
    {
        $conn = $this->container->get('dbal_connection');
        $elementId = $conn->fetchColumn('SELECT id FROM s_core_config_elements WHERE name LIKE "benchmarkTeaser"');
        $valueId = $conn->fetchColumn('SELECT id FROM s_core_config_values WHERE element_id = :elementId',
            ['elementId' => $elementId]);

        $data = [
            'element_id' => $elementId,
            'shop_id' => 1,
            'value' => serialize(false),
        ];

        if ($valueId) {
            $conn->update(
                's_core_config_values',
                $data,
                ['id' => $valueId]
            );
        } else {
            $conn->insert('s_core_config_values', $data);
        }
        /** @var CacheManager */
        $cacheManager = $this->get('shopware.cache_manager');
        $cacheManager->clearConfigCache();
    }

    public function checkBenchmarksAction()
    {
        $result = new BenchmarkDataResult();
        $message = null;

        try {
            /** @var BenchmarkDataResult $result */
            $result = $this->get('shopware.benchmark_bundle.benchmark_statistics')->handleTransmission();
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $statisticsResponseSuccess = $result->getStatisticsResponse() !== null;
        $bIResponseSuccess = $result->getBiResponse() !== null;

        $shopId = 0;
        if ($statisticsResponseSuccess) {
            $shopId = $result->getStatisticsResponse()->getShopId();
        }

        if ($bIResponseSuccess) {
            $shopId = $result->getBiResponse()->getShopId();
        }

        $this->View()->assign([
            'success' => $message === null,
            'statistics' => $statisticsResponseSuccess,
            'bi' => $bIResponseSuccess,
            'message' => $message,
            'shopId' => $shopId,
        ]);
    }

    protected function initAcl()
    {
        $this->addAclPermission('getShopConfigs', 'read', 'Insufficient permissions');
        $this->addAclPermission('saveSettings', 'manage', 'Insufficient permissions');
        $this->addAclPermission('saveIndustry', 'manage', 'Insufficient permissions');
        $this->addAclPermission('saveTypeAction', 'manage', 'Insufficient permissions');
        $this->addAclPermission('setActive', 'manage', 'Insufficient permissions');
        $this->addAclPermission('disableBenchmarkTeaser', 'manage', 'Insufficient permissions');
        $this->addAclPermission('checkBenchmarksAction', 'submit', 'Insufficient permissions');
    }
}
