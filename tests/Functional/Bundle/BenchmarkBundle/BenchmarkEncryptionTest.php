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

namespace Shopware\Tests\Functional\Bundle\BenchmarkBundle;

use Shopware\Bundle\BenchmarkBundle\BenchmarkEncryption;

class BenchmarkEncryptionTest extends \PHPUnit\Framework\TestCase
{
    public function testCorrectSignatureIsWorking()
    {
        $encryption = new BenchmarkEncryption(__DIR__ . '/fixtures/public_test_key.pem');

        $message = 'foobarbaz';
        $signature = $this->sign($message);

        static::assertTrue($encryption->isSignatureValid($message, $signature));
    }

    public function testWrongSignatureIsFailing()
    {
        $encryption = new BenchmarkEncryption(__DIR__ . '/fixtures/public_test_key.pem');

        $message = 'foobarbaz';
        $signature = 'bazbarfoo';

        static::assertFalse($encryption->isSignatureValid($message, $signature));
    }

    public function testSignatureIsSupported()
    {
        $encryption = new BenchmarkEncryption(__DIR__ . '/fixtures/public_test_key.pem');

        static::assertTrue($encryption->isSignatureSupported());
    }

    /**
     * Generates a signature for a given message using the private key
     *
     * @param string $message
     *
     * @return string
     */
    private function sign($message)
    {
        $signature = '';
        if (false === $privateKeyResource = openssl_pkey_get_private('file://' . __DIR__ . '/fixtures/private_test_key.pem', null)) {
            while ($errors[] = openssl_error_string()) {
            }
            throw new \RuntimeException(sprintf("Could not import private key: \n%s", implode("\n", $errors)));
        }

        if (openssl_sign($message, $signature, $privateKeyResource) === false) {
            while ($errors[] = openssl_error_string()) {
            }
            throw new \RuntimeException(sprintf("Error during private key read: \n%s", implode("\n", $errors)));
        }

        return base64_encode($signature);
    }
}
