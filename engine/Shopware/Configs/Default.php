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

if (file_exists($this->DocPath() . 'config_' . $this->Environment() . '.php')) {
    $customConfig = $this->loadConfig($this->DocPath() . 'config_' . $this->Environment() . '.php');
} elseif (file_exists($this->DocPath() . 'config.php')) {
    $customConfig = $this->loadConfig($this->DocPath() . 'config.php');
} else {
    $customConfig = [];
}

if (!is_array($customConfig)) {
    throw new Enlight_Exception('The custom configuration file must return an array.');
}

return array_replace_recursive([
    'custom' => [],
    'trustedproxies' => [],
    'cdn' => [
        'backend' => 'local',
        'strategy' => 'md5',
        'liveMigration' => false,
        'adapters' => [
            'local' => [
                'type' => 'local',
                'mediaUrl' => '',
                'permissions' => [
                    'file' => [
                        'public' => 0666 & ~umask(),
                        'private' => 0600 & ~umask(),
                    ],
                    'dir' => [
                        'public' => 0777 & ~umask(),
                        'private' => 0700 & ~umask(),
                    ],
                ],
                'path' => realpath(__DIR__ . '/../../../'),
            ],
            'ftp' => [
                'type' => 'ftp',
                'mediaUrl' => '',

                'host' => '',
                'username' => '',
                'password' => '',
                'port' => 21,
                'root' => '/',
                'passive' => true,
                'ssl' => false,
                'timeout' => 30,
            ],
        ],
    ],
    'csrfProtection' => [
        'frontend' => true,
        'backend' => true,
    ],
    'snippet' => [
        'readFromDb' => true,
        'writeToDb' => true,
        'readFromIni' => false,
        'writeToIni' => false,
        'showSnippetPlaceholder' => false,
    ],
    'errorHandler' => [
        'throwOnRecoverableError' => false,
    ],
    'db' => [
        'username' => 'root',
        'password' => '',
        'dbname' => 'shopware',
        'host' => 'localhost',
        'charset' => 'utf8',
        'adapter' => 'pdo_mysql',
    ],
    'es' => [
        'prefix' => 'sw_shop',
        'enabled' => false,
        'write_backlog' => true,
        'number_of_replicas' => null,
        'number_of_shards' => null,
        'wait_for_status' => 'green',
        'client' => [
            'hosts' => [
                'localhost:9200',
            ],
        ],
    ],
    'front' => [
        'noErrorHandler' => false,
        'throwExceptions' => false,
        'disableOutputBuffering' => false,
        'showException' => false,
        'charset' => 'utf-8',
    ],
    'config' => [],
    'store' => [
        'apiEndpoint' => 'https://api.shopware.com',
    ],
    'plugin_directories' => [
        'Default' => $this->AppPath('Plugins_Default'),
        'Local' => $this->AppPath('Plugins_Local'),
        'Community' => $this->AppPath('Plugins_Community'),
        'ShopwarePlugins' => $this->DocPath('custom_plugins'),
    ],
    'template' => [
        'compileCheck' => true,
        'compileLocking' => true,
        'useSubDirs' => true,
        'forceCompile' => false,
        'useIncludePath' => true,
        'charset' => 'utf-8',
        'forceCache' => false,
        'cacheDir' => $this->getCacheDir() . '/templates',
        'compileDir' => $this->getCacheDir() . '/templates',
    ],
    'mail' => [
        'charset' => 'utf-8',
    ],
    'httpcache' => [
        'enabled' => true,
        'lookup_optimization' => true,
        'debug' => false,
        'default_ttl' => 0,
        'private_headers' => ['Authorization', 'Cookie'],
        'allow_reload' => false,
        'allow_revalidate' => false,
        'stale_while_revalidate' => 2,
        'stale_if_error' => false,
        'cache_dir' => $this->getCacheDir() . '/html',
        'cache_cookies' => ['shop', 'currency', 'x-cache-context-hash'],
    ],
    'session' => [
        'cookie_lifetime' => 0,
        'cookie_httponly' => 1,
        'gc_probability' => 1,
        'gc_divisor' => 200,
        'save_handler' => 'db',
        'use_trans_sid' => 0,
        'locking' => true,
    ],
    'phpsettings' => [
        'error_reporting' => E_ALL & ~E_USER_DEPRECATED,
        'display_errors' => 0,
        'date.timezone' => 'Europe/Berlin',
    ],
    'cache' => [
        'frontendOptions' => [
            'automatic_serialization' => true,
            'automatic_cleaning_factor' => 0,
            'lifetime' => 3600,
            'cache_id_prefix' => md5($this->getCacheDir()),
        ],
        'backend' => 'auto', // e.G auto, apcu, xcache
        'backendOptions' => [
            'hashed_directory_perm' => 0777 & ~umask(),
            'cache_file_perm' => 0666 & ~umask(),
            'hashed_directory_level' => 3,
            'cache_dir' => $this->getCacheDir() . '/general',
            'file_name_prefix' => 'shopware',
        ],
    ],
    'hook' => [
        'proxyDir' => $this->getCacheDir() . '/proxies',
        'proxyNamespace' => $this->App() . '_Proxies',
    ],
    'model' => [
        'autoGenerateProxyClasses' => false,
        'attributeDir' => $this->getCacheDir() . '/doctrine/attributes',
        'proxyDir' => $this->getCacheDir() . '/doctrine/proxies',
        'proxyNamespace' => $this->App() . '\Proxies',
        'cacheProvider' => 'auto', // supports null, auto, Apcu, Array, Wincache and Xcache
        'cacheNamespace' => null, // custom namespace for doctrine cache provider (optional; null = auto-generated namespace)
    ],
    'backendsession' => [
        'name' => 'SHOPWAREBACKEND',
        'cookie_lifetime' => 0,
        'cookie_httponly' => 1,
        'use_trans_sid' => 0,
        'locking' => false,
    ],
], $customConfig);
