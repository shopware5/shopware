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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Shopware\Models\Customer\Address;

class ApplicationTest extends TestCase
{
    /**
     * @dataProvider formatSearchValueTestDataProvider
     */
    public function testFormatSearchValue(array $parameter, string $expectedResult): void
    {
        $controller = new ApplicationControllerMock();

        $method = (new \ReflectionClass(ApplicationControllerMock::class))->getMethod('formatSearchValue');
        $method->setAccessible(true);

        $result = $method->invokeArgs($controller, $parameter);

        static::assertSame($expectedResult, $result);
    }

    /**
     * @dataProvider searchProvider
     */
    public function testGetSearchAssociationQueryDoesNotContainUnnecessaryParameters(?string $search, ?int $id): void
    {
        $controller = new ApplicationControllerDerivateMock();

        $getAssociationModel = \Closure::bind(function (string $association): string {
            return $this->getAssociatedModelByProperty($this->model, $association);
        }, $controller, $controller);

        $getBuilder = \Closure::bind(function (array $args): QueryBuilder {
            return $this->getSearchAssociationQuery(...$args);
        }, $controller, $controller);

        $getSearchAssociation = \Closure::bind(function (array $args): array {
            return $this->searchAssociation(...$args);
        }, $controller, $controller);

        /** @var QueryBuilder $builder */
        $builder = $getBuilder([
            'country',
            $getAssociationModel('country'),
            $search,
        ]);

        try {
            $getSearchAssociation([
                $search,
                'country',
                0,
                25,
                $id,
            ]);
        } catch (QueryException $e) {
            static::fail($e->getMessage());
        }

        if (empty($search)) {
            static::assertEmpty($builder->getDQLPart('where'));
            static::assertLessThan(1, $builder->getParameters()->count());
        } else {
            static::assertNotEmpty($builder->getDQLPart('where'));
            static::assertGreaterThanOrEqual(1, $builder->getParameters()->count());
        }
    }

    public function formatSearchValueTestDataProvider(): array
    {
        return [
            [['', ['type' => '']], '%%'],
            [['test', ['type' => '']], '%test%'],
            [['12-12', ['type' => '']], '%12-12%'],
            [['12-12', ['type' => 'date']], '%12-12%'],
            [['12-12', ['type' => 'datetime']], '%12-12%'],
            [['2019-1016', ['type' => 'date']], '%2019-1016%'],
            [['2019-10-16', ['type' => 'datetime']], '%2019-10-16%'],
            [['2019-1016', ['type' => 'datetime']], '%2019-1016%'],
            [['23.06.1999', ['type' => 'datetime']], '%1999-06-23%'],
            [['23-1999', ['type' => 'date']], '%23-1999%'],
            [['23-1999', ['type' => 'datetime']], '%23-1999%'],
            [['2319-991', ['type' => 'datetime']], '%2319-991%'],
            [['2019-991', ['type' => 'date']], '%2019-991%'],
            [['2019-991', ['type' => 'datetime']], '%2019-991%'],
            [['2019-10-16', ['type' => 'date']], '2019-10-16'],
            [['23.06.1999', ['type' => 'date']], '1999-06-23'],
        ];
    }

    /**
     * @return \Generator<string, array>
     */
    public function searchProvider(): \Generator
    {
        yield 'search parameter null' => [null, null];
        yield 'search parameter empty' => ['', null];
        yield 'search parameter set' => ['24146963-df33-4ca8-b8ca-97eb4885e832', null];
        yield 'search parameter null, id set' => [null, 2];
        yield 'search parameter empty, id set' => ['', 2];
        yield 'search parameter set, id set' => ['24146963-df33-4ca8-b8ca-97eb4885e832', 2];
    }
}

class ApplicationControllerMock extends \Shopware_Controllers_Backend_Application
{
}

class ApplicationControllerDerivateMock extends \Shopware_Controllers_Backend_Application
{
    protected $model = Address::class;

    protected $alias = 'address';
}
