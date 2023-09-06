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

namespace Shopware\Tests\Unit\Components\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\From;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Components\Model\QueryOperatorValidator;

class ModelRepositoryTest extends \PHPUnit\Framework\TestCase
{
    public function testPassingIndexByParameter()
    {
        $em = $this->getMockBuilder(EntityManagerInterface::class)->disableOriginalConstructor()->getMock();
        $em->expects(static::once())
            ->method('createQueryBuilder')
            ->willReturn(new QueryBuilder($em, new QueryOperatorValidator()));

        $class = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();

        $modelRepository = new ModelRepository($em, $class);

        $builder = $modelRepository->createQueryBuilder('foo', 'bar');

        /** @var From[] $from */
        $from = $builder->getDQLPart('from');

        static::assertIsArray($from);
        static::assertCount(1, $from);

        /** @var From $from */
        $from = array_shift($from);

        static::assertEquals('bar', $from->getIndexBy());
    }
}
