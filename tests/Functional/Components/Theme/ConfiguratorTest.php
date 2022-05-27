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

namespace Shopware\Tests\Functional\Components\Theme;

use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\Test\TestLogger;
use Shopware\Components\Form\Container\FieldSet;
use Shopware\Components\Form\Container\Tab;
use Shopware\Components\Form\Container\TabContainer;
use Shopware\Components\Form\Field\Color;
use Shopware\Components\Form\Field\Percent;
use Shopware\Components\Form\Field\Text;
use Shopware\Components\Theme\Configurator;
use Shopware\Models\Shop\Template;
use Shopware\Models\Shop\TemplateConfig\Element;
use Shopware\Models\Shop\TemplateConfig\Layout;
use Shopware\Models\Shop\TemplateConfig\Set;

class ConfiguratorTest extends Base
{
    public function testContainerNames(): void
    {
        $container = new TabContainer('test1');
        $tab = new Tab('test2', 'test2');
        $container->addTab($tab);

        $tab->addElement(new Color('color'));
        $tab->addElement(new Text('text'));

        $fieldSet = new FieldSet('fieldset', 'title');
        $fieldSet->addElement(new Percent('percent'));

        $tab->addElement($fieldSet);

        $configurator = Shopware()->Container()->get(Configurator::class);
        $names = $this->invokeMethod(
            $configurator,
            'getContainerNames',
            [$container]
        );

        static::assertArrayHasKey('fields', $names);
        static::assertArrayHasKey('containers', $names);

        static::assertCount(3, $names['fields']);
        static::assertCount(3, $names['containers']);

        static::assertContains('color', $names['fields']);
        static::assertContains('percent', $names['fields']);
        static::assertContains('text', $names['fields']);

        static::assertContains('test1', $names['containers']);
        static::assertContains('test2', $names['containers']);
        static::assertContains('fieldset', $names['containers']);
    }

    public function testRemoveUnused(): void
    {
        $entityManager = $this->getEntityManager();

        $containers = new ArrayCollection();
        for ($i = 1; $i < 5; ++$i) {
            $layout = new Layout();
            $layout->setName('container' . $i);
            $containers->add($layout);
        }

        $elements = new ArrayCollection();
        for ($i = 1; $i < 5; ++$i) {
            $layout = new Element();
            $layout->setName('field' . $i);
            $elements->add($layout);
        }

        $entityManager->expects(static::once())
            ->method('flush');

        $entityManager->expects(static::exactly(3))
            ->method('remove')
            ->with(static::logicalOr(
                static::isInstanceOf(Layout::class),
                static::isInstanceOf(Element::class)
            ));

        $eventManager = $this->getEventManager();
        $eventManager->expects(static::once())
            ->method('filter')
            ->willReturn([
                'containers' => ['container1', 'container4'],
                'fields' => ['field1', 'field3', 'field4'],
            ]);

        $configurator = $this->getMockBuilder(Configurator::class)
            ->setConstructorArgs([
                $entityManager,
                $this->getUtilClass(),
                $this->getFormPersister(),
                $eventManager,
                new TestLogger(),
            ])
            ->getMock();

        $container = new TabContainer('container1');
        $tab = new Tab('container4', 'title');

        $container->addElement($tab);
        $tab->addElement(new Text('field1'));
        $tab->addElement(new Text('field3'));
        $tab->addElement(new Text('field4'));

        $this->invokeMethod(
            $configurator,
            'removeUnused',
            [
                $containers,
                $elements,
                $container,
            ]
        );
    }

    public function testValidateConfigSuccess(): void
    {
        $container = new TabContainer('test');
        $tab = new Tab('tab', 'tab');
        $container->addTab($tab);
        $tab->addElement(new Text('Text'));

        $configurator = Shopware()->Container()->get(Configurator::class);
        $this->invokeMethod(
            $configurator,
            'validateConfig',
            [$container]
        );

        static::assertTrue(true, 'validateConfig doesn\'t throw an exception');
    }

    public function testValidateConfigException(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Field Shopware\Components\Form\Field\Text requires a configured name');
        $container = new TabContainer('test');
        $container->setName('');

        $tab = new Tab('tab', 'tab');
        $container->addTab($tab);
        $tab->addElement(new Text(''));

        $configurator = Shopware()->Container()->get(Configurator::class);
        $this->invokeMethod(
            $configurator,
            'validateConfig',
            [$container]
        );
    }

    public function testSynchronizeSetsAdd(): void
    {
        $template = new Template();

        $theme = $this->getResponsiveTheme();

        $entityManager = $this->getEntityManager();
        $entityManager->expects(static::once())
            ->method('flush');

        $configurator = $this->getMockBuilder(Configurator::class)
            ->setConstructorArgs([$entityManager, $this->getUtilClass(), $this->getFormPersister(), $this->getEventManager(), new TestLogger()])
            ->getMock();

        $this->invokeMethod(
            $configurator,
            'synchronizeSets',
            [
                $theme,
                $template,
            ]
        );

        static::assertCount(2, $template->getConfigSets());

        $set = $template->getConfigSets()->get(0);
        static::assertEquals('set1', $set->getName());

        $set = $template->getConfigSets()->get(1);
        static::assertEquals('set2', $set->getName());
    }

    public function testSynchronizeSetsRemove(): void
    {
        $existing = new ArrayCollection();

        for ($i = 1; $i < 5; ++$i) {
            $set = new Set();
            $set->setName('set' . $i);
            $existing->add($set);
        }

        $template = $this->createMock(Template::class);

        $template->method('getConfigSets')
            ->willReturn($existing);

        $entityManager = $this->getEntityManager();
        $entityManager->expects(static::once())
            ->method('flush');

        $entityManager->expects(static::exactly(2))
            ->method('remove')
            ->with(static::isInstanceOf(Set::class));

        $configurator = $this->getMockBuilder(Configurator::class)
            ->setConstructorArgs([$entityManager, $this->getUtilClass(), $this->getFormPersister(), $this->getEventManager(), new TestLogger()])
            ->getMock();

        $theme = $this->getResponsiveTheme();

        $this->invokeMethod(
            $configurator,
            'synchronizeSets',
            [
                $theme,
                $template,
            ]
        );
    }
}
