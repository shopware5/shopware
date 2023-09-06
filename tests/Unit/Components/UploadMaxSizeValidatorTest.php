<?php

declare(strict_types=1);
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

use Enlight_Controller_EventArgs;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use PHPUnit\Framework\TestCase;
use Shopware\Components\UploadMaxSizeException;
use Shopware\Components\UploadMaxSizeValidator;

class UploadMaxSizeValidatorTest extends TestCase
{
    private UploadMaxSizeValidator $uploadMaxSizeValidator;

    protected function setUp(): void
    {
        $this->uploadMaxSizeValidator = new UploadMaxSizeValidator();
    }

    public function testEmptyContentLength(): void
    {
        $eventArgs = $this->getMockEnlightControllerEventArgs();

        $this->uploadMaxSizeValidator->validateContentLength($eventArgs);

        $this->expectNotToPerformAssertions();
    }

    public function testContentLengthInRange(): void
    {
        $testLength = $this->uploadMaxSizeValidator->getPostMaxSize() / 2;
        $eventArgs = $this->getMockEnlightControllerEventArgs($testLength);

        $this->uploadMaxSizeValidator->validateContentLength($eventArgs);

        $this->expectNotToPerformAssertions();
    }

    public function testExceededContentLength(): void
    {
        $testLength = $this->uploadMaxSizeValidator->getPostMaxSize() * 2;
        $eventArgs = $this->getMockEnlightControllerEventArgs($testLength);

        $this->expectException(UploadMaxSizeException::class);
        $this->expectExceptionCode(413);
        $this->expectExceptionMessage('The uploaded file was too large. Please try to upload a smaller file.');

        $this->uploadMaxSizeValidator->validateContentLength($eventArgs);
    }

    private function getMockEnlightControllerEventArgs(int $contentLength = 0): Enlight_Controller_EventArgs
    {
        $response = new Enlight_Controller_Response_ResponseTestCase();
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setServer('CONTENT_LENGTH', (string) $contentLength);
        $request->setMethod('POST');

        return new Enlight_Controller_EventArgs([
            'request' => $request,
            'response' => $response,
        ]);
    }
}
