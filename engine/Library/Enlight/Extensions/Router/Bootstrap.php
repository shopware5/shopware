<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category    Enlight
 * @package     Enlight_Extensions
 * @copyright   Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license     http://enlight.de/license New BSD License
 * @version     $Id$
 * @author      Heiner Lohaus
 * @author      $Author$
 */

/**
 * Default Enlight router extension for HTTPS support and more individual settings.
 * Provides support for HTTPS proxy, if the host has deposited it in the settings.
 *
 * @category    Enlight
 * @package     Enlight_Extensions
 * @copyright   Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license     http://enlight.de/license New BSD License
 * @config string $baseUrl
 * @config string $httpHost
 * @config string $secureBaseUrl
 * @config string $secureHttpHost
 * @config bool $forceSecure
 * @config bool $enableSecure
 * @config bool $forceAbsolute
 */
class Enlight_Extensions_Router_Bootstrap extends Enlight_Plugin_Bootstrap_Config
{
    /**
     * Install log plugin
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_RouteStartup',
            'onRouteStartup'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Router_FilterAssembleParams',
            'onFilterAssemble'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Router_FilterUrl',
            'onFilterUrl'
        );
    }

    /**
     * Updates the base url and the http host on route startup.
     * Adds the support for the HTTPS proxy system.
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onRouteStartup(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();

        if (($host = $request->getHeader('X_FORWARDED_HOST') !== null)
            && $host === $this->Config()->secureHttpHost
        ) {
            $request->setSecure();
        }

        if ($request->isSecure()) {
            $request->getPathInfo();
            $request->setBaseUrl($this->Config()->secureBaseUrl);
            $request->setHttpHost($this->Config()->secureHttpHost);
        } else {
            $request->getPathInfo();
            $request->setBaseUrl($this->Config()->baseUrl);
            $request->setHttpHost($this->Config()->httpHost);
        }
    }

    /**
     * Filter the router flags from the url params.
     *
     * @param Enlight_Event_EventArgs $args
     * @return array
     */
    public function onFilterAssemble(Enlight_Event_EventArgs $args)
    {
        $params = $args->getReturn();
        unset($params['forceSecure'], $params['forceAbsolute']);
        return $params;
    }

    /**
     * Complements the generated urls with scheme, host and basePath, if required.
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onFilterUrl(Enlight_Event_EventArgs $args)
    {
        /** @var $userParams array */
        $userParams = $args->get('userParams');

        /** @var $request Enlight_Controller_Request_RequestHttp */
        $request = $args->get('subject')->Front()->Request();
        /** @var $url string */
        $url = $args->getReturn();

        if (!empty($url) && ($url{0} === '/' || preg_match('|^[a-z]+://|', $url))) {
            return $url;
        }

        $forceSecure = !empty($this->Config()->forceSecure) || !empty($userParams['forceSecure']);
        $forceSecure = $forceSecure && !empty($this->Config()->enableSecure);
        $forceAbsolute = !empty($this->Config()->forceAbsolute) || !empty($userParams['forceAbsolute']);

        if ($forceAbsolute || $forceSecure) {
            $prepend = $forceSecure ? 'https://' : 'http://';
            if ($forceSecure) {
                $prepend .= $this->Config()->get('secureHttpHost', $request->getHttpHost());
                $prepend .= $this->Config()->get('secureBaseUrl', $request->getBaseUrl());
            } else {
                $prepend .= $this->Config()->get('httpHost', $request->getHttpHost());
                $prepend .= $this->Config()->get('baseUrl', $request->getBaseUrl());
            }
            $url = $prepend . '/' . $url;
        }

        return $url;
    }
}
