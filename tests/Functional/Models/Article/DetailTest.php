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

namespace Shopware\Tests\Functional\Models\Article;

use ReflectionClass;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\OrderNumberValidator\OrderNumberValidatorInterface;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Repository;

class DetailTest extends \Enlight_Components_Test_TestCase
{
    protected ModelManager $em;

    protected Repository $repo;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->em = Shopware()->Models();
        $this->repo = $this->em->getRepository(Detail::class);

        $this->em->getConnection()->beginTransaction();
    }

    public function tearDown(): void
    {
        $this->em->getConnection()->rollBack();
        parent::tearDown();
    }

    public function testValidOrderNumber(): void
    {
        $detail = $this->repo->find(6);
        static::assertInstanceOf(Detail::class, $detail);
        $detail->setNumber('SW100066');

        $this->em->persist($detail);
        $this->em->flush($detail);

        $detail = $this->repo->find(6);
        static::assertInstanceOf(Detail::class, $detail);

        $detailNumber = $detail->getNumber();
        static::assertNotNull($detailNumber);
        static::assertStringContainsString('SW100066', $detailNumber);
    }

    public function testInvalidOrderNumber(): void
    {
        $detail = $this->repo->find(6);
        static::assertInstanceOf(Detail::class, $detail);
        $detail->setNumber('€SW100066@1');

        $violations = $this->em->validate($detail);

        static::assertEquals(1, $violations->count());
        static::assertEquals('Order number "€SW100066@1" does not match pattern "/^[a-zA-Z0-9-_.]+$/"', $violations->get(0)->getMessage());
    }

    public function testEmptyOrderNumberIsInvalid(): void
    {
        $detail = $this->repo->find(6);
        static::assertInstanceOf(Detail::class, $detail);
        $detail->setNumber('');

        $violations = $this->em->validate($detail);

        static::assertEquals(1, $violations->count());
        static::assertEquals('This value should not be blank.', $violations->get(0)->getMessage());
    }

    /**
     * @runTestsInSeparateProcesses
     */
    public function testChangingOrderNumberRegexIsWorking(): void
    {
        $tests = [
            [
                'regex' => '/^[a-zA-Z0-9-_.€]+$/',
                'number' => '€SW1000661',
            ],
            [
                'regex' => '/^[a-zA-Z0-9-_.€@]+$/',
                'number' => '€SW100066@1',
            ],
            [
                'regex' => '/^[\w-_.@€]+$/',
                'number' => '@SW100066€1',
            ],
            [
                'regex' => '/^[\w\s-_.@€]+$/',
                'number' => '€SW1 00 066@1',
            ],
            [
                'regex' => '/^[\w\u-_.@€]+$/',
                'number' => '€SW100💙066@1',
            ],
        ];

        foreach ($tests as $test) {
            $this->internalTestChangingOrderNumberRegexIsWorking($test['regex'], $test['number']);
        }
    }

    /**
     * This helper set's the new regex pattern via inflection into the Validator-instance defined in the DIC
     *
     * @throws \ReflectionException
     */
    private function internalTestChangingOrderNumberRegexIsWorking(string $regex, string $number): void
    {
        $validator = Shopware()->Container()->get(OrderNumberValidatorInterface::class);

        $property = (new ReflectionClass(\get_class($validator)))->getProperty('pattern');
        $property->setAccessible(true);
        $property->setValue($validator, $regex);

        /** @var Detail $detail */
        $detail = $this->repo->find(6);
        $detail->setNumber($number);

        $violations = $this->em->validate($detail);

        static::assertEquals(0, $violations->count(), sprintf('Number "%s" does not match regex "%s"', $number, $regex));
    }
}
