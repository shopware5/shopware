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

use Shopware\Bundle\BenchmarkBundle\Service\TemplateCachingHandler;
use Shopware\Models\Benchmark\BenchmarkConfig;
use Shopware\Models\Benchmark\Repository as BenchmarkRepository;
use Shopware\Models\Menu\Menu;

class Shopware_Controllers_Backend_BenchmarkOverview extends Shopware_Controllers_Backend_ExtJs implements \Shopware\Components\CSRFWhitelistAware
{
    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return ['index', 'render', 'saveIndustry', 'getShops'];
    }

    public function indexAction()
    {
        $shopId = $this->getShopId();

        /** @var BenchmarkRepository $benchmarkRepository */
        $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $config = $benchmarkRepository->getConfigForShop($shopId);

        $this->handleSettings($config);
    }

    public function renderAction()
    {
        $this->get('plugins')->Controller()->ViewRenderer()->setNoRender(true);
        $this->Front()->Plugins()->Json()->setRenderer(false);

        /** @var BenchmarkRepository $benchmarkRepository */
        $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $config = $benchmarkRepository->getConfigForShop($this->getShopId());

        if ($this->hasOutdatedStatistics($config->getLastReceived())) {
            $this->redirect([
                'controller' => 'BenchmarkOverview',
                'action' => 'index',
                'shopId' => $this->getShopId(),
            ]);

            return;
        }

        echo $config->getCachedTemplate();
    }

    public function saveIndustryAction()
    {
        $config = $this->request->getParam('config');

        /** @var BenchmarkRepository $benchmarkRepository */
        $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $benchmarkRepository->saveShopConfigs($config);

        $this->enableMenu();
        $this->View()->assign('success', true);
    }

    public function getShopsAction()
    {
        /** @var BenchmarkRepository $benchmarkRepository */
        $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');

        $shops = $benchmarkRepository->getShopsWithValidTemplate();
        $currentShop = $this->getShopId();

        $shops[$currentShop]['active'] = 1;

        $widgetsAllowed = (int) $this->_isAllowed('swag-bi-base', 'widgets');

        $this->View()->assign([
            'shops' => $shops,
            'shopSwitchUrl' => $this->Front()->Router()->assemble([
                'controller' => 'BenchmarkOverview',
                'action' => 'render',
                'shopId' => 'replaceShopId',
            ]) . '?widgetAllowed=' . $widgetsAllowed,
        ]);
    }

    protected function initAcl()
    {
        $this->addAclPermission('index', 'read', 'Insufficient permissions');
        $this->addAclPermission('render', 'read', 'Insufficient permissions');
        $this->addAclPermission('setIndustry', 'manage', 'Insufficient permissions');
        $this->addAclPermission('getShops', 'read', 'Insufficient permissions');
    }

    private function handleSettings(BenchmarkConfig $config = null)
    {
        $backendLanguage = $this->get('auth')->getIdentity()->locale->getId() === 1 ? 'de' : 'en';

        /** @var BenchmarkRepository $benchmarkRepository */
        $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');

        if (!$config || $benchmarkRepository->getConfigsCount() === 0) {
            $this->redirect([
                'controller' => 'BenchmarkLocalOverview',
                'action' => 'render',
                'template' => 'start',
                'lang' => $this->request->getParam('lang', $backendLanguage),
            ]);

            return;
        }

        if ($this->hasFreshStatistics($config->getLastReceived())) {
            $this->loadCachedFile();

            return;
        }

        if (!$config->isActive() || $this->hasOutdatedStatistics($config->getLastReceived())) {
            $this->redirect([
                'controller' => 'BenchmarkLocalOverview',
                'action' => 'render',
                'template' => 'waiting',
                'lang' => $this->request->getParam('lang', $backendLanguage),
            ]);

            return;
        }

        $this->loadCachedFile();
    }

    /**
     * Checks if "lastReceived" is younger than 24 hours.
     *
     * @return bool
     */
    private function hasFreshStatistics(\DateTimeInterface $lastReceived)
    {
        $today = new \DateTime('now');

        $interval = new \DateInterval('PT1H');

        $periods = new \DatePeriod($lastReceived, $interval, $today);
        $hours = iterator_count($periods);

        return $hours < 24;
    }

    /**
     * Checks if "lastReceived" is older than 7 days.
     *
     * @return bool
     */
    private function hasOutdatedStatistics(\DateTimeInterface $lastReceived)
    {
        $today = new \DateTime('now');

        $interval = new \DateInterval('P1D');

        $periods = new \DatePeriod($lastReceived, $interval, $today);
        $days = iterator_count($periods);

        return $days > 7;
    }

    private function loadCachedFile()
    {
        /** @var TemplateCachingHandler $cachingHandler */
        $cachingHandler = $this->get('shopware.benchmark_bundle.components.template_caching_handler');
        $shopId = $this->getShopId();

        if ($cachingHandler->isTemplateCached($shopId)) {
            $link = $this->get('router')->assemble([
                'controller' => 'BenchmarkOverview',
                'action' => 'render',
                'shopId' => $shopId,
            ]);

            $widgetsAllowed = (int) $this->_isAllowed('swag-bi-base', 'widgets');

            $this->redirect($link . '?widgetAllowed=' . $widgetsAllowed);

            return;
        }

        $this->redirect([
            'controller' => 'BenchmarkLocalOverview',
            'action' => 'render',
            'template' => 'waiting',
            'lang' => $this->request->getParam('lang', 'de'),
        ]);
    }

    private function enableMenu()
    {
        $em = $this->get('models');
        $repo = $em->getRepository(Menu::class);

        /** @var Menu|null $menuEntry */
        $menuEntry = $repo->findOneBy(['controller' => 'Benchmark', 'action' => 'Settings']);
        if ($menuEntry) {
            $menuEntry->setActive(true);
            $em->persist($menuEntry);
            $em->flush();
        }
    }

    /**
     * @return int
     */
    private function getShopId()
    {
        $shopId = (int) $this->request->getParam('shopId');

        if (!$shopId) {
            $shopId = $this->get('models')->getRepository(\Shopware\Models\Shop\Shop::class)->getActiveDefault()->getId();
        }

        return $shopId;
    }
}
