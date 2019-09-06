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

use Shopware\Components\Logger;

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

    /*
     * For more information on working with reverse proxies and trusted headers see
     * https://symfony.com/doc/current/deployment/proxies.html
     */
    'trustedproxies' => [],
    'trustedheaderset' => -1,

    'filesystem' => [
        'private' => [
            'type' => 'local',
            'config' => [
                'root' => realpath(__DIR__ . '/../../../files/'),
            ],
        ],
        'public' => [
            'type' => 'local',
            'config' => [
                'root' => realpath(__DIR__ . '/../../../web/'),
                'url' => '',
            ],
        ],
    ],
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
                'root' => realpath(__DIR__ . '/../../../'),
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
            's3' => [
                'type' => 's3',
                'mediaUrl' => '',

                'bucket' => '',
                'region' => '',
                'endpoint' => null,
                'credentials' => [
                    'key' => '',
                    'secret' => '',
                ],
            ],
            'gcp' => [
                'type' => 'gcp',
                'mediaUrl' => '',

                'projectId' => '',
                'keyFilePath' => '',
                'bucket' => '',
                'root' => '',
            ],
        ],
    ],
    'csrfprotection' => [
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
    'errorhandler' => [
        'throwOnRecoverableError' => false,
        'ignoredExceptionClasses' => [
             // Disable logging for defined exceptions by class, eg. to disable any logging for CSRF exceptions add this:
             // \Shopware\Components\CSRFTokenValidationException::class
            \Shopware\Components\Api\Exception\BatchInterfaceNotImplementedException::class,
            \Shopware\Components\Api\Exception\CustomValidationException::class,
            \Shopware\Components\Api\Exception\NotFoundException::class,
            \Shopware\Components\Api\Exception\OrmException::class,
            \Shopware\Components\Api\Exception\ParameterMissingException::class,
            \Shopware\Components\Api\Exception\PrivilegeException::class,
            \Shopware\Components\Api\Exception\ValidationException::class,
        ],
    ],
    'db' => [
        'username' => 'root',
        'password' => '',
        'dbname' => 'shopware',
        'host' => 'localhost',
        'charset' => 'utf8mb4',
        'timezone' => null, // Something like: 'UTC', 'Europe/Berlin', '-09:30',
        'adapter' => 'pdo_mysql',
        'pdoOptions' => null,
        'serverVersion' => null,
        'defaultTableOptions' => [
            'charset' => 'utf8',
            'collate' => 'utf8_unicode_ci',
        ],
    ],
    'es' => [
        'prefix' => 'sw_shop',
        'enabled' => false,
        'write_backlog' => true,
        'number_of_replicas' => null,
        'number_of_shards' => null,
        'total_fields_limit' => null,
        'max_result_window' => 10000,
        'wait_for_status' => 'green',
        'dynamic_mapping_enabled' => true,
        'batchsize' => 500,
        'index_settings' => [
            'number_of_shards' => '%shopware.es.number_of_shards%',
            'number_of_replicas' => '%shopware.es.number_of_replicas%',
            'max_result_window' => '%shopware.es.max_result_window%',
            'mapping' => [
                'total_fields' => [
                    'limit' => '%shopware.es.total_fields_limit%',
                ],
            ],
        ],
        'backend' => [
            'prefix' => '%shopware.es.prefix%_backend_index_',
            'batch_size' => 500,
            'write_backlog' => false,
            'enabled' => false,
            'index_settings' => '%shopware.es.index_settings%',
        ],
        'client' => [
            'hosts' => [
                'localhost:9200',
            ],
        ],
        'logger' => [
            'level' => $this->Environment() !== 'production' ? Logger::DEBUG : Logger::ERROR,
        ],
        'max_expansions' => [
            'name' => 2,
            'number' => 2,
        ],
        'debug' => false,
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
        'timeout' => 7,
        'connect_timeout' => 5,
    ],
    'plugin_directories' => [
        'Default' => $this->AppPath('Plugins_Default'),
        'Local' => $this->AppPath('Plugins_Local'),
        'Community' => $this->AppPath('Plugins_Community'),
        'ShopwarePlugins' => $this->DocPath('custom_plugins'),
        'ProjectPlugins' => $this->DocPath('custom_project'),
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
        'templateDir' => $this->DocPath('themes'),
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
        /*
         * The "ignored_url_parameters" configuration will spare your Shopware system from re-caching a page when any
         * of the parameters listed here is matched. This allows the caching system to be more efficient.
         */
        'ignored_url_parameters' => [
           'pk_campaign',    // Piwik
           'piwik_campaign',
           'pk_kwd',
           'piwik_kwd',
           'pk_keyword',
           'pixelId',        // Yahoo
           'kwid',
           'kw',
           'adid',
           'chl',
           'dv',
           'nk',
           'pa',
           'camid',
           'adgid',
           'utm_term',       // Google
           'utm_source',
           'utm_medium',
           'utm_campaign',
           'utm_content',
           'gclid',
           'cx',
           'ie',
           'cof',
           'siteurl',
           '_ga',
           'fbclid',         // Facebook
        ],
    ],
    'bi' => [
        'endpoint' => [
            'benchmark' => 'https://bi.shopware.com/benchmark',
            'statistics' => 'https://bi.shopware.com/statistics',
        ],
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
    'sitemap' => [
        'batchsize' => 10000,
        'excluded_urls' => [],
        'custom_urls' => [],
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
        'backend' => 'auto', // e.G auto, apcu, xcache, redis
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
        'cacheProvider' => 'auto', // Supports null, auto, Apcu, Array, Wincache, Xcache and redis
        'cacheNamespace' => null, // Custom namespace for doctrine cache provider (optional; null = auto-generated namespace)
        'validOperators' => [], // Additional allowed QueryBuilder operators
    ],
    'backendsession' => [
        'name' => 'SHOPWAREBACKEND',
        'cookie_lifetime' => 0,
        'cookie_httponly' => 1,
        'use_trans_sid' => 0,
        'locking' => false,
    ],
    'template_security' => [
        'php_modifiers' => include __DIR__ . '/smarty_functions.php',
        'php_functions' => include __DIR__ . '/smarty_functions.php',
    ],
    'search' => [
        'indexer' => [
            'batchsize' => 4000,
        ],
    ],
    'app' => [
        'rootDir' => $this->DocPath(),
        'downloadsDir' => $this->DocPath('files_downloads'),
        'documentsDir' => $this->DocPath('files_documents'),
    ],
    'web' => [
        'webDir' => $this->DocPath('web'),
        'cacheDir' => $this->DocPath('web_cache'),
    ],
    'mpdf' => [
        // Passed to \Mpdf\Mpdf::__construct:
        'defaultConfig' => [
            'tempDir' => $this->getCacheDir() . '/mpdf/',
            'fontDir' => $this->DocPath('engine_Library_Mpdf_ttfonts_'),
            'fonttrans' => [
                'helvetica' => 'arial',
                'verdana' => 'arial',
                'times' => 'timesnewroman',
                'courier' => 'couriernew',
                'trebuchet' => 'arial',
                'comic' => 'arial',
                'franklin' => 'arial',
                'albertus' => 'arial',
                'arialuni' => 'arial',
                'zn_hannom_a' => 'arial',
                'ocr-b' => 'ocrb',
                'ocr-b10bt' => 'ocrb',
                'damase' => 'mph2bdamase',
            ],
            'fontdata' => [
                'arial' => [
                    'R' => 'arial.ttf',
                    'B' => 'arialbd.ttf',
                    'I' => 'ariali.ttf',
                    'BI' => 'arialbi.ttf',
                ],
                'couriernew' => [
                    'R' => 'cour.ttf',
                    'B' => 'courbd.ttf',
                    'I' => 'couri.ttf',
                    'BI' => 'courbi.ttf',
                ],
                'georgia' => [
                    'R' => 'georgia.ttf',
                    'B' => 'georgiab.ttf',
                    'I' => 'georgiai.ttf',
                    'BI' => 'georgiaz.ttf',
                ],
                'timesnewroman' => [
                    'R' => 'times.ttf',
                    'B' => 'timesbd.ttf',
                    'I' => 'timesi.ttf',
                    'BI' => 'timesbi.ttf',
                ],
                'verdana' => [
                    'R' => 'verdana.ttf',
                    'B' => 'verdanab.ttf',
                    'I' => 'verdanai.ttf',
                    'BI' => 'verdanaz.ttf',
                ],
            ],
            'format' => 'A4',
        ],
    ],
    'media' => [
        'whitelist' => [],
    ],
    'product' => [
        /*
         * This regex is used to validate SKUs, aka ordernumbers.
         * If you change this, please make sure the new format also works in URLs!
         */
        'orderNumberRegex' => '/^[a-zA-Z0-9-_.]+$/',
    ],
    'backward_compatibility' => [
        /*
         * @deprecated since Shopware 5.5
         *
         * Sorting of plugins is active by default in 5.6 and this parameter will be removed with Shopware 5.7
         */
        'predictable_plugin_order' => true,
    ],
    'logger' => [
        'level' => $this->Environment() !== 'production' ? Logger::DEBUG : Logger::ERROR,
    ],
    'extjs' => [
        'developer_mode' => false,
    ],
], $customConfig);
