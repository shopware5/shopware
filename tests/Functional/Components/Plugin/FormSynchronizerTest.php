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

namespace Shopware\Tests\Functional\Components\Plugin;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Shopware\Components\Plugin\FormSynchronizer;
use Shopware\Models\Config\Form;
use Shopware\Models\Plugin\Plugin;
use Shopware\Tests\Functional\Components\Theme\Base;

class FormSynchronizerTest extends Base
{
    /**
     * @var array
     */
    const CONFIG_1 = [
        'elements' => [
            [
                'isRequired' => false,
                'type' => 'text',
                'scope' => 0,
                'name' => 'ConfigA',
                'value' => null,
                'label' => [
                    'en' => 'A',
                ],
                'options' => [],
            ],
        ],
    ];

    /**
     * @var array
     */
    const CONFIG_2 = [
        'elements' => [
            [
                'isRequired' => false,
                'type' => 'text',
                'scope' => 0,
                'name' => 'ConfigB',
                'value' => null,
                'label' => [
                    'en' => 'B',
                ],
                'options' => [],
            ],
            [
                'isRequired' => false,
                'type' => 'text',
                'scope' => 0,
                'name' => 'ConfigA',
                'value' => null,
                'label' => [
                    'en' => 'A',
                ],
                'options' => [],
            ],
        ],
    ];

    /**
     * @var Form
     */
    private $form;

    /**
     * @var FormSynchronizer
     */
    private $synchronizer;

    protected function setUp(): void
    {
        $this->form = new Form();
        $this->synchronizer = $this->getSynchronizer();
    }

    public function testAddFormItems()
    {
        $this->synchronizer->synchronize(new Plugin(), self::CONFIG_1);
        static::assertEquals(1, $this->form->getElements()->count());
    }

    /**
     * @depends testAddFormItems
     */
    public function testNewFormFieldSorted()
    {
        $this->synchronizer->synchronize(new Plugin(), self::CONFIG_2);
        static::assertEquals(2, $this->form->getElements()->count());

        static::assertEquals(1, $this->form->getElement('ConfigA')->getPosition());
        static::assertEquals(0, $this->form->getElement('ConfigB')->getPosition());
    }

    /**
     * @return FormSynchronizer
     */
    protected function getSynchronizer()
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(static::any())
            ->method('findOneBy')
            ->willReturn($this->form);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('execute')
            ->willReturn($this->createMock(ResultStatement::class));

        $connection = $this->createMock(Connection::class);
        $connection->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $entityManager = $this->getEntityManager();
        $entityManager->method('persist');

        $entityManager->method('flush');

        $entityManager->method('getRepository')
            ->willReturn($repository);

        $entityManager->method('getConnection')
            ->willReturn($connection);

        return new FormSynchronizer($entityManager);
    }
}
