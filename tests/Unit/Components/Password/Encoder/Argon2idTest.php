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

namespace Shopware\Tests\Unit\Components\Password\Encoder;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Password\Encoder\Argon2id;

class Argon2idTest extends TestCase
{
    /**
     * @var Argon2id
     */
    private $hasher;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->hasher = new Argon2id([
            // test should run fast, use minimum cost
            'memory_cost' => 256,
        ]);

        if (!$this->hasher->isCompatible()) {
            static::markTestSkipped(
                'Argon2id Hasher is not compatible with current system.'
            );
        }
    }

    /**
     * Test case
     */
    public function testIsAvailable(): void
    {
        static::assertInstanceOf(Argon2id::class, $this->hasher);
    }

    /**
     * Test case
     */
    public function testGetNameShouldReturnName(): void
    {
        static::assertEquals('Argon2id', $this->hasher->getName());
    }

    /**
     * Test case
     */
    public function testGenerateShouldReturnString(): void
    {
        static::assertIsString($this->hasher->encodePassword('foobar'));
    }

    /**
     * Test case
     */
    public function testGenerateShouldReturnDifferentHashesForSamePlaintextString(): void
    {
        static::assertNotEquals($this->hasher->encodePassword('foobar'), $this->hasher->encodePassword('foobar'));
    }

    /**
     * Test case
     */
    public function testVerifyShouldReturnTrueForMatchingHash(): void
    {
        $hash = $this->hasher->encodePassword('foobar');

        static::assertTrue($this->hasher->isPasswordValid('foobar', $hash));
    }

    /**
     * Test case
     */
    public function testVerifyShouldReturnFalseForNotMatchingHash(): void
    {
        $hash = $this->hasher->encodePassword('foobar');

        static::assertFalse($this->hasher->isPasswordValid('notfoo', $hash));
    }

    /**
     * Test case
     */
    public function testRehash(): void
    {
        $hash = $this->hasher->encodePassword('foobar');

        static::assertFalse($this->hasher->isReencodeNeeded($hash));
    }

    /**
     * Test case
     */
    public function testRehash2(): void
    {
        $hash = $this->hasher->encodePassword('foobar');
        $this->hasher = new Argon2id([
            'memory_cost' => 1024,
        ]);

        static::assertTrue($this->hasher->isReencodeNeeded($hash));
    }
}
