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

use Shopware\Models\Config\Form;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class ShopwareConfigTest extends Enlight_Components_Test_TestCase
{
    use DatabaseTransactionBehaviour;

    /**
     * Random plugin id that should be unique
     */
    const PLUGIN_ID = 20095;
    const PLUGIN_NAME = 'Testplugin';

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function setUp()
    {
        parent::setUp();

        $form = $this->initForm();
        $form->setElement('text', 'mail', ['value' => 'other@email.org']);
        $form->setElement('number', 'sometestkey', ['value' => 0]);

        Shopware()->Models()->flush();

        // force reload from database
        Shopware()->Container()->reset('cache');
        Shopware()->Container()->reset('config');
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function tearDown()
    {
        // force reload from database
        Shopware()->Container()->reset('cache');
        Shopware()->Container()->reset('config');

        parent::tearDown();
    }

    /**
     * Test case
     */
    public function testCoreConfigIgnored()
    {
        static::assertNotEquals('other@email.org', Shopware()->Config()->get('mail'));
    }

    /**
     * Test case
     */
    public function testCoreConfigNoCollision()
    {
        static::assertEquals(0, Shopware()->Config()->get('sometestkey'));
    }

    /**
     * Test case
     */
    public function testCoreConfigPrefixed()
    {
        static::assertEquals('other@email.org', Shopware()->Config()->get(static::PLUGIN_NAME . '::mail'));
    }

    protected function initForm()
    {
        $repo = Shopware()->Models()->getRepository(Form::class);
        $form = $repo->findOneBy(['name' => self::PLUGIN_NAME]);

        if (!$form) {
            $form = new Form();
            $form->setPluginId(static::PLUGIN_ID);

            $form->setName(self::PLUGIN_NAME);
            $form->setLabel(self::PLUGIN_NAME);
            $form->setDescription('');

            /** @var Form $parent */
            $parent = $repo->findOneBy([
                'name' => 'Other',
            ]);

            $form->setParent($parent);
            Shopware()->Models()->persist($form);
        }

        return $form;
    }
}
