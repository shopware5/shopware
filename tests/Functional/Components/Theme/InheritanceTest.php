<?php
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

namespace Shopware\Tests\Functional\Components\Theme;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Theme\Inheritance;
use Shopware\Components\Theme\Installer;
use Shopware\Components\Theme\PathResolver;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;

class InheritanceTest extends Base
{
    protected function setUp(): void
    {
        $connection = Shopware()->Container()->get(Connection::class);
        $connection->beginTransaction();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $connection = Shopware()->Container()->get(Connection::class);
        $connection->rollBack();
        parent::tearDown();
    }

    public function getTheme(Template $template)
    {
        if ($template->getParent() === null) {
            return $this->getBareTheme();
        }

        return $this->getResponsiveTheme();
    }

    public function testBuildInheritance()
    {
        $custom = $this->getDummyTemplates();

        $util = $this->getUtilClass();
        $util->expects(static::any())
            ->method('getThemeByTemplate')
            ->with(static::logicalOr(
                static::equalTo($custom),
                static::equalTo($custom->getParent())
            ))
            ->willReturnCallback([$this, 'getTheme']);

        $inheritance = new Inheritance(
            Shopware()->Container()->get(ModelManager::class),
            $util,
            Shopware()->Container()->get(PathResolver::class),
            Shopware()->Container()->get('events'),
            Shopware()->Container()->get(MediaServiceInterface::class)
        );

        $hierarchy = $inheritance->buildInheritances($custom);

        static::assertCount(2, $hierarchy['full']);
        static::assertEquals('TestResponsive', $hierarchy['full'][0]->getName());
        static::assertEquals('TestBare', $hierarchy['full'][1]->getName());

        static::assertCount(1, $hierarchy['bare']);
        static::assertCount(1, $hierarchy['custom']);

        static::assertEquals('TestBare', $hierarchy['bare'][0]->getName());
        static::assertEquals('TestResponsive', $hierarchy['custom'][0]->getName());
    }

    public function testSmartyDirectories()
    {
        $custom = $this->getDummyTemplates();

        $directories = Shopware()->Container()->get('theme_inheritance')
            ->getSmartyDirectories($custom);

        static::assertCount(2, $directories);

        foreach ($directories as $dir) {
            static::assertStringEndsWith('/_private/smarty/', $dir);
        }
    }

    public function testTemplateDirectories()
    {
        $custom = $this->getDummyTemplates();

        $directories = Shopware()->Container()->get('theme_inheritance')
            ->getTemplateDirectories($custom);

        static::assertCount(2, $directories);

        static::assertStringEndsWith(
            'themes/Frontend/' . $custom->getTemplate() . '',
            $directories[0]
        );

        static::assertStringEndsWith(
            'themes/Frontend/' . $custom->getParent()->getTemplate() . '',
            $directories[1]
        );
    }

    public function testThemeFiles()
    {
        $util = $this->getUtilClass();

        $template = $this->getDummyTemplates();
        $template->setParent(null);

        $bareTheme = $this->getBareTheme();

        $util->expects(static::exactly(2))
            ->method('getThemeByTemplate')
            ->with($template)
            ->willReturn($bareTheme);

        $pathResolver = $this->getPathResolver();
        $pathResolver->expects(static::any())
            ->method('getPublicDirectory')
            ->willReturn('public_directory');

        $inheritance = new Inheritance(
            $this->getEntityManager(),
            $util,
            $pathResolver,
            $this->getEventManager(),
            Shopware()->Container()->get(MediaServiceInterface::class)
        );

        $files = $inheritance->getTemplateJavascriptFiles($template);
        static::assertCount(2, $files);

        foreach ($files as $file) {
            static::assertStringEndsWith('.js', $file);
            static::assertStringStartsWith('public_directory', $file);
        }

        $files = $inheritance->getTemplateCssFiles($template);

        static::assertCount(2, $files);

        foreach ($files as $file) {
            static::assertStringEndsWith('.css', $file);
            static::assertStringStartsWith('public_directory', $file);
        }
    }

    public function testConfigInheritanceForLanguageShop()
    {
        /** @var Connection $connection */
        $connection = Shopware()->Container()->get(Connection::class);
        $connection->beginTransaction();

        /** @var Installer $service */
        $service = Shopware()->Container()->get(Installer::class);
        $service->synchronize();

        /** @var ModelManager $em */
        $em = Shopware()->Container()->get(ModelManager::class);

        $shop = new Shop();
        $shop->setName('Main shop');

        $templateId = $connection->fetchColumn("SELECT id FROM s_core_templates WHERE template = 'Responsive' LIMIT 1");
        $template = $em->find(Template::class, $templateId);
        $shop->setTemplate($template);
        $em->persist($shop);
        $em->flush($shop);

        $elementId = $connection->fetchColumn("SELECT id FROM s_core_templates_config_elements WHERE template_id = :id AND name = 'brand-primary'", [':id' => $templateId]);
        $connection->executeQuery('DELETE FROM s_core_templates_config_values');

        $connection->executeQuery(
            'INSERT INTO s_core_templates_config_values (element_id, shop_id, `value`) VALUES (:elementId, :shopId, :value)',
            [':elementId' => $elementId, ':shopId' => $shop->getId(), ':value' => serialize('#000')]
        );

        /** @var Inheritance $inheritance */
        $inheritance = Shopware()->Container()->get('theme_inheritance');
        $config = $inheritance->buildConfig($template, $shop);
        static::assertArrayHasKey('brand-primary', $config);
        static::assertSame('#000', $config['brand-primary']);

        $sub = new Shop();
        $sub->setName('sub shop of main');
        $sub->setMain($shop);

        $config = $inheritance->buildConfig($template, $sub);
        static::assertArrayHasKey('brand-primary', $config);
        static::assertSame('#000', $config['brand-primary']);

        $connection->rollBack();
    }

    private function getDummyTemplates()
    {
        $master = new Template();
        $master->setName('TestBare');
        $master->setTemplate('TestBare');
        $master->setVersion(3);

        Shopware()->Container()->get(ModelManager::class)->persist($master);
        Shopware()->Container()->get(ModelManager::class)->flush();

        $slave = new Template();
        $slave->setName('TestResponsive');
        $slave->setTemplate('TestResponsive');
        $slave->setParent($master);
        $slave->setVersion(3);

        Shopware()->Container()->get(ModelManager::class)->persist($slave);
        Shopware()->Container()->get(ModelManager::class)->flush();

        return $slave;
    }
}
