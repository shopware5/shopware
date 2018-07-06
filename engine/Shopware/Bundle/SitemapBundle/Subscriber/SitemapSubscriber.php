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

namespace Shopware\Bundle\SitemapBundle\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\Bundle\SitemapBundle\Controller\SitemapIndexXml;
use Shopware\Bundle\SitemapBundle\SitemapExporterInterface;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Config as Config;
use Symfony\Component\HttpKernel\KernelEvents;

class SitemapSubscriber implements SubscriberInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var SitemapExporterInterface
     */
    private $sitemapExporter;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param Config                   $config
     * @param SitemapExporterInterface $sitemapExporter
     * @param ModelManager             $modelManager
     */
    public function __construct(Config $config, SitemapExporterInterface $sitemapExporter, ModelManager $modelManager)
    {
        $this->config = $config;
        $this->sitemapExporter = $sitemapExporter;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_SitemapIndexXml' => 'registerSitemapIndexXmlController',
            KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }

    /**
     * @return string
     */
    public function registerSitemapIndexXmlController()
    {
        return SitemapIndexXml::class;
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onKernelTerminate(Enlight_Event_EventArgs $args)
    {
        /** @var Container $container */
        $container = $args->get('container');

        // Is strategy live?
        if ($this->config->get('sitemapRefreshStrategy') !== SitemapExporterInterface::STRATEGY_LIVE || !$container->initialized('shop')) {
            return;
        }

        $lastGenerated = $this->config->get('sitemapLastRefresh');
        $refreshInterval = $this->config->get('sitemapRefreshTime');

        // Regeneration is required
        if (time() > $refreshInterval + $lastGenerated) {
            foreach ($this->modelManager->getRepository(Shop::class)->getActiveShopsFixed() as $shop) {
                $this->sitemapExporter->generate($shop);
            }
        }
    }
}
