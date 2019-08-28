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

namespace Shopware\Tests\Functional\Controllers\Frontend;

use Enlight_Components_Test_Plugin_TestCase;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class FormTest extends Enlight_Components_Test_Plugin_TestCase
{
    use DatabaseTransactionBehaviour;

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
            static::fail('Exception thrown. This should not occur.');
        }

        static::assertTrue(!$this->Response()->isRedirect());
        static::assertContains(self::GERMAN_NAME, $this->Response()->getBody());
    }

    /**
     * Request translated form page
     */
    public function testRequestPartnerFormEnglish()
    {
        $this->Request()->setCookie('shop', 2);

        Shopware()->Container()->get('translation')->write(2, 'forms', 8, [
            'name' => self::ENGLISH_NAME,
        ]);

        try {
            $this->dispatch('/partnerformular');
        } catch (\Exception $e) {
            static::fail('Exception thrown. This should not occur.');
        }

        static::assertNotTrue($this->Response()->isRedirect());
        static::assertContains(self::ENGLISH_NAME, $this->Response()->getBody());

        Shopware()->Models()->getRepository(Shop::class)->getActiveDefault()->registerResources();

        $this->Request()->clearCookies();
    }

    public function testValidOrderNumberIsResolved()
    {
        $this->dispatch('/anfrage-formular?sInquiry=detail&sOrdernumber=sw10010');

        static::assertContains(
            '<input type="hidden" class="normal " value="Aperitif-Glas Demi Sec (sw10010)" id="sordernumber" placeholder="Artikelnummer" name="sordernumber"/>',
            $this->Response()->getBody()
        );
    }

    public function testInvalidOrderNumberIsRemoved()
    {
        $this->dispatch('/anfrage-formular?sInquiry=detail&sOrdernumber="this" [is] #not a {valid} <order> $number');

        static::assertContains(
            '<input type="hidden" class="normal " value="" id="sordernumber" placeholder="Artikelnummer" name="sordernumber"/>',
            $this->Response()->getBody()
        );
    }
}
