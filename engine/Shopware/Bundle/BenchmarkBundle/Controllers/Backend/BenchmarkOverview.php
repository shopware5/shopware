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
use Shopware\Bundle\BenchmarkBundle\Services\TemplateCachingHandler;
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
        return ['index', 'render', 'acceptTerms', 'setBusiness'];
    }

    public function indexAction()
    {
        /** @var ConfigRepositoryInterface $configRepository */
        $configRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $settings = $configRepository->loadSettings();

        $this->handleSettings($settings);
    }

    public function renderAction()
    {
        $this->get('plugins')->Controller()->ViewRenderer()->setNoRender(true);
        $this->Front()->Plugins()->Json()->setRenderer(false);

        /** @var ConfigRepositoryInterface $configRepository */
        $configRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $template = $configRepository->getTemplate();

        echo $template;
    }

    public function acceptTermsAction()
    {
        /** @var ConfigRepositoryInterface $configRepository */
        $configRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $configRepository->acceptTerms();

        $this->redirect([
            'controller' => $this->request->getParam('sTarget', 'BenchmarkOverview'),
            'action' => $this->request->getParam('sTargetAction', 'render'),
            'template' => 'branch_select',
        ]);
    }

    public function setBusinessAction()
    {
        /** @var ConfigRepositoryInterface $configRepository */
        $configRepository = $this->get('shopware.benchmark_bundle.repository.config');
        $configRepository->saveBusiness((int) $this->request->getParam('business'));
        $configRepository->setActive(true);

        $this->enableMenu();

        $this->redirect([
            'controller' => $this->request->getParam('sTarget', 'BenchmarkOverview'),
            'action' => $this->request->getParam('sTargetAction', 'index'),
            'template' => 'statistics',
        ]);
    }

    /**
     * @param array $settings
     */
    private function handleSettings(array $settings)
    {
        if (!$settings['termsAccepted']) {
            $this->redirect([
                'controller' => 'BenchmarkLocalOverview',
                'action' => 'render',
                'template' => 'start',
            ]);

            return;
        }

        if (!$settings['business']) {
            $this->redirect([
                'controller' => 'BenchmarkLocalOverview',
                'action' => 'render',
                'template' => 'branch_select',
            ]);

            return;
        }

        if ($this->hasFreshStatistics($settings['lastReceived'])) {
            $this->loadCachedFile();

            return;
        }

        if (!$settings['active'] || $this->hasOutdatedStatistics($settings['lastReceived'])) {
            $this->redirect([
                'controller' => 'BenchmarkLocalOverview',
                'action' => 'render',
                'template' => 'statistics',
            ]);

            return;
        }

        $this->loadCachedFile();
    }

    /**
     * Checks if "lastReceived" is younger than 24 hours.
     *
     * @param string $lastReceived
     *
     * @return bool
     */
    private function hasFreshStatistics($lastReceived)
    {
        $dateTimeToday = new \DateTime(date('Y-m-d H:i:s'));
        $dateTimeReceived = new \DateTime($lastReceived);

        $interval = new \DateInterval('PT1H');

        $periods = new \DatePeriod($dateTimeReceived, $interval, $dateTimeToday);
        $hours = iterator_count($periods);

        return $hours < 24;
    }

    /**
     * Checks if "lastReceived" is older than 7 days.
     *
     * @param string $lastReceived
     *
     * @return bool
     */
    private function hasOutdatedStatistics($lastReceived)
    {
        $dateTimeToday = new \DateTime(date('Y-m-d H:i:s'));
        $dateTimeReceived = new \DateTime($lastReceived);

        $interval = new \DateInterval('P1D');

        $periods = new \DatePeriod($dateTimeReceived, $interval, $dateTimeToday);
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
