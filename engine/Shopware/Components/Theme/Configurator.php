<?php
/**
 * Shopware 4
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
namespace Shopware\Components\Theme;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Form as Form;
use Shopware\Components\Model as Model;
use Shopware\Models\Shop as Shop;
use Shopware\Theme;

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
     * @param Model\ModelManager $entityManager
     * @param Form\Persister\Theme $persister
     * @param Util $util
     */
    function __construct(
        Model\ModelManager $entityManager,
        Util $util,
        Form\Persister\Theme $persister)
    {
        $this->entityManager = $entityManager;
        $this->persister = $persister;
        $this->util = $util;
        $this->repository = $entityManager->getRepository('Shopware\Models\Shop\Template');
    }

    /**
     * Helper function which refresh the theme configuration element definition.
     *
     * @param Theme $theme
     */
    public function synchronize(Theme $theme)
    {
        $template = $this->getTemplate($theme);

        $container = new Form\Container\TabContainer('main_container');

        $this->injectConfig($theme, $container);

        $theme->createConfig($container);

        $this->persister->save($container, $template);

        $this->removeUnused($template, $container);

        $this->synchronizeSets($theme, $template);

    }

    /**
     * @param Theme $theme
     * @param Shop\Template $template
     */
    private function synchronizeSets(Theme $theme, Shop\Template $template)
    {
        $collection = new ArrayCollection();
        $theme->createConfigSets($collection);

        $synchronized = array();

        foreach ($collection as $item) {
            $existing = $this->getExistingConfigSet(
                $template->getConfigSets(),
                $item['name']
            );

            if (!$existing instanceof Shop\TemplateConfig\Set) {
                $existing = new Shop\TemplateConfig\Set();
                $template->getConfigSets()->add($existing);
            }

            $existing->setTemplate($template);

            $existing->fromArray($item);
            $synchronized[] = $existing;
        }

        foreach ($template->getConfigSets() as $existing) {
            $defined = $this->getExistingConfigSet(
                $synchronized,
                $existing->getName()
            );

            if ($defined instanceof Shop\TemplateConfig\Set) {
                continue;
            }

            $this->entityManager->remove($existing);
        }
        $this->entityManager->flush();
    }

    /**
     * Helper function which removes all unused configuration containers and elements
     * which are stored in the database but not in the passed container.
     *
     * @param Shop\Template $template
     * @param Form\Container $container
     */
    private function removeUnused(Shop\Template $template, Form\Container $container)
    {
        $existing = $this->getLayout($template);
        $structure = $this->getContainerNames($container);

        /**@var $layout Shop\TemplateConfig\Layout */
        foreach ($existing as $layout) {
            if (!in_array($layout->getName(), $structure['containers'])) {
                $this->entityManager->remove($layout);
            }
        }

        $existing = $this->getElements($template);

        /**@var $layout Shop\TemplateConfig\Element */
        foreach ($existing as $layout) {
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
     * @param Theme $theme
     * @return mixed
     */
    private function getTemplate(Theme $theme)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array(
            'template',
            'elements',
            'layouts'
        ))
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
     * @return array
     */
    private function getLayout(Shop\Template $template)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select('layout')
            ->from('Shopware\Models\Shop\TemplateConfig\Layout', 'layout')
            ->where('layout.templateId = :templateId')
            ->setParameter('templateId', $template->getId());

        return $builder->getQuery()->getResult();
    }

    /**
     * Returns all config elements of the passed template.
     * @param \Shopware\Models\Shop\Template $template
     * @return array
     */
    private function getElements(Shop\Template $template)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select('elements')
            ->from('Shopware\Models\Shop\TemplateConfig\Element', 'elements')
            ->where('elements.templateId = :templateId')
            ->setParameter('templateId', $template->getId());

        return $builder->getQuery()->getResult();
    }

    /**
     * Helper function to create an array with all container and element names.
     *
     * @param \Shopware\Components\Form\Container $container
     * @return array
     */
    private function getContainerNames(Form\Container $container)
    {
        $layout = array(
            'containers' => array(),
            'fields' => array()
        );

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

            } else if ($element instanceof Form\Field) {

                $layout['fields'][] = $element->getName();
            }
        }

        return $layout;
    }

    /**
     * @param Theme $theme
     * @param Form\Container\TabContainer $container
     * @return Form\Container\TabContainer
     */
    private function injectConfig(Theme $theme, Form\Container\TabContainer $container)
    {
        //check if theme wants to inject parent configuration
        if (!$theme->useInheritanceConfig() || $theme->getExtend() == null) {
            return;
        }

        /**@var $template Shop\Template */
        $template = $this->repository->findOneBy(array(
            'template' => $theme->getTemplate()
        ));

        //no parent configured? cancel injection.
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
     * @param Shop\TemplateConfig\Set[] $collection
     * @param $name
     * @return Shop\TemplateConfig\Set
     */
    private function getExistingConfigSet(array $collection, $name)
    {
        /**@var $item Shop\TemplateConfig\Set */
        foreach ($collection as $item) {
            if ($item->getName() == $name) {
                return $item;
            }
        }
        return null;
    }
}