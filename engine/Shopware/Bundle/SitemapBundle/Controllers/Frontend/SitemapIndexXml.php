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

use Shopware\Bundle\SitemapBundle\SitemapExporterInterface;
use Shopware\Bundle\SitemapBundle\SitemapListerInterface;

class SitemapIndexXml extends \Enlight_Controller_Action
{
    /**
     * Index action method
     */
    public function indexAction()
    {
        /** @var SitemapListerInterface $sitemap */
        $sitemapLister = $this->get('shopware_bundle_sitemap.service.sitemap_lister');

        $this->Response()->setHeader('Content-Type', 'text/xml; charset=utf-8');

        $this->View()->sitemaps = $sitemapLister->getSitemaps();

        $this->response->sendResponse();
        ob_flush();

        // Todo: Checken, wie alt die Sitemaps sind und bei Bedarf neu generieren
        $age = 500;

        if ($age > 1000) {
            set_time_limit(0);

            /** @var SitemapExporterInterface $exporter */
            $exporter = $this->get('shopware_bundle_sitemap.service.sitemap_exporter');

            $exporter->generate();
        }
    }
}
