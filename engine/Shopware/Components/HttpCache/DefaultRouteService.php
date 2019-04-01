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

namespace Shopware\Components\HttpCache;

use Enlight_Controller_Request_Request;
use Shopware\Components\Plugin\ConfigReader;

class DefaultRouteService
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var CacheRouteGenerationService
     */
    private $cacheRouteGeneration;

    public function __construct(ConfigReader $configReader, CacheRouteGenerationService $cacheRouteGeneration)
    {
        $this->config = $configReader->getByPluginName('HttpCache');
        $this->cacheRouteGeneration = $cacheRouteGeneration;
    }

    /**
     * Returns an array mapping nocache-tags to controller names
     *
     * @return array
     */
    public function getDefaultNoCacheTags()
    {
        $controllers = $this->config['noCacheControllers'];
        $result = [];

        if (empty($controllers)) {
            return $result;
        }

        $controllers = str_replace(["\r\n", "\r"], "\n", $controllers);
        $controllers = explode("\n", trim($controllers));

        foreach ($controllers as $controller) {
            list($controller, $tag) = explode(' ', $controller);
            $result[strtolower($controller)] = explode(',', $tag);
        }

        return $result;
    }

    /**
     * findRouteValue takes the current Request and tries to find a default max-age value for either the full route,
     * including the action, or at least the controller. If that fails, null is returned.
     *
     * @return int|null
     */
    public function findRouteValue(Enlight_Controller_Request_Request $request, array $values = null)
    {
        if (!$values) {
            $values = $this->getDefaultRouteValues();
        }

        $route = $this->cacheRouteGeneration->getActionRoute($request);
        if (isset($values[$route])) {
            return $values[$route];
        }

        $route = $this->cacheRouteGeneration->getControllerRoute($request);
        if (isset($values[$route])) {
            return $values[$route];
        }

        return null;
    }

    /**
     * Returns an array mapping controller names to their default max-age as set via the HttpCache Plugin.
     *
     * @return array
     */
    private function getDefaultRouteValues()
    {
        $controllers = $this->config['cacheControllers'];
        $result = [];

        if (empty($controllers)) {
            return $result;
        }

        $controllers = str_replace(["\r\n", "\r"], "\n", $controllers);
        $controllers = explode("\n", trim($controllers));

        foreach ($controllers as $controller) {
            list($controller, $cacheTime) = explode(' ', $controller);
            $result[strtolower($controller)] = (int) $cacheTime;
        }

        return $result;
    }
}
