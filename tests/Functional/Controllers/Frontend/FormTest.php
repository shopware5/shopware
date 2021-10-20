<?php

declare(strict_types=1);
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
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Bundle\AttributeBundle\Service\DataPersisterInterface;
use Shopware\Bundle\AttributeBundle\Service\TypeMappingInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware_Components_Translation;
use Throwable;

class FormTest extends Enlight_Components_Test_Plugin_TestCase
{
    use ContainerTrait;

    private const ENGLISH_SHOP_ID = 2;
    private const PARTNER_FORM_ID = 8;
    private const FORM_ATTRIBUTE_TABLE_NAME = 's_cms_support_attributes';
    private const FORM_ATTRIBUTE_NAME = 'test';
    private const FORM_ATTRIBUTE_PERSISTER_NAME = '__attribute_' . self::FORM_ATTRIBUTE_NAME;

    private const GERMAN_NAME = 'Partnerformular';
    private const ENGLISH_NAME = 'Partner Form';

    /**
     * Request form page
     */
    public function testRequestPartnerForm(): void
    {
        $attributeCrudService = $this->getContainer()->get(CrudServiceInterface::class);
        $attributeCrudService->update(self::FORM_ATTRIBUTE_TABLE_NAME, self::FORM_ATTRIBUTE_NAME, TypeMappingInterface::TYPE_STRING);
        $dataPersister = $this->getContainer()->get(DataPersisterInterface::class);
        $dataPersister->persist([self::FORM_ATTRIBUTE_PERSISTER_NAME => 'Hello World'], self::FORM_ATTRIBUTE_TABLE_NAME, self::PARTNER_FORM_ID);

        $connection = $this->getContainer()->get('dbal_connection');
        $connection->beginTransaction();

        try {
            $this->dispatch('/partnerformular');
        } catch (Throwable $e) {
            static::fail('Exception thrown. This should not occur.');
        }

        static::assertNotTrue($this->Response()->isRedirect());
        $body = $this->Response()->getBody();
        static::assertIsString($body);
        static::assertStringContainsString(self::GERMAN_NAME, $body);

        $connection->rollBack();
        $this->getContainer()->get(ModelManager::class)->clear();
    }

    /**
     * Request translated form page
     */
    public function testRequestPartnerFormEnglish(): void
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $connection->beginTransaction();

        $this->Request()->setCookie('shop', self::ENGLISH_SHOP_ID);

        $this->getContainer()->get(Shopware_Components_Translation::class)->write(self::ENGLISH_SHOP_ID, 'forms', self::PARTNER_FORM_ID, [
            'name' => self::ENGLISH_NAME,
        ]);

        try {
            $this->dispatch('/partnerformular');
        } catch (Throwable $e) {
            static::fail('Exception thrown. This should not occur.');
        }

        static::assertNotTrue($this->Response()->isRedirect());
        $body = $this->Response()->getBody();
        static::assertIsString($body);
        static::assertStringContainsString(self::ENGLISH_NAME, $body);

        $shop = $this->getContainer()->get(ModelManager::class)->getRepository(Shop::class)->getActiveDefault();
        $this->getContainer()->get(ShopRegistrationServiceInterface::class)->registerResources($shop);

        $this->Request()->clearCookies();

        $connection->rollBack();
        $this->getContainer()->get(ModelManager::class)->clear();
    }

    public function testValidOrderNumberIsResolved(): void
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $connection->beginTransaction();

        $this->dispatch('/anfrage-formular?sInquiry=detail&sOrdernumber=sw10010');

        $body = $this->Response()->getBody();
        static::assertIsString($body);

        static::assertStringContainsString(
            '<input type="hidden" class="normal " value="Aperitif-Glas Demi Sec (sw10010)" id="sordernumber" placeholder="Artikelnummer" name="sordernumber"/>',
            $body
        );

        $connection->rollBack();
        $this->getContainer()->get(ModelManager::class)->clear();
    }

    public function testInvalidOrderNumberIsRemoved(): void
    {
        $connection = $this->getContainer()->get('dbal_connection');
        $connection->beginTransaction();

        $this->dispatch('/anfrage-formular?sInquiry=detail&sOrdernumber="this" [is] #not a {valid} <order> $number');

        $body = $this->Response()->getBody();
        static::assertIsString($body);

        static::assertStringContainsString(
            '<input type="hidden" class="normal " value="" id="sordernumber" placeholder="Artikelnummer" name="sordernumber"/>',
            $body
        );

        $connection->rollBack();
        $this->getContainer()->get(ModelManager::class)->clear();
    }
}
