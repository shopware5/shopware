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

/**
 * Mobile sitemap controller
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_SitemapMobileXml extends Enlight_Controller_Action
{
    /**
     * Index action method
     */
    public function indexAction()
    {
        $this->assertMobileSitemapEnabled();

        $this->Response()->setHeader('Content-Type', 'text/xml; charset=utf-8');
        set_time_limit(0);

        /** @var \Shopware\Components\SitemapXMLRepository $sitemap */
        $sitemap = $this->get('sitemapxml.repository');
        $this->View()->sitemap = $sitemap->getSitemapContent();
    }

    private function assertMobileSitemapEnabled()
    {
        if (!$this->get('config')->get('mobileSitemap')) {
            throw new Enlight_Controller_Exception(
                'Page not found', 404
            );
        }
    }
}
