<?php

return [
    'db' => [
        'username' => '%db.user%',
        'password' => '%db.password%',
        'dbname' => '%db.database%',
        'host' => '%db.host%',
        'port' => '%db.port%',
    ],
    'front' => [
        'showException' => true,
        'throwExceptions' => true,
        'noErrorHandler' => false,
    ],
    'phpsettings' => [
        'display_errors' => 1,
    ],
    'template' => [
        'forceCompile' => true,
    ],
    'cache' => [
        'backend' => 'Black-Hole',
        'backendOptions' => [],
        'frontendOptions' => [
            'write_control' => false,
        ],
    ],
    'model' => [
        'cacheProvider' => 'Array',
    ],
    'httpCache' => [
        'enabled' => false,
        'debug' => true,
    ],
    'csrfProtection' => [
        'frontend' => false,
        'backend' => false,
    ],
    'template_security' => [
        'enabled' => false,
        'php_modifiers' => [
            'get_class',
        ],
    ],
    'es' => [
        'enabled' => false,
        'number_of_replicas' => null,
        'number_of_shards' => null,
        'client' => [
            'hosts' => [
                'localhost:9200',
            ],
        ],
    ],
];
