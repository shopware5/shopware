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
use Shopware\Components\ClientDetection\ClientDetectionProcessor;
use Symfony\Component\HttpFoundation\Request;

class Shopware_Tests_Components_ClientDetectionTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultsToDesktop()
    {
        $request = new Request();
        ClientDetectionProcessor::parseRequest($request);

        $this->assertEquals('desktop', $request->headers->get('X-UA-Device'));
    }

    public function validDeviceTypesProvider()
    {
        return array(
            array('mobile'),
            array('tablet'),
            array('desktop'),
        );
    }

    /**
     * @dataProvider validDeviceTypesProvider
     */
    public function testValidHeader($deviceType)
    {
        $request = new Request();
        $request->headers->set('X-UA-Device', $deviceType);

        ClientDetectionProcessor::parseRequest($request);

        $this->assertEquals($deviceType, $request->headers->get('X-UA-Device'));
    }

    /**
     * @dataProvider validDeviceTypesProvider
     */
    public function testValidCookie($deviceType)
    {
        $request = new Request();
        $request->cookies->set('X-UA-Device-force', $deviceType);

        ClientDetectionProcessor::parseRequest($request);

        $this->assertEquals($deviceType, $request->headers->get('X-UA-Device'));
    }

    public function invalidDeviceTypesProvider()
    {
        return array(
            array('phone', 'desktop'),
            array('', 'desktop'),
            array(false, 'desktop'),
            array(null, 'desktop'),
        );
    }

    /**
     * @dataProvider invalidDeviceTypesProvider
     */
    public function testInvalidValidHeader($deviceType, $expectedDeviceType)
    {
        $request = new Request();
        $request->headers->set('X-UA-Device', $deviceType);

        ClientDetectionProcessor::parseRequest($request);

        $this->assertEquals($expectedDeviceType, $request->headers->get('X-UA-Device'));
    }

    /**
     * @dataProvider invalidDeviceTypesProvider
     */
    public function testInvalidValidCookie($deviceType, $expectedDeviceType)
    {
        $request = new Request();
        $request->cookies->set('X-UA-Device-force', $deviceType);

        ClientDetectionProcessor::parseRequest($request);

        $this->assertEquals($expectedDeviceType, $request->headers->get('X-UA-Device'));
    }

    public function testCookieHasPrecedence()
    {
        $request = new Request();
        $request->cookies->set('X-UA-Device-force', 'tablet');
        $request->headers->set('X-UA-Device', 'mobile');

        ClientDetectionProcessor::parseRequest($request);

        $this->assertEquals('tablet', $request->headers->get('X-UA-Device'));
    }
}
