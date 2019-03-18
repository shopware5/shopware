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
use Shopware\Bundle\SitemapBundle\Exception\AlreadyLockedException;
use Shopware\Bundle\SitemapBundle\SitemapExporterInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Shop;
use Shopware_Components_Config as Config;

class CronjobSubscriber implements SubscriberInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var SitemapExporterInterface
     */
    private $sitemapExporter;

    public function __construct(Config $config, ModelManager $modelManager, SitemapExporterInterface $sitemapExporter)
    {
        $this->config = $config;
        $this->modelManager = $modelManager;
        $this->sitemapExporter = $sitemapExporter;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Cronjob_SitemapGeneration' => 'onSitemapGeneration',
        ];
    }

    /**
     * Sitemap generation with cronjob
     *
     * @return string
     */
    public function onSitemapGeneration()
    {
        // Is strategy live?
        if ($this->config->get('sitemapRefreshStrategy') !== SitemapExporterInterface::STRATEGY_CRON) {
            return 'Sitemap Generation throught cronjob is disabled';
        }

        $output = '';

        /** @var Shop $shop */
        foreach ($this->modelManager->getRepository(Shop::class)->getActiveShopsFixed() as $shop) {
            $output .= sprintf('Generating sitemaps for shop #%d (%s)...', $shop->getId(), $shop->getName()) . PHP_EOL;
            try {
                $this->sitemapExporter->generate($shop);
            } catch (AlreadyLockedException $exception) {
                $output .= sprintf('ERROR: %s', $exception->getMessage()) . PHP_EOL;
            }
        }

        return $output;
    }
}
