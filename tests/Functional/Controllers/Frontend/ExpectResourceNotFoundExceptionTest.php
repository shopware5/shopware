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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Enlight_Components_Test_Controller_TestCase;
use Shopware\Bundle\ControllerBundle\Exceptions\ResourceNotFoundException;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class ExpectResourceNotFoundExceptionTest extends Enlight_Components_Test_Controller_TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

    private ?bool $defaultBotValue;

    /**
     * @before
     */
    public function setBotSession(): void
    {
        $this->defaultBotValue = $this->getContainer()->get('session')->get('Bot');
        $this->getContainer()->get('session')->set('Bot', true);
    }

    /**
     * @after
     */
    public function resetBotSession(): void
    {
        $this->getContainer()->get('session')->set('Bot', $this->defaultBotValue);
    }

    public function testListingIndexExpectedResourceNotFoundException(): void
    {
        $sql = file_get_contents(__DIR__ . '/fixtures/category.sql');
        static::assertIsString($sql);
        $this->getContainer()->get('dbal_connection')->executeStatement($sql);

        $this->expectException(ResourceNotFoundException::class);
        $this->expectErrorMessage('Category not found. The request comes from: "notFound". Module: "frontend", Controller: "listing", Action: "index"');
        $this->dispatch('/cat/?sCategory=312', true);
    }

    public function testCustomIndexExpectedResourceNotFoundException(): void
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->expectErrorMessage('Custom page not found. The request comes from: "notFound". Module: "frontend", Controller: "custom", Action: "index"');
        $this->dispatch('/custom/?sCustom=312', true);
    }
}
