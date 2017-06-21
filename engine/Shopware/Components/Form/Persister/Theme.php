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
use Shopware\Components\Form as Form;
use Shopware\Components\Form\Container;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Template;
use Shopware\Models\Shop\TemplateConfig;

/**
 * Class Base
 */
class Theme implements Form\Interfaces\Persister
{
    /**
     * @var ModelManager
     */
    protected $entityManager;

    /**
     * @param ModelManager $entityManager
     */
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
     * @param Form\Interfaces\Container $container
     * @param Template                  $template
     * @param TemplateConfig\Layout     $parent
     */
    private function saveContainer(Form\Interfaces\Container $container, Template $template, TemplateConfig\Layout $parent = null)
    {
        $class = get_class($container);

        $entity = $this->createContainer($container, $template, $parent);

        //do class switch to route the container to the responsible save function.
        switch ($class) {
            case 'Shopware\\Components\\Form\\Container\\TabContainer':
                $entity = $this->saveTabContainer($entity);
                break;

            case 'Shopware\\Components\\Form\\Container\\Tab':
                $entity = $this->saveTab($entity, $container);
                break;

            case 'Shopware\\Components\\Form\\Container\\FieldSet':
                $entity = $this->saveFieldSet($entity, $container);
                break;
        }

        //check for recursion
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
     * @param Container             $container
     * @param Template              $template
     * @param TemplateConfig\Layout $parent
     *
     * @return TemplateConfig\Layout
     */
    private function createContainer(
        Container $container,
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
     * @param TemplateConfig\Layout   $entity
     * @param Form\Container\FieldSet $container
     *
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
     * @param TemplateConfig\Layout $entity
     *
     * @return TemplateConfig\Layout
     */
    private function saveTabContainer(TemplateConfig\Layout $entity)
    {
        $entity->setType('theme-tab-panel');
        $this->entityManager->persist($entity);

        return $entity;
    }

    /**
     * @param TemplateConfig\Layout $entity
     * @param Form\Container\Tab    $container
     *
     * @return TemplateConfig\Layout
     */
    private function saveTab(TemplateConfig\Layout $entity, Form\Container\Tab $container)
    {
        $entity->setType('theme-tab');
        $entity->setTitle($container->getTitle());
        $this->entityManager->persist($entity);

        return $entity;
    }

    /**
     * @param Form\Interfaces\Field $field
     * @param Template              $template
     * @param TemplateConfig\Layout $parent
     */
    private function saveField(Form\Interfaces\Field $field, Template $template, TemplateConfig\Layout $parent)
    {
        /** @var $field Form\Field */
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
                /* @var $field Form\Field\Text */
                $data += ['type' => 'theme-text-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Boolean':
                /* @var $field Form\Field\Boolean */
                $data += ['type' => 'theme-checkbox-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Date':
                /* @var $field Form\Field\Date */
                $data += ['type' => 'theme-date-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Color':
                /* @var $field Form\Field\Color */
                $data += ['type' => 'theme-color-picker'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Media':
                /* @var $field Form\Field\Media */
                $data += ['type' => 'theme-media-selection'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Number':
                /* @var $field Form\Field\Number */
                $data += ['type' => 'numberfield'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Em':
                /* @var $field Form\Field\Number */
                $data += ['type' => 'theme-em-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Percent':
                /* @var $field Form\Field\Number */
                $data += ['type' => 'theme-percent-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Pixel':
                /* @var $field Form\Field\Number */
                $data += ['type' => 'theme-pixel-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\TextArea':
                /* @var $field Form\Field\Number */
                $data += ['type' => 'theme-text-area-field'];
                break;
            case 'Shopware\\Components\\Form\\Field\\Selection':
                /* @var $field Form\Field\Selection */
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
     * @param PersistentCollection $collection
     * @param string               $name
     *
     * @return TemplateConfig\Element
     */
    private function checkExistingElement(PersistentCollection $collection, $name)
    {
        /** @var $element TemplateConfig\Element */
        foreach ($collection as $element) {
            if ($element->getName() == $name) {
                return $element;
            }
        }

        return new TemplateConfig\Element();
    }

    /**
     * @param PersistentCollection $collection
     * @param string               $name
     *
     * @return TemplateConfig\Layout
     */
    private function checkExistingLayout(PersistentCollection $collection, $name)
    {
        /** @var $element TemplateConfig\Layout */
        foreach ($collection as $element) {
            if ($element->getName() == $name) {
                return $element;
            }
        }

        return new TemplateConfig\Layout();
    }
}
