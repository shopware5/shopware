<?php

if (file_exists($this->DocPath()."/config.php")){
    $defaultConfig = include($this->DocPath()."/config.php");
}else {
    $defaultConfig = array();
}

$stagingConfig = array(
    'db' => array_merge($defaultConfig["db"],array(
        'dbname' => $defaultConfig["custom"]["staging_cache_general"]
    )),
    'custom' => array_merge($defaultConfig["custom"],array(
        'is_staging' => true,
    )),
    'cache' => array(
        'backendOptions' => array("cache_dir" => $this->DocPath('staging_cache_general')),
        'frontendOptions' => array()
    ),
    'httpCache' => array(
        'cache_dir' =>  $this->DocPath('staging_cache_templates_html')
    ),
    'template' => array(
        'cacheDir' => $this->DocPath('staging_cache_templates'),
        'compileDir' => $this->DocPath('staging_cache_templates')
    ),
    'hook' => array(
        'proxyDir' => $this->DocPath('staging_cache_proxies'),
        'proxyNamespace' => $this->App() . '_ProxiesStaging'
    ),
    'model' => array(
        'fileCacheDir'   => $this->DocPath('staging_cache_doctrine_filecache'),
        'attributeDir'   => $this->DocPath('staging_cache_doctrine_attributes'),
        'proxyDir'       => $this->DocPath('staging_cache_doctrine_proxies'),
        'proxyNamespace' => $this->App() . '\ProxiesStaging'
    )
);
return $stagingConfig;
