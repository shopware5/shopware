<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Controller_Request_RequestHttp;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ContentTypeBundle\Controller\Backend\ContentTypeManager;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class ContentTypeManagerTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    /**
     * @dataProvider searchQueries
     */
    public function testFieldsAction(string $searchQuery, int $expectedCount): void
    {
        $controller = $this->getController();
        $request = new Enlight_Controller_Request_RequestHttp();
        $request->setParam('query', $searchQuery);
        $controller->setRequest($request);

        $controller->fieldsAction();

        $fields = $controller->View()->getAssign('data');
        static::assertCount($expectedCount, $fields);
    }

    public function searchQueries(): Generator
    {
        yield 'No search query' => [
            '',
            15,
        ];

        yield 'Search for "Pro" uppercase' => [
            'Pro',
            2,
        ];

        yield 'Search for "pro" lowercase' => [
            'Pro',
            2,
        ];
    }

    private function getController(): ContentTypeManager
    {
        $view = new Enlight_View_Default(new Enlight_Template_Manager());
        $controller = $this->getContainer()->get(ContentTypeManager::class);
        $controller->setView($view);
        $controller->setContainer($this->getContainer());

        return $controller;
    }
}
