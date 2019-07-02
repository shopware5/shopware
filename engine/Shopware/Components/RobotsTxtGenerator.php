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

namespace Shopware\Components;

use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;

class RobotsTxtGenerator implements RobotsTxtGeneratorInterface
{
    /**
     * @var string[]
     */
    private $allows = [];

    /**
     * @var string[]
     */
    private $disallows = [];

    /**
     * @var string[]
     */
    private $baseUrls = [];

    /**
     * @var string
     */
    private $host = [];

    /**
     * @var bool
     */
    private $secure = false;

    /**
     * @var Context[]
     */
    private $routerContext = [];

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function setAllow(string $allow): void
    {
        if (in_array($allow, $this->disallows, true)) {
            $index = array_search($allow, $this->disallows);
            unset($this->disallows[$index]);

            return;
        }

        if (in_array($allow, $this->allows, true)) {
            return;
        }

        $this->allows[] = $allow;
    }

    public function setDisallow(string $disallow): void
    {
        if (in_array($disallow, $this->allows, true)) {
            $index = array_search($disallow, $this->allows);
            unset($this->allows[$index]);

            return;
        }

        if (in_array($disallow, $this->disallows, true)) {
            return;
        }

        $this->disallows[] = $disallow;
    }

    public function setBaseUrls(array $baseUrls): void
    {
        $this->baseUrls = $baseUrls;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function setSecure(bool $secure): void
    {
        $this->secure = $secure;
    }

    /**
     * @return string[]
     */
    public function getAllows(): array
    {
        $finalAllow = [];
        foreach ($this->baseUrls as $url) {
            foreach ($this->allows as $allow) {
                $finalAllow[] = 'Allow: ' . $url . $allow;
            }
        }

        return $finalAllow;
    }

    /**
     * @return string[]
     */
    public function getDisallows(): array
    {
        $finalDisallow = [];
        foreach ($this->baseUrls as $url) {
            foreach ($this->disallows as $disallow) {
                $finalDisallow[] = 'Disallow: ' . $url . $disallow . '/';
            }
        }

        return $finalDisallow;
    }

    /**
     * @return string[]
     */
    public function getSitemaps(): array
    {
        $finalSitemaps = [];

        foreach ($this->routerContext as $context) {
            $finalSitemaps[] = 'Sitemap: ' . $this->router->assemble([
                'module' => 'frontend',
                'controller' => 'sitemap_index.xml',
                'fullPath' => true,
            ], $context);
        }

        $finalSitemaps = array_unique($finalSitemaps);

        return $finalSitemaps;
    }

    /**
     * @return string[]
     */
    public function getBaseUrls(): array
    {
        return $this->baseUrls;
    }

    public function removeAllow(string $allow): void
    {
        $index = array_search($allow, $this->allows);
        if ($index !== false) {
            unset($this->allows[$index]);
        }
    }

    /**
     * @param string $disallow
     */
    public function removeDisallow($disallow): void
    {
        $index = array_search($disallow, $this->disallows);
        if ($index !== false) {
            unset($this->disallows[$index]);
        }
    }

    public function setRouterContext(array $routerContext)
    {
        $this->routerContext = $routerContext;

        return $this;
    }
}
