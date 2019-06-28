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

namespace Shopware\Components\Theme;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Form;
use Shopware\Components\Model;
use Shopware\Components\Theme;
use Shopware\Models\Shop;
use Shopware\Models\Shop\TemplateConfig\Set;

/**
 * The Theme\Configurator class is used
 * for theme configuration operations.
 * This class handles the configuration synchronization
 * between file system and database.
 *
 * Additionally this class is used to build the configuration
 * inheritance for the backend module.
 */
class Configurator
{
    /**
     * @var Model\ModelManager
     */
    protected $entityManager;

    /**
     * @var Form\Persister\Theme
     */
    protected $persister;

    /**
     * @var Util
     */
    protected $util;

    /**
     * @var Model\ModelRepository
     */
    protected $repository;

    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    public function __construct(
        Model\ModelManager $entityManager,
        Util $util,
        Form\Persister\Theme $persister,
        \Enlight_Event_EventManager $eventManager)
    {
        $this->entityManager = $entityManager;
        $this->persister = $persister;
        $this->util = $util;
        $this->eventManager = $eventManager;
        $this->repository = $entityManager->getRepository('Shopware\Models\Shop\Template');
    }

    /**
     * This function synchronize the defined file system theme configuration with
     * the already initialed configuration of the database.
     *
     * This function handles the configuration inheritance for the backend.
     *
     * If one of the theme container elements isn't valid the function throws an exception
     *
     * @throws \Exception
     */
    public function synchronize(Theme $theme)
    {
        //prevents the theme configuration lazy loading
        $template = $this->getTemplate($theme);

        //static main container which generated for each theme configuration.
        $container = new Form\Container\TabContainer('main_container');

        //inject the inheritance config container.
        $this->injectConfig($theme, $container);

        $this->eventManager->notify('Theme_Configurator_Container_Injected', [
            'theme' => $theme,
            'template' => $template,
            'container' => $container,
        ]);

        $theme->createConfig($container);

        $this->validateConfig($container);

        $this->eventManager->notify('Theme_Configurator_Theme_Config_Created', [
            'theme' => $theme,
            'template' => $template,
            'container' => $container,
        ]);

        //use the theme persister class to write the Shopware\Components\Form elements into the database
        $this->persister->save($container, $template);

        $this->eventManager->notify('Theme_Configurator_Theme_Config_Saved', [
            'theme' => $theme,
            'template' => $template,
            'container' => $container,
        ]);

        $this->removeUnused(
            $this->getLayout($template),
            $this->getElements($template),
            $container
        );

        $this->synchronizeSets($theme, $template);

        $this->eventManager->notify('Theme_Configurator_Theme_Config_Synchronized', [
            'theme' => $theme,
            'template' => $template,
            'container' => $container,
        ]);
    }

    /**
     * Helper function which validates the passed Shopware\Components\Form\Container.
     *
     * @throws \Exception
     */
    private function validateConfig(Form\Interfaces\Container $container)
    {
        //check if the container implements the validation interface
        if ($container instanceof Form\Interfaces\Validate) {
            $container->validate();
        }

        foreach ($container->getElements() as $element) {
            //check recursive validation.
            if ($element instanceof Form\Interfaces\Container) {
                $this->validateConfig($element);

            //check Form\Field validation
            } elseif ($element instanceof Form\Interfaces\Validate) {
                $element->validate();
            }
        }
    }

    /**
     * Synchronize the theme configuration sets of the file system and
     * the database.
     *
     * @throws \Exception
     */
    private function synchronizeSets(Theme $theme, Shop\Template $template)
    {
        $collection = new ArrayCollection();
        $theme->createConfigSets($collection);

        $synchronized = [];

        // Iterates all configurations sets of the file system
        foreach ($collection as $item) {
            if (!$item instanceof ConfigSet) {
                throw new \Exception(sprintf(
                    "Theme %s adds a configuration set which isn't an instance of Shopware\Components\Theme\ConfigSet.",
                    $theme->getTemplate()
                ));
            }

            $item->validate();

            // Check if this set is already defined, to prevent auto increment in the database.
            $existing = $this->getExistingConfigSet(
                $template->getConfigSets(),
                $item->getName()
            );

            // If the set isn't defined, create a new one
            if (!$existing instanceof Shop\TemplateConfig\Set) {
                $existing = new Shop\TemplateConfig\Set();
                $template->getConfigSets()->add($existing);
            }

            $existing->setTemplate($template);

            $existing->setName($item->getName());
            $existing->setDescription($item->getDescription());
            $existing->setValues($item->getValues());

            $this->eventManager->notify('Theme_Configurator_Theme_ConfigSet_Updated', [
                'theme' => $theme,
                'template' => $template,
                'existing' => $existing,
                'defined' => $item,
            ]);

            $synchronized[] = $existing;
        }

        //iterates all sets of the template, file system and database
        foreach ($template->getConfigSets() as $existing) {
            //check if the current set was synchronized in the foreach before
            $defined = $this->getExistingConfigSet(
                $synchronized,
                $existing->getName()
            );

            if ($defined instanceof Shop\TemplateConfig\Set) {
                continue;
            }

            //if it wasn't synchronized, the file system theme want to remove the set.
            $this->entityManager->remove($existing);
        }

        $this->entityManager->flush();

        $this->eventManager->notify('Theme_Configurator_Theme_ConfigSets_Synchronized', [
            'theme' => $theme,
            'template' => $template,
        ]);
    }

