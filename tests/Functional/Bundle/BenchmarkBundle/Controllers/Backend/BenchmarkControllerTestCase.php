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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle\Controllers\Backend;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Tests\Functional\Bundle\BenchmarkBundle\BenchmarkTestCase;

class BenchmarkControllerTestCase extends BenchmarkTestCase
{
    protected function getAssetsFolder()
    {
        return __DIR__ . '/assets/';
    }

    /**
     * @return \Enlight_Class
     */
    protected function getController()
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = \Enlight_Class::Instance($this::CONTROLLER_NAME);

        $controller->initController(new \Enlight_Controller_Request_RequestTestCase(), new \Enlight_Controller_Response_ResponseTestCase());

        $controller->setContainer(Shopware()->Container());
        $controller->setView(new \Enlight_View_Default(new \Enlight_Template_Manager()));

        return $controller;
    }

    /**
     * @param string $select
     *
     * @return string
     */
    protected function loadSettingColumn($select)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();

        return $queryBuilder->select($select)
            ->from('s_benchmark_config', 'config')
            ->execute()
            ->fetchColumn();
    }

    /**
     * @param string $key
     * @param string $value
     */
    protected function setSetting($key, $value)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();

        $queryBuilder->update('s_benchmark_config', 'config')
            ->set($key, ':value')
            ->setParameter(':value', $value)
            ->execute();
    }
}
