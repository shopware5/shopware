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

use DateTime;
use Enlight_Components_Test_TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Esd;

class EsdTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var array<string, string|bool|int>
     */
    public array $testData = [
        'file' => '../foobar.pdf',
        'hasSerials' => true,
        'notification' => true,
        'maxdownloads' => 55,
    ];

    protected ModelManager $em;

    /**
     * @var ModelRepository<Esd>
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
        $this->repo = Shopware()->Models()->getRepository(Esd::class);
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        $esd = $this->repo->findOneBy(['file' => '../foobar.pdf']);

        if (!empty($esd)) {
            $this->em->remove($esd);
            $this->em->flush();
        }
        parent::tearDown();
    }

    public function testGetterAndSetter(): void
    {
        $esd = new Esd();

        foreach ($this->testData as $field => $value) {
            $setMethod = 'set' . ucfirst($field);
            $getMethod = 'get' . ucfirst($field);

            $esd->$setMethod($value);

            static::assertEquals($esd->$getMethod(), $value);
        }
    }

    public function testFromArrayWorks(): void
    {
        $esd = new Esd();
        $esd->fromArray($this->testData);

        foreach ($this->testData as $fieldName => $value) {
            $getMethod = 'get' . ucfirst($fieldName);
            static::assertEquals($esd->$getMethod(), $value);
        }
    }

    public function testEsdShouldBePersisted(): void
    {
        $esd = new Esd();

        $productVariant = Shopware()->Models()->getRepository(Detail::class)->findOneBy(['active' => true]);
        static::assertInstanceOf(Detail::class, $productVariant);
        $esd->setArticleDetail($productVariant);

        $esd->fromArray($this->testData);

        $this->em->persist($esd);
        $this->em->flush();

        $esdId = $esd->getId();

        // Remove esd from entity manager
        $this->em->detach($esd);
        unset($esd);

        $esd = $this->repo->find($esdId);
        static::assertInstanceOf(Esd::class, $esd);

        foreach ($this->testData as $fieldName => $value) {
            $getMethod = 'get' . ucfirst($fieldName);
            static::assertEquals($esd->$getMethod(), $value);
        }

        static::assertInstanceOf(DateTime::class, $esd->getDate());
    }

    public function testEsdShouldBePersistedWithCustomDateTime(): void
    {
        $esd = new Esd();

        $productVariant = Shopware()->Models()->getRepository(Detail::class)->findOneBy(['active' => true]);
        static::assertInstanceOf(Detail::class, $productVariant);
        $esd->setArticleDetail($productVariant);

        $esd->fromArray($this->testData);

        $esd->setDate(new Carbon());

        static::assertInstanceOf(Carbon::class, $esd->getDate());

        $this->em->persist($esd);
        $this->em->flush();

        $esdId = $esd->getId();

        // Remove esd from entity manager
        $this->em->detach($esd);
        unset($esd);

        $esd = $this->repo->find($esdId);
        static::assertInstanceOf(Esd::class, $esd);

        static::assertInstanceOf(DateTime::class, $esd->getDate());
    }
}

class Carbon extends DateTime
{
}
