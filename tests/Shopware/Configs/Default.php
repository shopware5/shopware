<?php
return array_merge($this->loadConfig($this->AppPath() . 'Configs/Default.php'), array(
     'front' => array(
        'throwExceptions' => true,
        'returnResponse' => true,
        'disableOutputBuffering' => false,
        'showException' => true,
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
    'autoLoaderNamespaces' => array(
        'Shopware' => $this->TestPath(),
        'PHPUnit' => 'PHPUnit/'
    ),
    'mail' => array(
        'type' => 'file',
        'path' => $this->TestPath('TempFiles'),
        'callback' => create_function('$transport', 'return
            "ShopwareMail_" . sha1($transport->body) .
            "_" . str_replace("@", "[at]", $transport->recipients).".eml";
        ')
    ),
    'phpSettings' => array(
        'error_reporting' => E_ALL & ~E_NOTICE & ~E_STRICT,
        'display_errors' => 1,
        'date.timezone' => 'Europe/Berlin',
        'zend.ze1_compatibility_mode' => 0,
        'max_execution_time' => 0
    )
));
