<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle\Controllers\Backend;

use Doctrine\DBAL\Connection;
use Enlight_Class;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Shopware\Tests\Functional\Bundle\BenchmarkBundle\BenchmarkTestCase;
use Shopware_Controllers_Backend_ExtJs;

class BenchmarkControllerTestCase extends BenchmarkTestCase
{
    protected const CONTROLLER_NAME = '';

    protected function getAssetsFolder(): string
    {
        return __DIR__ . '/assets/';
    }

    protected function getController(): Shopware_Controllers_Backend_ExtJs
    {
        $controller = Enlight_Class::Instance(static::CONTROLLER_NAME);
        static::assertInstanceOf(Shopware_Controllers_Backend_ExtJs::class, $controller);

        $controller->initController(new Enlight_Controller_Request_RequestTestCase(), new Enlight_Controller_Response_ResponseTestCase());

        $controller->setContainer($this->getContainer());
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));

        return $controller;
    }

    protected function loadSettingColumn(string $select): string
    {
        $queryBuilder = $this->getContainer()->get(Connection::class)->createQueryBuilder();

        return (string) $queryBuilder->select($select)
            ->from('s_benchmark_config', 'config')
            ->execute()
            ->fetchOne();
    }

    protected function setSetting(string $key, string $value): void
    {
        $queryBuilder = $this->getContainer()->get(Connection::class)->createQueryBuilder();

        $queryBuilder->update('s_benchmark_config', 'config')
            ->set($key, ':value')
            ->setParameter(':value', $value)
            ->execute();
    }
}
