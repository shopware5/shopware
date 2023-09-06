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
use Shopware\Components\Password\Encoder\PreHashed;

class PreHashedTest extends TestCase
{
    /**
     * @var PreHashed
     */
    private $hasher;

    public function setUp(): void
    {
        $this->hasher = new PreHashed();
    }

    /**
     * Test case
     */
    public function testGetNameShouldReturnName(): void
    {
        static::assertEquals('PreHashed', $this->hasher->getName());
    }

    public function testEncodePasswordShouldNotModifyInput(): void
    {
        static::assertEquals('example', $this->hasher->encodePassword('example'));
    }

    public function testRehash(): void
    {
        static::assertFalse($this->hasher->isReencodeNeeded('example'));
    }

    public function testValidatePasswordForSameHashes(): void
    {
        static::assertTrue($this->hasher->isPasswordValid('example', 'example'));
    }

    public function testValidatePasswordForDifferentHashes(): void
    {
        static::assertFalse($this->hasher->isPasswordValid('example', 'alice'));
    }
}
