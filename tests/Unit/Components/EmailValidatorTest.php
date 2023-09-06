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

namespace Shopware\Tests\Unit\Components;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Validator\EmailValidator;

class EmailValidatorTest extends TestCase
{
    private EmailValidator $SUT;

    protected function setUp(): void
    {
        $this->SUT = new EmailValidator();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getValidEmails(): array
    {
        return [
            // old domains
            'test@example.de' => ['test@example.de'],
            'test@example.com' => ['test@example.com'],
            'test@example.org' => ['test@example.org'],

            // new released domains
            'test@example.berlin' => ['test@example.berlin'],
            'test@example.email' => ['test@example.email'],
            'test@example.systems' => ['test@example.systems'],

            // new non released domains
            'test@example.active' => ['test@example.active'],
            'test@example.love' => ['test@example.love'],
            'test@example.video' => ['test@example.video'],
            'test@example.app' => ['test@example.app'],
            'test@example.shop' => ['test@example.shop'],

            'disposable.style.email.with+symbol@example.com' => ['disposable.style.email.with+symbol@example.com'],
            'other.email-with-dash@example.com' => ['other.email-with-dash@example.com'],
            '"much.more.unusual"@example.com' => ['"much.more.unusual"@example.com'],
            '!#$%&*+-/=?^_`.{|}~@example.com' => ['!#$%&*+-/=?^_`.{|}~@example.com'],
        ];
    }

    /**
     * @dataProvider getValidEmails
     */
    public function testValidEmails(string $email): void
    {
        static::assertTrue($this->SUT->isValid($email));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getInvalidEmails(): array
    {
        return [
            'test' => ['test'],
            'test@.de' => ['test@.de'],
            '@example' => ['@example'],
            '@example.de' => ['@example.de'],
            '@.' => ['@.'],
            ' @foo.de' => [' @foo.de'],
            '@foo.' => ['@foo.'],
            'foo@ .de' => ['foo@ .de'],
            'foo@bar. ' => ['foo@bar. '],
            "testing@example.com'||DBMS_PIPE.RECEIVE_MESSAGE(CHR(98)||CHR(98)||CHR(" => ["testing@example.com'||DBMS_PIPE.RECEIVE_MESSAGE(CHR(98)||CHR(98)||CHR("],
            "testing@example.com'||''||'" => ["testing@example.com'||''||'"],
            "testing@example.com'|||'" => ["testing@example.com'|||'"],
            "testing@example.com'||'" => ["testing@example.com'||'"],
            'test@example.com|' => ['test@example.com|'],
            'test@example.com(' => ['test@example.com('],
            'test@example.com"' => ['test@example.com"'],
        ];
    }

    /**
     * @dataProvider getInvalidEmails
     */
    public function testInvalidEmails(string $email): void
    {
        static::assertFalse($this->SUT->isValid($email));
    }
}
