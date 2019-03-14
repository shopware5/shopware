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

use Shopware\Components\ConfigWriter;

class CacheRouteInstaller
{
    /**
     * @var ConfigWriter
     */
    private $configWriter;

    public function __construct(ConfigWriter $configWriter)
    {
        $this->configWriter = $configWriter;
    }

    /**
     * @param string $route
     * @param int    $time
     *
     * @return bool
     */
    public function addHttpCacheRoute($route, $time, array $invalidateTags = [])
    {
        $cacheRoutes = $this->configWriter->get('cacheControllers', 'HttpCache');
        if (empty($cacheRoutes)) {
            return false;
        }

        $cacheRoutes = $this->explodeHttpCacheRoutes($cacheRoutes);
        $cacheRoutes = $this->addOrUpdateHttpCacheRoute($route, $time, $cacheRoutes);
        $cacheRoutes = $this->implodeHttpCacheRoutes($cacheRoutes);
        $this->configWriter->save('cacheControllers', $cacheRoutes, 'HttpCache');

        if (empty($invalidateTags)) {
            return true;
        }

        $noCacheRoutes = $this->configWriter->get('noCacheControllers', 'HttpCache');
        $noCacheRoutes = $this->explodeHttpCacheRoutes($noCacheRoutes);
        foreach ($invalidateTags as $tag) {
            $noCacheRoutes = $this->addNoCacheTag($route, strtolower($tag), $noCacheRoutes);
        }
        $noCacheRoutes = $this->implodeHttpCacheRoutes($noCacheRoutes);
        $this->configWriter->save('noCacheControllers', $noCacheRoutes, 'HttpCache');

        return true;
    }

    /**
     * @param string $route
     *
     * @return bool
     */
    public function removeHttpCacheRoute($route)
    {
        //remove cached controller
        $cacheRoutes = $this->configWriter->get('cacheControllers', 'HttpCache');
        if (empty($cacheRoutes)) {
            return false;
        }

        $cacheRoutes = $this->explodeHttpCacheRoutes($cacheRoutes);
        $cacheRoutes = array_filter($cacheRoutes, function ($row) use ($route) {
            return $row['route'] !== $route;
        });

        $cacheRoutes = $this->implodeHttpCacheRoutes($cacheRoutes);
        $this->configWriter->save('cacheControllers', $cacheRoutes, 'HttpCache');

        //remove no cache tags
        $noCacheRoutes = $this->configWriter->get('noCacheControllers', 'HttpCache');
        $noCacheRoutes = $this->explodeHttpCacheRoutes($noCacheRoutes);
        $noCacheRoutes = array_filter($noCacheRoutes, function ($row) use ($route) {
            return $row['route'] !== $route;
        });

        $noCacheRoutes = $this->implodeHttpCacheRoutes($noCacheRoutes);
        $this->configWriter->save('noCacheControllers', $noCacheRoutes, 'HttpCache');

        return true;
    }

    /**
     * @param string $routes
     *
     * @return array
     */
    private function explodeHttpCacheRoutes($routes)
    {
        $explodedRoutes = explode("\n", $routes);

        $explodedRoutes = array_map(function ($route) {
            $route = explode(' ', $route);
            if (empty($route[0])) {
                return null;
            }

            return ['route' => $route[0], 'time' => $route[1]];
        }, $explodedRoutes);

        $explodedRoutes = array_filter($explodedRoutes);

        return $explodedRoutes;
    }

    /**
     * @param string $route
     * @param int    $time
     * @param array  $existingRoutes
     *
     * @return array
     */
    private function addOrUpdateHttpCacheRoute($route, $time, $existingRoutes)
    {
        $exist = false;
        foreach ($existingRoutes as &$existingRoute) {
            if ($existingRoute['route'] !== $route) {
                continue;
            }

            $exist = true;
            if ((int) $existingRoute['time'] === (int) $time) {
                continue;
            }

            $existingRoute['time'] = $time;
        }
        unset($existingRoute);

        if ($exist === false) {
            $existingRoutes[] = ['route' => $route, 'time' => $time];
        }

        return $existingRoutes;
    }

    /**
     * @param array $routes
     *
     * @return string
     */
    private function implodeHttpCacheRoutes($routes)
    {
        $routes = array_map(function ($row) {
            return implode(' ', $row);
        }, $routes);

        return implode("\n", $routes);
    }

    /**
     * @param string $route
     * @param string $tag
     * @param array  $existingRoutes
     *
     * @return array
     */
    private function addNoCacheTag($route, $tag, $existingRoutes)
    {
        $exist = false;
        foreach ($existingRoutes as $existingRoute) {
            if ($existingRoute['route'] !== $route) {
                continue;
            }

            if ($existingRoute['time'] !== $tag) {
                continue;
            }

            $exist = true;
        }

        if ($exist === false) {
            $existingRoutes[] = ['route' => $route, 'time' => $tag];
        }

        return $existingRoutes;
    }
}
