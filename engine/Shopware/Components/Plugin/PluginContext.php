<?php

namespace Shopware\Components\Plugin;

use Shopware\Models\Plugin\Plugin;

class PluginContext implements \JsonSerializable
{
    const CACHE_TAG_TEMPLATE = 'template';
    const CACHE_TAG_CONFIG = 'config';
    const CACHE_TAG_ROUTER = 'router';
    const CACHE_TAG_PROXY = 'proxy';
    const CACHE_TAG_THEME = 'theme';
    const CACHE_TAG_HTTP = 'http';

    /**
     * pre defined list to invalidate simple caches
     */
    const CACHE_LIST_DEFAULT = [
        self::CACHE_TAG_TEMPLATE,
        self::CACHE_TAG_CONFIG,
        self::CACHE_TAG_ROUTER,
        self::CACHE_TAG_PROXY
    ];

    /**
     * pre defined list to invalidate required frontend caches
     */
    const CACHE_LIST_FRONTEND = [
        self::CACHE_TAG_TEMPLATE,
        self::CACHE_TAG_THEME,
        self::CACHE_TAG_HTTP
    ];

    /**
     * pre defined list to invalidate all caches
     */
    const CACHE_LIST_ALL = [
        self::CACHE_TAG_TEMPLATE,
        self::CACHE_TAG_CONFIG,
        self::CACHE_TAG_ROUTER,
        self::CACHE_TAG_PROXY,
        self::CACHE_TAG_THEME,
        self::CACHE_TAG_HTTP
    ];

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @var string[]
     */
    private $scheduled = [];

    /**
     * @var string
     */
    private $currentVersion;

    /**
     * @var string
     */
    private $updateVersion;

    /**
     * @var string
     */
    private $shopwareVersion;

    /**
     * @param Plugin $plugin
     * @param string $shopwareVersion
     * @param string $currentVersion
     * @param string $updateVersion
     */
    public function __construct(
        Plugin $plugin,
        $shopwareVersion,
        $currentVersion = null,
        $updateVersion = null
    ) {
        $this->plugin = $plugin;
        $this->currentVersion = $currentVersion;
        $this->updateVersion = $updateVersion;
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
     * @return string
     */
    public function getUpdateVersion()
    {
        return $this->updateVersion;
    }

    /**
     * @param string $requiredVersion
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
     */
    public function scheduleMessage($message)
    {
        $this->scheduled['message'] = $message;
    }

    /**
     * Adds the defer task to clear the frontend cache
     * @param array $caches
     */
    public function scheduleClearCache(array $caches)
    {
        if (!array_key_exists('cache', $this->scheduled)) {
            $this->scheduled['cache'] = [];
        }
        $this->scheduled['cache'] = array_values(array_unique(array_merge($this->scheduled['cache'], $caches)));
    }

    /**
     * @return mixed[]
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
     * @return array
     */
    public function jsonSerialize()
    {
        return ['scheduled' => $this->scheduled];
    }
}
