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

namespace Shopware\Components\Form\Persister;

use Doctrine\ORM\PersistentCollection;
use Shopware\Components\Form;
use Shopware\Components\Form\Container;
use Shopware\Components\Form\Interfaces\Container as ContainerInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Template;
use Shopware\Models\Shop\TemplateConfig;

class Theme implements Form\Interfaces\Persister
{
    /**
     * @var ModelManager
     */
    protected $entityManager;

    public function __construct(ModelManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Saves the given container to the database, files or wherever.
     *
     * @param \Shopware\Components\Form\Interfaces\Container $container
     * @param Template                                       $reference
     */
    public function save(Form\Interfaces\Container $container, $reference)
    {
        $this->saveContainer($container, $reference);
        $this->entityManager->flush();
    }

    /**
     * @param TemplateConfig\Layout $parent
     */
    private function saveContainer(Form\Interfaces\Container $container, Template $template, TemplateConfig\Layout $parent = null)
    {
        $class = get_class($container);

        $entity = $this->createContainer($container, $template, $parent);

        // Do class switch to route the container to the responsible save function.
        switch ($class) {
            case 'Shopware\\Components\\Form\\Container\\TabContainer':
                $entity = $this->saveTabContainer($entity);
                break;

            case 'Shopware\\Components\\Form\\Container\\Tab':
                /** @var Form\Container\Tab $container */
                $entity = $this->saveTab($entity, $container);
                break;

            case 'Shopware\\Components\\Form\\Container\\FieldSet':
                /** @var Form\Container\FieldSet $container */
                $entity = $this->saveFieldSet($entity, $container);
                break;
        }

        // Check for recursion
        foreach ($container->getElements() as $element) {
            if ($element instanceof Form\Interfaces\Container) {
                $this->saveContainer($element, $template, $entity);
            } elseif ($element instanceof Form\Interfaces\Field) {
                $this->saveField($element, $template, $entity);
            }
        }
    }

    /**
     * Helper function to create a generic ConfigLayout entity.
     *
     * @param TemplateConfig\Layout $parent
     *
     * @return TemplateConfig\Layout
     */
    private function createContainer(
        ContainerInterface $container,
        Template $template,
        TemplateConfig\Layout $parent = null)
    {
        $entity = $this->checkExistingLayout(
            $template->getLayouts(),
            $container->getName()
        );

        $entity->setTemplate($template);
        $entity->setParent($parent);
        $entity->setName($container->getName());
        $entity->setAttributes($container->getAttributes());

        return $entity;
    }

    /**
     * @return TemplateConfig\Layout
     */
    private function saveFieldSet(TemplateConfig\Layout $entity, Form\Container\FieldSet $container)
    {
        $entity->setType('theme-field-set');
        $entity->setTitle($container->getTitle());
        $this->entityManager->persist($entity);

        return $entity;
    }

    /**
     * @return TemplateConfig\Layout
     */
    private function saveTabContainer(TemplateConfig\Layout $entity)
    {
        $entity->setType('theme-tab-panel');
        $this->entityManager->persist($entity);

        return $entity;
    }

    /**
     * @return TemplateConfig\Layout
     */
    private function saveTab(TemplateConfig\Layout $entity, Form\Container\Tab $container)
    {
        $entity->setType('theme-tab');
        $entity->setTitle($container->getTitle());
        $this->entityManager->persist($entity);

        return $entity;
    }

    private function saveField(Form\Interfaces\Field $field, Template $template, TemplateConfig\Layout $parent)
    {
        /** @var Form\Field $field */
        $lessCompatible = true;
        if (array_key_exists('lessCompatible', $field->getAttributes())) {
            $attributes = $field->getAttributes();
            $lessCompatible = (bool) $attributes['lessCompatible'];
        }

        $data = [
            'attributes' => $field->getAttributes(),
            'fieldLabel' => $field->getLabel(),
            'name' => $field->getName(),
            'defaultValue' => $field->getDefaultValue(),
            'supportText' => $field->getHelp(),
            'allowBlank' => !$field->isRequired(),
            'lessCompatible' => $lessCompatible,
        ];

        $class = get_class($field);

        switch ($class) {
            case 'Shopware\\Components\\Form\\Field\\Text':
                /* @var Form\Field\Text $field */
                $data += ['type' => 'theme-text-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Boolean':
                /* @var Form\Field\Boolean $field */
                $data += ['type' => 'theme-checkbox-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Date':
                /* @var Form\Field\Date $field */
                $data += ['type' => 'theme-date-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Color':
                /* @var Form\Field\Color $field */
                $data += ['type' => 'theme-color-picker'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Media':
                /* @var Form\Field\Media $field */
                $data += ['type' => 'theme-media-selection'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Number':
                /* @var Form\Field\Number $field */
                $data += ['type' => 'numberfield'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Em':
                /* @var Form\Field\Number $field */
                $data += ['type' => 'theme-em-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Percent':
                /* @var Form\Field\Number $field */
                $data += ['type' => 'theme-percent-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Pixel':
                /* @var Form\Field\Number $field */
                $data += ['type' => 'theme-pixel-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\TextArea':
                /* @var Form\Field\Number $field */
                $data += ['type' => 'theme-text-area-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Selection':
                /* @var Form\Field\Selection $field */
                $data += [
                    'type' => 'theme-select-field',
                    'selection' => $field->getStore(),
                ];
                break;
        }

        $entity = $this->checkExistingElement(
            $template->getElements(),
            $field->getName()
        );

        $entity->fromArray($data);
        $entity->setTemplate($template);
        $entity->setContainer($parent);

        $this->entityManager->persist($entity);
    }

    /**
     * @param string $name
     *
     * @return TemplateConfig\Element
     */
    private function checkExistingElement(PersistentCollection $collection, $name)
    {
        /** @var TemplateConfig\Element $element */
        foreach ($collection as $element) {
            if ($element->getName() == $name) {
                return $element;
            }
        }

        return new TemplateConfig\Element();
    }

    /**
     * @param string $name
     *
     * @return TemplateConfig\Layout
     */
    private function checkExistingLayout(PersistentCollection $collection, $name)
    {
        /** @var TemplateConfig\Layout $element */
        foreach ($collection as $element) {
            if ($element->getName() == $name) {
                return $element;
            }
        }

        return new TemplateConfig\Layout();
    }
}
