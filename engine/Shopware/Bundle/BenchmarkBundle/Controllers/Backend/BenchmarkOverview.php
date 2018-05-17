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
        return ['index', 'render', 'setIndustry'];
    }

    public function indexAction()
    {
        /** @var BenchmarkRepository $benchmarkRepository */
        $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $config = $benchmarkRepository->getMainConfig();

        $this->handleSettings($config);
    }

    public function renderAction()
    {
        $this->get('plugins')->Controller()->ViewRenderer()->setNoRender(true);
        $this->Front()->Plugins()->Json()->setRenderer(false);

        /** @var BenchmarkRepository $benchmarkRepository */
        $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $config = $benchmarkRepository->getMainConfig();

        echo $config->getCachedTemplate();
    }

    public function setIndustryAction()
    {
        /** @var BenchmarkRepository $benchmarkRepository */
        $benchmarkRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $config = $benchmarkRepository->getMainConfig();
        $config->setActive(true);
        $config->setIndustry((int) $this->request->getParam('industry'));
        $benchmarkRepository->save($config);

        $this->enableMenu();

        $this->redirect([
            'controller' => $this->request->getParam('sTarget', 'BenchmarkOverview'),
            'action' => $this->request->getParam('sTargetAction', 'index'),
            'template' => 'statistics',
            'lang' => $this->request->getParam('lang', 'de'),
        ]);
    }

    /**
     * @param BenchmarkConfig $settings
     */
    private function handleSettings(BenchmarkConfig $config)
    {
        if ($config->getIndustry() === null) {
            $this->redirect([
                'controller' => 'BenchmarkLocalOverview',
                'action' => 'render',
                'template' => 'start',
                'lang' => $this->request->getParam('lang', 'de'),
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
                'template' => 'statistics',
                'lang' => $this->request->getParam('lang', 'de'),
            ]);

            return;
        }

        $this->loadCachedFile();
    }

    /**
     * Checks if "lastReceived" is younger than 24 hours.
     *
     * @param \DateTime $lastReceived
     *
     * @return bool
     */
    private function hasFreshStatistics(\DateTime $lastReceived)
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
     * @param \DateTime $lastReceived
     *
     * @return bool
     */
    private function hasOutdatedStatistics(\DateTime $lastReceived)
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

        if ($cachingHandler->isTemplateCached()) {
            $this->redirect([
                'controller' => 'BenchmarkOverview',
                'action' => 'render',
            ]);

            return;
        }

        $this->redirect([
            'controller' => 'BenchmarkLocalOverview',
            'action' => 'render',
            'template' => 'statistics',
            'lang' => $this->request->getParam('lang', 'de'),
        ]);
    }

    private function enableMenu()
    {
        $em = $this->get('models');
        $repo = $em->getRepository(Menu::class);

        /** @var Menu $menuEntry */
        $menuEntry = $repo->findOneBy(['controller' => 'Benchmark', 'action' => 'Settings']);
        if ($menuEntry) {
            $menuEntry->setActive(true);
            $em->persist($menuEntry);
            $em->flush();
        }
    }
}
