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

namespace Shopware\Components\Privacy;

use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\CookieBundle\CookieGroupCollection;
use Shopware\Bundle\CookieBundle\Services\CookieHandlerInterface;
use Shopware\Bundle\CookieBundle\Services\CookieRemoveHandler;
use Shopware_Components_Config as Config;

class CookieRemoveSubscriber implements SubscriberInterface
{
    public const COOKIE_MODE_NOTICE = 0;
    public const COOKIE_MODE_TECHNICAL = 1;
    public const COOKIE_MODE_ALL = 2;

    /**
     * @var bool
     */
    private $cookieRemovalActive;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CookieHandlerInterface
     */
    private $cookieHandler;

    /**
     * @var bool
     */
    private $httpCacheEnabled;

    public function __construct(Config $config, CookieHandlerInterface $cookieHandler, bool $httpCacheEnabled)
    {
        $this->cookieRemovalActive = $config->get('cookie_note_mode') && $config->get('show_cookie_note');
        $this->config = $config;
        $this->cookieHandler = $cookieHandler;
        $this->httpCacheEnabled = $httpCacheEnabled;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Frontend' => 'onPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Widgets' => 'onPostDispatch',
        ];
    }

    public function onPostDispatch(\Enlight_Controller_ActionEventArgs $args): void
    {
        $controller = $args->getSubject();
        $controller->View()->assign('httpCacheEnabled', $this->httpCacheEnabled);

        if (!$this->cookieRemovalActive) {
            return;
        }

        if ($this->httpCacheEnabled) {
            $controller->Response()->headers->set(
                CookieRemoveHandler::COOKIE_CONFIG_KEY,
                json_encode([
                    'cookieNoteMode' => $this->config->get('cookie_note_mode'),
                    'showCookieNote' => $this->config->get('show_cookie_note'),
                ])
            );
        }

        $allowCookie = (int) $controller->Request()->cookies->getInt('allowCookie');
        if ($this->config->get('cookie_note_mode') !== self::COOKIE_MODE_TECHNICAL) {
            if ($allowCookie === 1) {
                return;
            }

            header_remove('Set-Cookie');

            return;
        }

        if ($this->httpCacheEnabled) {
            $controller->Response()->headers->set(
                CookieRemoveHandler::COOKIE_GROUP_COLLECTION_KEY,
                base64_encode(serialize($this->cookieHandler->getCookies()))
            );
        }

        $controller->View()->assign(
            'cookieGroups',
            $this->convertToArray($this->cookieHandler->getCookies())
        );
    }

    private function convertToArray(CookieGroupCollection $cookieGroupCollection): array
    {
        return json_decode(json_encode($cookieGroupCollection), true);
    }
}
