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

namespace Shopware\Bundle\SitemapBundle\Controller;

use Shopware\Bundle\SitemapBundle\Exception\AlreadyLockedException;
use Shopware\Bundle\SitemapBundle\SitemapExporterInterface;
use Shopware\Bundle\SitemapBundle\SitemapListerInterface;

class SitemapIndexXml extends \Enlight_Controller_Action
{
    /**
     * Redirect to sitemap_index.xml if the old sitemap is being requested
     */
    public function preDispatch()
    {
        if ($this->Request()->getPathInfo() !== '/sitemap_index.xml') {
            $this->redirect(['controller' => 'sitemap_index.xml']);

            return;
        }
    }

    public function indexAction()
    {
        /** @var SitemapListerInterface $sitemap */
        $sitemapLister = $this->get('shopware_bundle_sitemap.service.sitemap_lister');
        $sitemaps = $sitemapLister->getSitemaps($this->get('shop')->getId());

        $config = $this->get('config');
        $lastGenerated = $config->get('sitemapLastRefresh');
        $refreshInterval = $config->get('sitemapRefreshTime');

        // If there are no sitemaps yet (or they are too old) and the generation strategy is "live", generate sitemaps
        if ((empty($sitemaps) || time() > $refreshInterval + $lastGenerated) &&
            $this->get('config')->get('sitemapRefreshStrategy') === SitemapExporterInterface::STRATEGY_LIVE) {
            // Close session to prevent session locking from waiting in case there is another request coming in
            session_write_close();

            /** @var SitemapExporterInterface $exporter */
            $exporter = $this->get('shopware_bundle_sitemap.service.sitemap_exporter');

            try {
                $exporter->generate($this->get('shop'));
            } catch (AlreadyLockedException $exception) {
                // Silent catch, lock couldn't be acquired. Some other process already generates the sitemap.
            }

            $sitemaps = $sitemapLister->getSitemaps($this->get('shop')->getId());
        }

        $this->Response()->setHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->View()->assign('sitemaps', $sitemaps);
    }
}
