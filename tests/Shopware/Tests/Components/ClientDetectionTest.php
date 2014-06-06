<?php
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

    public function invalidCookieDeviceTypesProvider()
    {
        return array(
            array('phone', 'desktop'),
            array('', 'desktop'),
            array(false,'desktop'),
            array(null, 'desktop'),
        );
    }

    /**
     * @dataProvider invalidCookieDeviceTypesProvider
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
