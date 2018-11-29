<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

return [
    'shopware.root_dir' => realpath(__DIR__ . '/../../../'),
    'check.ping_url' => 'recovery/install/ping.php',
    'check.check_url' => 'recovery/install/check.php',
    'check.token.path' => __DIR__ . '/../tmp/token',

    'api.endpoint' => 'https://api.shopware.com',

    'tos.urls' => [
        'de' => 'https://api.shopware.com/gtc/de_DE.html',
        'en' => 'https://api.shopware.com/gtc/en_GB.html',
    ],

    'languages' => ['de', 'en', 'nl', 'it', 'fr', 'es', 'pt', 'pl'],

    'slim' => [
        'log.level' => \Slim\Log::DEBUG,
        'log.enabled' => true,
        'debug' => true, // set debug to false so custom error handler is used
        'templates.path' => __DIR__ . '/../templates',
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
        ],
    ],
];
