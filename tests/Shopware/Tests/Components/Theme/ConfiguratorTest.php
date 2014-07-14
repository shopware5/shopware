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
class Shopware_Tests_Components_Theme_ConfiguratorTest extends Shopware_Tests_Components_Theme_Base
{
    /**
     * @var \Shopware\Components\Theme\Configurator
     */
    protected $configurator;

    protected function setUp()
    {
        parent::setUp();
    }

    public function testContainerInjection()
    {
        $manager = $this->getEntityManager();
        $util = $this->getUtilClass();
        $persister = $this->getFormPersister();
        $template = $this->getTemplate();
        $repo = $this->getShopRepository();

        $template->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue(true));

        $manager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repo));

        $repo->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($template));

        $util->expects($this->any())
            ->method('getThemeByTemplate')
            ->will($this->returnValue($this->getBareTheme()));

        $configurator = new \Shopware\Components\Theme\Configurator(
            $manager,
            $util,
            $persister,
            $this->getEventManager()
        );

        $container = new \Shopware\Components\Form\Container\TabContainer('test');

        $this->invokeMethod($configurator, 'injectConfig', array(
            $this->getResponsiveTheme(),
            $container
        ));

        $this->assertCount(1, $container->getElements());
    }

    public function testContainerNames()
    {

        $container = new \Shopware\Components\Form\Container\TabContainer('test1');
        $tab = new \Shopware\Components\Form\Container\Tab('test2', 'test2');
        $container->addTab($tab);

        $tab->addElement(new \Shopware\Components\Form\Field\Color('color'));
        $tab->addElement(new \Shopware\Components\Form\Field\Text('text'));

        $fieldSet = new \Shopware\Components\Form\Container\FieldSet('fieldset', 'title');
        $fieldSet->addElement(new \Shopware\Components\Form\Field\Percent('percent'));

        $tab->addElement($fieldSet);

        $names = $this->invokeMethod(
            Shopware()->Container()->get('theme_configurator'),
            'getContainerNames',
            array($container)
        );

        $this->assertArrayHasKey('fields', $names);
        $this->assertArrayHasKey('containers', $names);


        $this->assertCount(3, $names['fields']);
        $this->assertCount(3, $names['containers']);

        $this->assertContains('color', $names['fields']);
        $this->assertContains('percent', $names['fields']);
        $this->assertContains('text', $names['fields']);

        $this->assertContains('test1', $names['containers']);
        $this->assertContains('test2', $names['containers']);
        $this->assertContains('fieldset', $names['containers']);
    }


    public function testRemoveUnused()
    {
        $entityManager = $this->getEntityManager();

        $containers = new \Doctrine\Common\Collections\ArrayCollection();
        for ($i = 1; $i < 5; $i++) {
            $layout = new \Shopware\Models\Shop\TemplateConfig\Layout();
            $layout->setName('container' . $i);
            $containers->add($layout);
        }

        $elements = new \Doctrine\Common\Collections\ArrayCollection();
        for ($i = 1; $i < 5; $i++) {
            $layout = new \Shopware\Models\Shop\TemplateConfig\Element();
            $layout->setName('field' . $i);
            $elements->add($layout);
        }

        $entityManager->expects($this->once())
            ->method('flush');

        $entityManager->expects($this->exactly(3))
            ->method('remove')
            ->with($this->logicalOr(
                $this->isInstanceOf('Shopware\Models\Shop\TemplateConfig\Layout'),
                $this->isInstanceOf('Shopware\Models\Shop\TemplateConfig\Element')
            ));

        $eventManager = $this->getEventManager();
        $eventManager->expects($this->once())
            ->method('filter')
            ->will($this->returnValue(array(
                'containers' => array('container1', 'container4'),
                'fields' => array('field1','field3','field4')
            )));

        $configurator = $this->getMockBuilder('Shopware\Components\Theme\Configurator')
            ->setConstructorArgs(array(
                $entityManager,
                null,
                null,
                $eventManager
            ))
            ->getMock();

        $container = new \Shopware\Components\Form\Container\TabContainer('container1');
        $tab = new \Shopware\Components\Form\Container\Tab('container4', 'title');

        $container->addElement($tab);
        $tab->addElement(new \Shopware\Components\Form\Field\Text('field1'));
        $tab->addElement(new \Shopware\Components\Form\Field\Text('field3'));
        $tab->addElement(new \Shopware\Components\Form\Field\Text('field4'));


        $this->invokeMethod(
            $configurator,
            'removeUnused',
            array(
                $containers,
                $elements,
                $container
            )
        );
    }

    public function testValidateConfigSuccess()
    {
        $container = new \Shopware\Components\Form\Container\TabContainer('test');
        $tab = new \Shopware\Components\Form\Container\Tab('tab', 'tab');
        $container->addTab($tab);
        $tab->addElement(new \Shopware\Components\Form\Field\Text('Text'));

        $this->invokeMethod(
            Shopware()->Container()->get('theme_configurator'),
            'validateConfig',
            array($container)
        );
    }
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Field Shopware\Components\Form\Field\Text requires a configured name
     */
    public function testValidateConfigException()
    {
        $container = new \Shopware\Components\Form\Container\TabContainer('test');
        $container->setName(null);

        $tab = new \Shopware\Components\Form\Container\Tab('tab', 'tab');
        $container->addTab($tab);
        $tab->addElement(new \Shopware\Components\Form\Field\Text(null));

        $this->invokeMethod(
            Shopware()->Container()->get('theme_configurator'),
            'validateConfig',
            array($container)
        );
    }

    public function testSynchronizeSetsAdd()
    {
        $template = new \Shopware\Models\Shop\Template();

        $theme = $this->getResponsiveTheme();

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())
            ->method('flush');

        $configurator = $this->getMockBuilder('Shopware\Components\Theme\Configurator')
            ->setConstructorArgs(array($entityManager, null, null, $this->getEventManager()))
            ->getMock();

        $this->invokeMethod(
            $configurator,
            'synchronizeSets',
            array(
                $theme,
                $template
            )
        );

        $this->assertCount(2, $template->getConfigSets());

        $set = $template->getConfigSets()->get(0);
        $this->assertEquals('set1', $set->getName());

        $set = $template->getConfigSets()->get(1);
        $this->assertEquals('set2', $set->getName());
    }

    public function testSynchronizeSetsRemove()
    {
        $existing = new \Doctrine\Common\Collections\ArrayCollection();

        for ($i = 1; $i < 5; $i++) {
            $set = new \Shopware\Models\Shop\TemplateConfig\Set();
            $set->setName('set' . $i);
            $existing->add($set);
        }

        $template = $this->getMockBuilder('Shopware\Models\Shop\Template')
            ->disableOriginalConstructor()
            ->getMock();

        $template->expects($this->any())
            ->method('getConfigSets')
            ->will($this->returnValue($existing));

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())
            ->method('flush');

        $entityManager->expects($this->exactly(2))
            ->method('remove')
            ->with($this->isInstanceOf('Shopware\Models\Shop\TemplateConfig\Set'));

        $configurator = $this->getMockBuilder('Shopware\Components\Theme\Configurator')
            ->setConstructorArgs(array($entityManager, null, null, $this->getEventManager()))
            ->getMock();

        $theme = $this->getResponsiveTheme();

        $this->invokeMethod(
            $configurator,
            'synchronizeSets',
            array(
                $theme,
                $template
            )
        );
    }



}