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

namespace Shopware\Tests\Unit\Components\Password;

use DomainException;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Password\Encoder\PasswordEncoderInterface;
use Shopware\Components\Password\Manager;
use Shopware_Components_Config;

class ManagerTest extends TestCase
{
    public function testReencodePasswordWithInvalidPassword(): void
    {
        $mock = $this->getMockBuilder(Shopware_Components_Config::class)->disableOriginalConstructor()->getMock();

        $manager = new Manager($mock);

        $this->expectException(DomainException::class);
        $manager->reencodePassword('', 'invalid', 'invalid');
    }

    public function testReencodePasswordWithInvalidHash(): void
    {
        $mock = $this->getMockBuilder(Shopware_Components_Config::class)->disableOriginalConstructor()->getMock();

        $manager = new Manager($mock);

        $this->expectException(DomainException::class);
        $manager->reencodePassword('invalid', '', 'invalid');
    }

    public function testIsPasswordValidWithInvalidPassword(): void
    {
        $mock = $this->getMockBuilder(Shopware_Components_Config::class)->disableOriginalConstructor()->getMock();

        $manager = new Manager($mock);

        static::assertFalse($manager->isPasswordValid('', 'invalid', 'invalid'));
    }

    public function testIsPasswordValidWithInvalidHash(): void
    {
        $mock = $this->getMockBuilder(Shopware_Components_Config::class)->disableOriginalConstructor()->getMock();

        $manager = new Manager($mock);

        static::assertFalse($manager->isPasswordValid('invalid', '', 'invalid'));
    }

    public function testEncodePasswordWithInvalidPassword(): void
    {
        $mock = $this->getMockBuilder(Shopware_Components_Config::class)->disableOriginalConstructor()->getMock();

        $manager = new Manager($mock);

        $this->expectException(DomainException::class);
        $manager->encodePassword('', 'invalid');
    }

    public function testManagerThorwsErrorOnInvalidPassword(): void
    {
        $mock = $this->getMockBuilder(Shopware_Components_Config::class)->disableOriginalConstructor()->getMock();
        $encoder = $this->getMockBuilder(PasswordEncoderInterface::class)->getMock();
        $encoder->method('isReencodeNeeded')->willReturn(true);
        $encoder->method('encodePassword')->willReturn(false);
        $encoder->method('getName')->willReturn('invalid');

        $manager = new Manager($mock);
        $manager->addEncoder($encoder);

        $this->expectException(DomainException::class);
        $manager->reencodePassword('invalid', 'invalidHash', 'invalid');
    }
}
