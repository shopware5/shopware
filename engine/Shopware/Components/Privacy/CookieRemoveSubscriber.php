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
use Enlight_Event_EventArgs;
use Shopware_Components_Config as Config;
use Symfony\Component\HttpFoundation\Cookie;

class CookieRemoveSubscriber implements SubscriberInterface
{
    const COOKIE_MODE_NOTICE = 0;
    const COOKIE_MODE_TECHNICAL = 1;
    const COOKIE_MODE_ALL = 2;

    /**
     * @var bool
     */
    private $cookieRemovalActive;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->cookieRemovalActive = $config->get('cookie_note_mode') && $config->get('show_cookie_note');
        $this->config = $config;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatch_Frontend' => 'onPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Widgets' => 'onPostDispatch',
        ];
    }

    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        if (!$this->cookieRemovalActive) {
            return;
        }

        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');

        $allowCookie = (int) $controller->Request()->getCookie('allowCookie');

        if ($allowCookie !== 1) {
            $requestCookies = array_keys($controller->Request()->getCookie());
            $cookiePath = $controller->Request()->getBasePath() . '/';

            foreach ($controller->Response()->getCookies() as $cookie) {
                if (!$this->isTechnicallyRequiredCookie($cookie['name']) || $this->config->get('cookie_note_mode') === self::COOKIE_MODE_ALL) {
                    if (!in_array($cookie['name'], $requestCookies)) {
                        $controller->Response()->headers->removeCookie($cookie['name']);
                        $controller->Response()->headers->removeCookie($cookie['name'], $cookiePath);
                    } else {
                        $controller->Response()->headers->setCookie(new Cookie($cookie['name'], null, 0));
                        $controller->Response()->headers->setCookie(new Cookie($cookie['name'], null, 0, $cookiePath));
                    }
                }
            }

            foreach ($requestCookies as $key) {
                if (!$this->isTechnicallyRequiredCookie($key) || $this->config->get('cookie_note_mode') === self::COOKIE_MODE_ALL) {
                    $controller->Response()->headers->setCookie(new Cookie($key, null, 0));
                    $controller->Response()->headers->setCookie(new Cookie($key, null, 0, $cookiePath));
                }
            }
        }
    }

    /**
     * Is cookie technically required?
     *
     * @param string $name
     *
     * @return bool
     */
    protected function isTechnicallyRequiredCookie($name)
    {
        return strpos($name, 'session-') !== false
            || strpos($name, '__csrf_') !== false
            || $name === 'shop'
            || $name === 'cookieDeclined';
    }
}
