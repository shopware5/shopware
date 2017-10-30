<?php

return [
    'db' => [
        'username' => '__DB_USER__',
        'password' => '__DB_PASSWORD__',
        'dbname' => '__DB_NAME__',
        'host' => '__DB_HOST__',
        'port' => '__DB_PORT__',
    ],

    'csrfProtection' => [
        'frontend' => false,
        'backend' => false,
    ],

    'store' => [
        'apiEndpoint' => 'http://172.16.0.61:8000',
    ],

    'front' => [
        'showException' => true,
    ],

    'phpsettings' => [
        'display_errors' => 1,
    ],

    'mail' => [
        'type' => 'smtp',
        'host' => 'smtp',
        'port' => 1025,
    ],
];
