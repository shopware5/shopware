<?php

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Enlight_Components_Test_Plugin_TestCase;
use Shopware\Models\Shop\Shop;

/**
 * Class FormTest
 * @package Shopware\Tests\Functional\Controllers\Frontend
 * @group Test
 */
class FormTest extends Enlight_Components_Test_Plugin_TestCase
{
    const GERMAN_NAME = 'Partnerformular';
    const ENGLISH_NAME = 'Partner Form';

    /**
     * Request form page
     */
    public function testRequestPartnerForm()
    {
        try {
            $this->dispatch('/partnerformular');
        } catch (\Exception $e) {
            $this->fail('Exception thrown. This should not occur.');
        }

        $this->assertTrue(!$this->Response()->isRedirect());
        $this->assertContains(self::GERMAN_NAME, $this->Response()->getBody());
    }

    /**
     * Request translated form page
     */
    public function testRequestPartnerFormEnglish()
    {
        $this->Request()->setCookie('shop', 2);

        Shopware()->Container()->get('translation')->write(2, 'forms', 8, [
            'name' => self::ENGLISH_NAME
        ]);

        try {
            $this->dispatch('/partnerformular');
        } catch (\Exception $e)  {
            $this->fail('Exception thrown. This should not occur.');
        }

        $this->assertTrue(!$this->Response()->isRedirect());
        $this->assertContains(self::ENGLISH_NAME, $this->Response()->getBody());

        Shopware()->Models()->getRepository(Shop::class)->getActiveDefault()->registerResources();

        $this->Request()->clearCookies();
    }
}