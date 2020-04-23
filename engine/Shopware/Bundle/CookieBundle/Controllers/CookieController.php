<?php declare(strict_types=1);
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

namespace Shopware\Bundle\CookieBundle\Controllers;

use Shopware\Bundle\CookieBundle\Services\CookieRemoveHandlerInterface;
use Shopware\Components\Privacy\CookieRemoveSubscriber;

class CookieController extends \Enlight_Controller_Action
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var CookieRemoveHandlerInterface
     */
    private $cookieRemoveHandler;

    public function __construct(\Shopware_Components_Config $config, CookieRemoveHandlerInterface $cookieHandler)
    {
        $this->config = $config;
        $this->cookieRemoveHandler = $cookieHandler;
    }

    public function preDispatch(): void
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender(true);
    }

    public function indexAction(): void
    {
        if ($this->isHttpCacheEnabled()) {
            return;
        }

        $this->handleCookies();
    }

    private function handleCookies(): void
    {
        if (!$this->config->get('cookie_note_mode') || !$this->config->get('show_cookie_note')) {
            return;
        }

        $allowCookie = $this->request->cookies->getInt('allowCookie');

        if ($this->config->get('cookie_note_mode') === CookieRemoveSubscriber::COOKIE_MODE_ALL) {
            if ($allowCookie === 1) {
                return;
            }

            header_remove('Set-Cookie');

            $this->cookieRemoveHandler->removeAllCookies($this->request, $this->response);

            return;
        }

        if ($this->config->get('cookie_note_mode') === CookieRemoveSubscriber::COOKIE_MODE_TECHNICAL) {
            if ($allowCookie === 1) {
                return;
            }

            $this->cookieRemoveHandler->removeCookiesFromPreferences($this->request, $this->response);
        }
    }

    private function isHttpCacheEnabled(): bool
    {
        return (bool) $this->container->get('kernel')->getHttpCacheConfig()['enabled'];
    }
}
