<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Plugin\Context;

use JsonSerializable;
use ReturnTypeWillChange;
use Shopware\Components\CacheManager;
use Shopware\Models\Plugin\Plugin;

class InstallContext implements JsonSerializable
{
    public const CACHE_TAG_TEMPLATE = CacheManager::CACHE_TAG_TEMPLATE;
    public const CACHE_TAG_CONFIG = CacheManager::CACHE_TAG_CONFIG;
    public const CACHE_TAG_ROUTER = CacheManager::CACHE_TAG_ROUTER;
    public const CACHE_TAG_PROXY = CacheManager::CACHE_TAG_PROXY;
    public const CACHE_TAG_THEME = CacheManager::CACHE_TAG_THEME;
    public const CACHE_TAG_HTTP = CacheManager::CACHE_TAG_HTTP;

    /**
     * pre defined list to invalidate simple caches
     */
    public const CACHE_LIST_DEFAULT = [
        self::CACHE_TAG_TEMPLATE,
        self::CACHE_TAG_CONFIG,
        self::CACHE_TAG_ROUTER,
        self::CACHE_TAG_PROXY,
    ];

    /**
     * pre defined list to invalidate required frontend caches
     */
    public const CACHE_LIST_FRONTEND = [
        self::CACHE_TAG_TEMPLATE,
        self::CACHE_TAG_THEME,
        self::CACHE_TAG_HTTP,
    ];

    /**
     * pre defined list to invalidate all caches
     */
    public const CACHE_LIST_ALL = [
        self::CACHE_TAG_TEMPLATE,
        self::CACHE_TAG_CONFIG,
        self::CACHE_TAG_ROUTER,
        self::CACHE_TAG_PROXY,
        self::CACHE_TAG_THEME,
        self::CACHE_TAG_HTTP,
    ];

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var array{message?: string, cache?: array<string>}
     */
    private $scheduled = [];

    /**
     * @var string
     */
    private $currentVersion;

    /**
     * @var string
     */
    private $shopwareVersion;

    /**
     * @param string $shopwareVersion
     * @param string $currentVersion
     */
    public function __construct(Plugin $plugin, $shopwareVersion, $currentVersion)
    {
        $this->plugin = $plugin;
        $this->currentVersion = $currentVersion;
        $this->shopwareVersion = $shopwareVersion;
    }

    /**
     * @return string
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * @param string $requiredVersion
     *
     * @return bool
     */
    public function assertMinimumVersion($requiredVersion)
    {
        if ($this->shopwareVersion === '___VERSION___') {
            return true;
        }

        return version_compare($this->shopwareVersion, $requiredVersion, '>=');
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function scheduleMessage($message)
    {
        $this->scheduled['message'] = $message;
    }

    /**
     * Adds the defer task to clear the frontend cache
     *
     * @param string[] $caches
     *
     * @return void
     */
    public function scheduleClearCache(array $caches)
    {
        if (!\array_key_exists('cache', $this->scheduled)) {
            $this->scheduled['cache'] = [];
        }
        $this->scheduled['cache'] = array_values(array_unique(array_merge($this->scheduled['cache'], $caches)));
    }

    /**
     * @return array{message?: string, cache?: array<string>}
     */
    public function getScheduled()
    {
        return $this->scheduled;
    }

    /**
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @return array{scheduled: array{message?: string, cache?: array<string>}}
     *
     * @deprecated - Native return type will be added with Shopware 5.8
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return ['scheduled' => $this->scheduled];
    }
}
