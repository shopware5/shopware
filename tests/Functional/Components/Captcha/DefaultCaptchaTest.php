<?php

namespace Shopware\Tests\Functional\Components;
use Shopware\Components\Captcha\DefaultCaptcha;

/**
 * @category  Shopware
 * @package   Shopware\Components\Captcha
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class DefaultCaptchaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultCaptcha
     */
    private $captcha;

    public function setUp()
    {
        if (!function_exists('imagettftext')) {
            $this->markTestSkipped(
                'The imagettftext() function is not available.'
            );
        }

        $this->captcha = new DefaultCaptcha(
            Shopware()->Container(),
            Shopware()->Container()->get('config'),
            Shopware()->Container()->get('template')
        );
    }

    public function testCaptchaIsInitiallyInvalid()
    {
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sCaptcha', 'foobar');
        $this->assertFalse($this->captcha->validate($request));
    }

    public function testValidCaptcha()
    {
        $templateData = $this->captcha->getTemplateData();
        $this->assertArrayHasKey('img', $templateData);

        $random = Shopware()->Session()->get(DefaultCaptcha::SESSION_KEY);

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sCaptcha', array_pop(array_keys($random)));

        $this->assertTrue($this->captcha->validate($request));
    }

    public function testValidMultipleCaptchaCalls()
    {
        // call captcha five times
        $this->captcha->getTemplateData();
        $this->captcha->getTemplateData();
        $this->captcha->getTemplateData();
        $this->captcha->getTemplateData();
        $templateData = $this->captcha->getTemplateData();

        $this->assertArrayHasKey('img', $templateData);

        $random = Shopware()->Session()->get(DefaultCaptcha::SESSION_KEY);
        $this->assertCount(5, $random);

        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sCaptcha', 'INVALID CHALLENGE');
        $this->assertFalse($this->captcha->validate($request));

        $random = Shopware()->Session()->get(DefaultCaptcha::SESSION_KEY);
        $this->assertCount(5, $random, 'Invalid captcha should not decrease captcha backlog');

        // extract second generated captcha
        $challenge = array_slice(array_keys($random), 1, 1)[0];
        $request = new \Enlight_Controller_Request_RequestTestCase();
        $request->setParam('sCaptcha', $challenge);
        $this->assertTrue($this->captcha->validate($request));

        $random = Shopware()->Session()->get(DefaultCaptcha::SESSION_KEY);
        $this->assertCount(4, $random, 'Valid challenge should decrease captcha backlog');
    }
}
