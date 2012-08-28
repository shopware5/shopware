<?php

if (file_exists($this->DocPath()."/config.php")){
    $defaultConfig = include($this->DocPath()."/config.php");
}else {
    $defaultConfig = array();
}

$stagingConfig = array(
    'db' => array_merge($defaultConfig["db"],array(
        'dbname' => $defaultConfig["custom"]["staging_database"]
    )),
    'custom' => array_merge($defaultConfig["custom"],array(
        'is_staging' => true,
    )),
    'cache' => array(
        'backendOptions' => array("cache_dir"=>$this->DocPath('staging_cache_database'))
    ),
    'httpCache' => array(
        'cache_dir' =>  $this->DocPath('staging_cache_templates_html')
    ),
    'template' => array(
        'cacheDir' => $this->DocPath('staging_cache_templates_cache'),
        'compileDir' => $this->DocPath('staging_cache_templates_compile')
    ),
    'hook' => array(
        'proxyDir' => $this->AppPath('ProxiesStaging'),
        'proxyNamespace' => $this->App() . '_ProxiesStaging'
    ),
    'model' => array(
        'proxyDir' => $this->AppPath('ProxiesStaging'),
        'proxyNamespace' => $this->App() . '\ProxiesStaging'
    )
);
return $stagingConfig;