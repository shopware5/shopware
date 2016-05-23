<?php
return array_merge($this->loadConfig($this->AppPath() . 'Configs/Default.php'), array(
     'front' => array(
        'throwExceptions' => true,
        'disableOutputBuffering' => false,
        'showException' => true,
    ),
    'errorHandler' => array(
        'throwOnRecoverableError' => true,
    ),
    'session' => array(
        'unitTestEnabled' => true,
        'name' => 'SHOPWARESID',
        'cookie_lifetime' => 0,
        'use_trans_sid' => false,
        'gc_probability' => 1,
        'gc_divisor' => 100,
        'save_handler' => 'db'
    ),
    'mail' => array(
        'type' => 'file',
        'path' => $this->getCacheDir(),
    ),
    'phpSettings' => array(
        'error_reporting' => E_ALL,
        'display_errors' => 1,
        'date.timezone' => 'Europe/Berlin',
        'max_execution_time' => 0
    ),
    'csrfProtection' => array(
        'frontend' => false,
        'backend' => false
    )
));
