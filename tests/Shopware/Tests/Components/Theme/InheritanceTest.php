<?php

/**
 * Shopware 4.0
 * Copyright © shopware AG
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
class Shopware_Tests_Components_Theme_InheritanceTest extends Shopware_Tests_Components_Theme_Base
{
    public function testBuildInheritance()
    {
        $custom = $this->getDummyTemplates();

        $hierarchy = Shopware()->Container()->get('theme_inheritance')
            ->buildInheritance($custom);

        $this->assertCount(2, $hierarchy);
        $this->assertEquals('slave', $hierarchy[0]->getName());
        $this->assertEquals('master', $hierarchy[1]->getName());
    }

    public function testSmartyDirectories()
    {
        $custom = $this->getDummyTemplates();

        $directories = Shopware()->Container()->get('theme_inheritance')
            ->getSmartyDirectories($custom);

        $this->assertCount(2, $directories);

        foreach ($directories as $dir) {
            $this->assertStringEndsWith('/_private/smarty/', $dir);
        }
    }

    public function testTemplateDirectories()
    {
        $custom = $this->getDummyTemplates();

        $directories = Shopware()->Container()->get('theme_inheritance')
            ->getTemplateDirectories($custom);

        $this->assertCount(2, $directories);

        $this->assertStringEndsWith(
            'Themes/' . $custom->getTemplate() . '',
            $directories[0]
        );

        $this->assertStringEndsWith(
            'Themes/' . $custom->getParent()->getTemplate() . '',
            $directories[1]
        );
    }


    public function testThemeFiles()
    {
        $util = $this->getUtilClass();

        $template = $this->getDummyTemplates();
        $template->setParent(null);

        $bareTheme = $this->getBareTheme();

        $util->expects($this->exactly(2))
            ->method('getThemeByTemplate')
            ->with($template)
            ->will($this->returnValue($bareTheme));

        $pathResolver = $this->getPathResolver();
        $pathResolver->expects($this->any())
            ->method('getPublicDirectory')
            ->will($this->returnValue('public_directory'));

        $inheritance = new \Shopware\Components\Theme\Inheritance(
            null,
            $util,
            $pathResolver,
            $this->getEventManager()
        );

        $files = $inheritance->getJavascriptFiles($template);

        $this->assertCount(2, $files);

        foreach ($files as $file) {
            $this->assertStringEndsWith('.js', $file);
            $this->assertStringStartsWith('public_directory', $file);
        }

        $files = $inheritance->getCssFiles($template);

        $this->assertCount(2, $files);

        foreach ($files as $file) {
            $this->assertStringEndsWith('.css', $file);
            $this->assertStringStartsWith('public_directory', $file);
        }
    }


    private function getDummyTemplates()
    {
        $master = new \Shopware\Models\Shop\Template();
        $master->setName('master');
        $master->setTemplate('master');
        $master->setVersion(3);

        $slave = new \Shopware\Models\Shop\Template();
        $slave->setName('slave');
        $slave->setTemplate('slave');
        $slave->setParent($master);
        $slave->setVersion(3);

        return $slave;
    }
}