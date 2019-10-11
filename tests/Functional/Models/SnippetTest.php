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

namespace Shopware\Tests\Models;

use Shopware\Models\Snippet\Snippet;

class SnippetTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var array
     */
    public $testData = [
        'namespace' => 'unit/test/snippettestcase',
        'name' => 'ErrorIndexTitle',
        'value' => 'Fehler',
        'shopid' => '1',
        'localeId' => '1',
    ];

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $em;

    /**
     * @var \Shopware\Models\User\Repository
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
        $this->repo = Shopware()->Models()->getRepository('Shopware\Models\Snippet\Snippet');
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        $snippet = $this->repo->findOneBy(['namespace' => 'unit/test/snippettestcase']);

        if (!empty($snippet)) {
            $this->em->remove($snippet);
            $this->em->flush();
        }
        parent::tearDown();
    }

    /**
     * Test case
     */
    public function testGetterAndSetter()
    {
        $snippet = new Snippet();

        foreach ($this->testData as $field => $value) {
            $setMethod = 'set' . ucfirst($field);
            $getMethod = 'get' . ucfirst($field);

            $snippet->$setMethod($value);

            static::assertEquals($snippet->$getMethod(), $value);
        }
    }

    /**
     * Test case
     */
    public function testFromArrayWorks()
    {
        $snippet = new Snippet();
        $snippet->fromArray($this->testData);

        foreach ($this->testData as $fieldname => $value) {
            $getMethod = 'get' . ucfirst($fieldname);
            static::assertEquals($snippet->$getMethod(), $value);
        }
    }

    /**
     * Test case
     */
    public function testShouldBePersisted()
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

        foreach ($this->testData as $fieldname => $value) {
            $getMethod = 'get' . ucfirst($fieldname);
            static::assertEquals($snippet->$getMethod(), $value);
        }

        static::assertInstanceOf('\DateTime', $snippet->getCreated());
        static::assertInstanceOf('\DateTime', $snippet->getUpdated());
    }
}
