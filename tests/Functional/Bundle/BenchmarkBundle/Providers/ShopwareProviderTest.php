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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle\Providers;

use PHPUnit\Framework\Constraint\IsType;

class ShopwareProviderTest extends ProviderTestCase
{
    const SERVICE_ID = 'shopware.benchmark_bundle.providers.shopware';
    const EXPECTED_KEYS_COUNT = 16;
    const EXPECTED_TYPES = [
        'api' => IsType::TYPE_STRING,
        'os' => IsType::TYPE_STRING,
        'arch' => IsType::TYPE_STRING,
        'dist' => IsType::TYPE_STRING,
        'serverSoftware' => IsType::TYPE_STRING,
        'phpVersion' => IsType::TYPE_STRING,
        'phpVersionId' => IsType::TYPE_INT,
        'maxExecutionTime' => IsType::TYPE_INT,
        'memoryLimit' => IsType::TYPE_INT,
        'sApi' => IsType::TYPE_STRING,
        'extensions' => IsType::TYPE_ARRAY,
        'mysqlVersion' => IsType::TYPE_STRING,
        'version' => IsType::TYPE_STRING,
        'revision' => IsType::TYPE_STRING,
        'licence' => IsType::TYPE_STRING,
        'shops' => IsType::TYPE_INT,
    ];
}
