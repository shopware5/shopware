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
    'hook' => array(
        'proxyNamespace' => $this->App() . '_ProxiesStaging'
    ),
    'model' => array(
        'proxyNamespace' => $this->App() . '\ProxiesStaging'
    )
);

return $stagingConfig;
