<?php
return [
    'slim' => [
        'log.level'   => \Slim\Log::DEBUG,
        'log.enabled' => true,
        'debug'           => true, // set debug to false so custom error handler is used
        'check.ip'        => false,
        'skippable.check' => false,
        'templates.path'  => __DIR__ . '/../templates'
    ],
    'storeapi.endpoint' => 'http://store.shopware.de/storeApi'
];
