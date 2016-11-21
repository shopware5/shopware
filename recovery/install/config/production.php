<?php
return [
    'shopware.root_dir' => realpath(__DIR__ . '/../../../'),
    'check.ping_url' => 'recovery/install/ping.php',
    'check.check_url' => 'recovery/install/check.php',
    'check.token.path' => __DIR__ . '/../tmp/token',

    'api.endpoint' => 'https://api.shopware.com',

    'slim' => [
        'log.level'   => \Slim\Log::DEBUG,
        'log.enabled' => true,
        'debug'           => true, // set debug to false so custom error handler is used
        'templates.path'  => __DIR__ . '/../templates'
    ],

    'menu.helper' => [
        'routes' => [
            'language-selection',
            'requirements',
            'license',
            'database-configuration',
            'database-import',
            'edition',
            'configuration',
            'finish',
        ]
    ]
];
