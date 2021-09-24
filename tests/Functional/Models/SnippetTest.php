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

namespace Shopware\Tests\Functional\Models;

use Doctrine\ORM\EntityRepository;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Snippet\Snippet;
use Shopware\Models\Snippet\SnippetRepository;

class SnippetTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var array<string, string>
     */
    public array $testData = [
        'namespace' => 'unit/test/snippettestcase',
        'name' => 'ErrorIndexTitle',
        'value' => 'Fehler',
        'shopid' => '1',
        'localeId' => '1',
    ];

    protected ModelManager $em;

    /**
     * @var SnippetRepository|EntityRepository<Snippet>
     */
    protected $repo;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Shopware()->Models();
        $this->repo = Shopware()->Models()->getRepository(Snippet::class);
    }

    protected function tearDown(): void
    {
        $snippet = $this->repo->findOneBy(['namespace' => 'unit/test/snippettestcase']);

        if (!empty($snippet)) {
            $this->em->remove($snippet);
            $this->em->flush();
        }
        parent::tearDown();
    }

    public function testGetterAndSetter(): void
    {
        $snippet = new Snippet();

        foreach ($this->testData as $field => $value) {
            $setMethod = 'set' . ucfirst($field);
            $getMethod = 'get' . ucfirst($field);

            $snippet->$setMethod($value);

            static::assertEquals($snippet->$getMethod(), $value);
        }
    }

    public function testFromArrayWorks(): void
    {
        $snippet = new Snippet();
        $snippet->fromArray($this->testData);

        foreach ($this->testData as $fieldName => $value) {
            $getMethod = 'get' . ucfirst($fieldName);
            static::assertEquals($snippet->$getMethod(), $value);
        }
    }

    public function testShouldBePersisted(): void
    {
        $snippet = new Snippet();
        $snippet->fromArray($this->testData);

        $this->em->persist($snippet);
        $this->em->flush();

        $snippetId = $snippet->getId();

        // remove from entity manager
        $this->em->detach($snippet);
        unset($snippet);

        $snippet = $this->repo->find($snippetId);
        static::assertInstanceOf(Snippet::class, $snippet);

        foreach ($this->testData as $fieldName => $value) {
            $getMethod = 'get' . ucfirst($fieldName);
            static::assertEquals($snippet->$getMethod(), $value);
        }

        static::assertInstanceOf('\DateTime', $snippet->getCreated());
        static::assertInstanceOf('\DateTime', $snippet->getUpdated());
    }
}