    /**
     * Helper function which removes all unused configuration containers and elements
     * which are stored in the database but not in the passed container.
     */
    private function removeUnused(
        ArrayCollection $containers,
        ArrayCollection $fields,
        Form\Container $container)
    {
        $structure = $this->getContainerNames($container);

        $structure = $this->eventManager->filter('Theme_Configurator_Container_Names_Loaded', $structure, [
            'containers' => $container,
            'fields' => $fields,
            'container' => $container,
        ]);

        /** @var Shop\TemplateConfig\Layout $layout */
        foreach ($containers as $layout) {
            if (!in_array($layout->getName(), $structure['containers'])) {
                $this->entityManager->remove($layout);
            }
        }

        /** @var Shop\TemplateConfig\Element $layout */
        foreach ($fields as $layout) {
            if (!in_array($layout->getName(), $structure['fields'])) {
                $this->entityManager->remove($layout);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Helper function to select the shopware template with all config elements
     * with only one query.
     *
     * Used to synchronize the theme configuration in the synchronize() function.
     */
    private function getTemplate(Theme $theme)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select([
            'template',
            'elements',
            'layouts',
        ])
            ->from('Shopware\Models\Shop\Template', 'template')
            ->leftJoin('template.elements', 'elements')
            ->leftJoin('template.layouts', 'layouts')
            ->where('template.template = :name')
            ->setParameter('name', $theme->getTemplate());

        return $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_OBJECT
        );
    }

    /**
     * Returns all config containers of the passed template.
     *
     * @param \Shopware\Models\Shop\Template $template
     *
     * @return ArrayCollection
     */
    private function getLayout(Shop\Template $template)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select('layout')
            ->from('Shopware\Models\Shop\TemplateConfig\Layout', 'layout')
            ->where('layout.templateId = :templateId')
            ->setParameter('templateId', $template->getId());

        $layout = $builder->getQuery()->getResult();

        $layout = $this->eventManager->filter('Theme_Configurator_Layout_Loaded', $layout, [
            'template' => $template,
        ]);

        return new ArrayCollection($layout);
    }

    /**
     * Returns all config elements of the passed template.
     *
     * @param \Shopware\Models\Shop\Template $template
     *
     * @return ArrayCollection
     */
    private function getElements(Shop\Template $template)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select('elements')
            ->from('Shopware\Models\Shop\TemplateConfig\Element', 'elements')
            ->where('elements.templateId = :templateId')
            ->setParameter('templateId', $template->getId());

        $elements = $builder->getQuery()->getResult();

        $elements = $this->eventManager->filter('Theme_Configurator_Elements_Loaded', $elements, [
            'template' => $template,
        ]);

        return new ArrayCollection($elements);
    }

    /**
     * Helper function to create an array with all container and element names.
     * This function is used to synchronize the configuration fields and containers.
     * Elements which not stored in this array has to be removed by the removeUnused()
     * function
     *
     * @param \Shopware\Components\Form\Container $container
     *
     * @return array
     */
    private function getContainerNames(Form\Container $container)
    {
        $layout = [
            'containers' => [],
            'fields' => [],
        ];

        $layout['containers'][] = $container->getName();

        foreach ($container->getElements() as $element) {
            if ($element instanceof Form\Container) {
                $child = $this->getContainerNames($element);

                $layout['containers'] = array_merge(
                    $layout['containers'],
                    $child['containers']
                );

                $layout['fields'] = array_merge(
                    $layout['fields'],
                    $child['fields']
                );
            } elseif ($element instanceof Form\Field) {
                $layout['fields'][] = $element->getName();
            }
        }

        return $layout;
    }

    /**
     * This function handles the configuration inheritance for the synchronization.
     * The function handles the inheritance over a recursive call.
     *
     * First this function is called with the theme which should be synchronized.
     * If the theme uses a inheritance configuration, the
     * function resolves the theme parent and calls the "createConfig" function
     * of the Theme.php.
     * The Form\Container\TabContainer won't be initialed again, so each
     * inheritance level becomes the same container instance passed into their
     * createConfig() function.
     *
     * This allows the developer to display the theme configuration of extended
     * themes.
     */
    private function injectConfig(Theme $theme, Form\Container\TabContainer $container)
    {
        // Check if theme wants to inject parent configuration
        if (!$theme->useInheritanceConfig() || $theme->getExtend() === null) {
            return;
        }

        /** @var Shop\Template $template */
        $template = $this->repository->findOneBy([
            'template' => $theme->getTemplate(),
        ]);

        // No parent configured? cancel injection.
        if (!$template->getParent()) {
            return;
        }

        //get Theme.php instance of the parent template
        $parent = $this->util->getThemeByTemplate(
            $template->getParent()
        );

        $this->injectConfig($parent, $container);

        $parent->createConfig($container);
    }

    /**
     * Helper function which checks if the configuration set is
     * already exists in the passed collection.
     *
     * @param Set[]|ArrayCollection<Set> $collection
     * @param string                     $name
     *
     * @return Shop\TemplateConfig\Set|null
     */
    private function getExistingConfigSet($collection, $name)
    {
        /** @var Shop\TemplateConfig\Set $item */
        foreach ($collection as $item) {
            if ($item->getName() === $name) {
                return $item;
            }
        }

        return null;
    }
}
