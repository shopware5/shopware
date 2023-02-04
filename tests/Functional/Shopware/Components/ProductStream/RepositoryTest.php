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

namespace Shopware\Tests\Functional\Shopware\Components\ProductStream;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Components\LogawareReflectionHelper;
use Shopware\Components\ProductStream\Repository;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class RepositoryTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private const NOT_EXISTING_PRODUCT_STREAM_ID = 99999;

    public function testPrepareCriteriaWithInvalidStreamId(): void
    {
        $repo = new Repository(
            $this->getContainer()->get(Connection::class),
            new LogawareReflectionHelper(new NullLogger()),
        );

        $this->expectNotToPerformAssertions();
        // reading a product stream with an invalid ID should not throw an error
        $repo->prepareCriteria(new Criteria(), self::NOT_EXISTING_PRODUCT_STREAM_ID);
    }
}
