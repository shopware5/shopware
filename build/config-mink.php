<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

return [
    'db' => [
        'username' => '%db.user%',
        'password' => '%db.password%',
        'dbname' => '%db.database%',
        'host' => '%db.host%',
        'port' => '%db.port%',
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

    // Backend-Cache
    'cache' => [
        'backend' => 'Black-Hole',
        'backendOptions' => [],
        'frontendOptions' => [
            'write_control' => false,
        ],
    ],

    // Model-Cache
    'model' => [
        'cacheProvider' => 'Array', // supports Apc, Array, Wincache and Xcache
    ],

    // Http-Cache
    'httpCache' => [
        'enabled' => false,
        'debug' => true,
    ],

    'mail' => [
        'type' => 'smtp',
        'host' => '%smtp.host%',
        'port' => 1025,
    ],
];
