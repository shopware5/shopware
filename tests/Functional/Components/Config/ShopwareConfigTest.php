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

namespace Shopware\Tests\Functional\Components\Config;

use Enlight_Components_Test_TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Config\Form;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Zend_Cache_Core;

class ShopwareConfigTest extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public const PLUGIN_ID = 20095;
    public const PLUGIN_NAME = 'TestPlugin';

    private ModelManager $modelManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelManager = $this->getContainer()->get(ModelManager::class);
        $this->createForm();

        $this->getContainer()->get(Zend_Cache_Core::class)->clean();
        $this->getContainer()->get('config')->setShop($this->getContainer()->get('shop'));
    }

    protected function tearDown(): void
    {
        $form = $this->modelManager->getRepository(Form::class)->findOneBy(['name' => self::PLUGIN_NAME]);
        static::assertInstanceOf(Form::class, $form);
        $this->modelManager->remove($form);
        $this->modelManager->flush($form);

        parent::tearDown();
    }

    public function testCoreConfigIgnored(): void
    {
        static::assertNotEquals('other@email.org', $this->getContainer()->get('config')->get('mail'));
    }

    public function testCoreConfigNoCollision(): void
    {
        static::assertSame(123, $this->getContainer()->get('config')->get('sometestkey'));
    }

    public function testCoreConfigPrefixed(): void
    {
        static::assertSame('other@email.org', $this->getContainer()->get('config')->get(static::PLUGIN_NAME . '::mail'));
    }

    private function createForm(): void
    {
        $form = new Form();
        $form->setPluginId(static::PLUGIN_ID);

        $form->setName(self::PLUGIN_NAME);
        $form->setLabel(self::PLUGIN_NAME);
        $form->setDescription('');

        $parent = $this->modelManager->getRepository(Form::class)->findOneBy(['name' => 'Other']);

        $form->setParent($parent);
        $form->setElement('text', 'mail', ['value' => 'other@email.org']);
        $form->setElement('number', 'sometestkey', ['value' => 123]);

        $this->modelManager->persist($form);
        $this->modelManager->flush($form);
    }
}
