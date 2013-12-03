<?php

// Load custom config
if (file_exists($this->DocPath() . 'config_' . $this->Environment() . '.php')) {
    $customConfig = $this->loadConfig($this->DocPath() . 'config_' . $this->Environment() . '.php');
} elseif (file_exists($this->DocPath() . 'config.php')) {
    $customConfig = $this->loadConfig($this->DocPath() . 'config.php');
} elseif (file_exists(__DIR__ . '/Custom.php')) {
    $customConfig = $this->loadConfig(__DIR__ . '/Custom.php');
}  else {
    $customConfig = array();
}

if(!is_array($customConfig)) {
    throw new Enlight_Exception('The custom configuration file must return an array.');
}

// Allow partial override
$customConfig = array_merge(array(
    'db' => array(),
    'front' => array(),
    'template' => array(),
    'mail' => array(),
    'httpCache' => array(),
    'session' => array(),
    'phpSettings' => array(),
    'cache' => array(
        'backendOptions' => array(),
        'frontendOptions' => array()
    ),
    'hook' => array(),
    'model' => array(),
    'config' => array(),
    'custom' => array(),
    'backendSession' => array(),
    'plugins' => array()
), $customConfig);

return array_merge($customConfig, array(
    'custom' => $customConfig['custom'],
    'db' => array_merge(array(
        'username' => 'root',
        'password' => '',
        'dbname' => 'shopware',
        'host' => 'localhost',
        'charset' => 'utf8',
        'adapter' => 'pdo_mysql'
    ), $customConfig['db']),
    'front' => array_merge(array(
        'noErrorHandler' => false,
        'throwExceptions' => false,
        'useDefaultControllerAlways' => true,
        'disableOutputBuffering' => false,
        'showException' => true,
        'charset' => 'utf-8'
    ), $customConfig['front']),
    'config' => array_merge(array(), $customConfig['config']),
    'plugins' => array_merge(array(), $customConfig['plugins']),
    'template' => array_merge(array(
        'compileCheck' => true,
        'compileLocking' => true,
        'useSubDirs' => !ini_get('safe_mode'),
        'forceCompile' => false,
        'useIncludePath' => true,
        'charset' => 'utf-8',
        'forceCache' => false,
        'cacheDir' => $this->DocPath('cache_templates'),
        'compileDir' => $this->DocPath('cache_templates')
    ), $customConfig['template']),
    'mail' => array_merge(array(
        'charset' => 'utf-8'
    ), $customConfig['mail']),
    'httpCache' => array_merge(array(
        'enabled' => true,
        'debug' => false,
        'default_ttl' => 0,
        'private_headers' => array('Authorization', 'Cookie'),
        'allow_reload' => false,
        'allow_revalidate' => false,
        'stale_while_revalidate' => 2,
        'stale_if_error' => false,
        'cache_dir' => $this->DocPath('cache_html')
    ), $customConfig['httpCache']),
    'session' => array_merge(array(
        'name' => 'SHOPWARESID',
        'cookie_lifetime' => 0,
        //'cookie_httponly' => 1,
        'use_trans_sid' => false,
        'gc_probability' => 1,
        'gc_divisor' => 100,
        'save_handler' => 'db'
    ), $customConfig['session']),
    'phpSettings' => array_merge(array(
        'error_reporting' => E_ALL | E_STRICT,
        'display_errors' => 1,
        'date.timezone' => 'Europe/Berlin',
        'zend.ze1_compatibility_mode' => 0
    ), $customConfig['phpSettings']),
    'cache' => array(
        'frontendOptions' => array_merge(array(
            'automatic_serialization' => true,
            'automatic_cleaning_factor' => 0,
            'lifetime' => 3600
        ), $customConfig['cache']['frontendOptions']),
        'backend' => isset($customConfig['cache']['backend']) ? $customConfig['cache']['backend'] : 'File',
        'backendOptions' => array_merge(array(
            'hashed_directory_perm' => 0771,
            'cache_file_perm' => 0644,
            'hashed_directory_level' => ini_get('safe_mode') ? 0 : 3,
            'cache_dir' => $this->DocPath('cache_general'),
            'file_name_prefix' => 'shopware'
        ), $customConfig['cache']['backendOptions']),
    ),
    'hook' => array_merge(array(
        'proxyDir' => $this->DocPath('cache_proxies'),
        'proxyNamespace' => $this->App() . '_Proxies'
    ), $customConfig['hook']),
    'model' => array_merge(array(
        'autoGenerateProxyClasses' => false,
        'fileCacheDir'     => $this->DocPath('cache_doctrine_filecache'),
        'attributeDir' => $this->DocPath('cache_doctrine_attributes'),
        'proxyDir' => $this->DocPath('cache_doctrine_proxies'),
        'proxyNamespace' => $this->App() . '\Proxies',
        'cacheProvider' => 'auto' // supports null, auto, Apc, Array, Wincache and Xcache
    ), $customConfig['model']),
    'backendSession' => array_merge(array(
        'name' => 'SHOPWAREBACKEND',
//        'gc_maxlifetime' => 60 * 90,
        'cookie_lifetime' => 0,
        'cookie_httponly' => 1,
        'use_trans_sid' => false,
        'referer_check' => true, // true, false or a fix value
        'client_check' => false // true or false (is not compatible with firebug)
    ), $customConfig['backendSession']),
    /*
    'cache' => array(
        'backend' => 'Two Levels',
    	'backendOptions' => array(
			'slow_backend' => 'File',
			'slow_backend_options' =>  array(
				'hashed_directory_umask' => 0771,
				'cache_file_umask' => 0644,
				'hashed_directory_level' => 2,
				'cache_dir' => $this->DocPath('cache_general'),
				'file_name_prefix' => 'shopware'
	    	),
			'fast_backend'  => 'Memcached',
			'fast_backend_options' => array(
				'servers' => array(
					array(
						'host' => 'localhost',
						'port' => 11211,
						'persistent' => true,
						'weight' => 1,
						'timeout' => 5,
						'retry_interval' => 15,
						'status' => true,
						'failure_callback' => null
					)
				),
				'compression' => false,
				'compatibility' => false
			)
    	),
    ),
    */
    /*
     'session' => array(
         ...
         'save_handler' => 'memcache',
         'save_path' => 'tcp://localhost:11211?persistent=1&weight=1&timeout=1&retry_interval=15'
     ),
     'session' => array(
         ...
         'save_handler' => 'files'
     ),
     */
    /*
    'shop'=>array(
        'options' => array(
            'host' => 'test.shopware.de'
        ),
        'config' => array('data'=>array(
            'hostOriginal' => 'test.shopware.de',
            'basePath' => 'test.shopware.de/shopware',
        ))
    ),
    */
));
