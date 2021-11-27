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

use Doctrine\Common\Collections\Collection;
use Shopware\Components\Form;
use Shopware\Components\Form\Container;
use Shopware\Components\Form\Container\FieldSet;
use Shopware\Components\Form\Container\Tab;
use Shopware\Components\Form\Container\TabContainer;
use Shopware\Components\Form\Field;
use Shopware\Components\Form\Field\Boolean;
use Shopware\Components\Form\Field\Color;
use Shopware\Components\Form\Field\Date;
use Shopware\Components\Form\Field\Em;
use Shopware\Components\Form\Field\Media;
use Shopware\Components\Form\Field\Number;
use Shopware\Components\Form\Field\Percent;
use Shopware\Components\Form\Field\Pixel;
use Shopware\Components\Form\Field\Selection;
use Shopware\Components\Form\Field\Text;
use Shopware\Components\Form\Field\TextArea;
use Shopware\Components\Form\Interfaces\Container as ContainerInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Shop\Template;
use Shopware\Models\Shop\TemplateConfig\Element;
use Shopware\Models\Shop\TemplateConfig\Layout;

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
     * @param Template $reference
     */
    public function save(ContainerInterface $container, $reference)
    {
        $this->saveContainer($container, $reference);
        $this->entityManager->flush();
    }

    private function saveContainer(ContainerInterface $container, Template $template, ?Layout $parent = null): void
    {
        $class = \get_class($container);

        $entity = $this->createContainer($container, $template, $parent);

        // Do class switch to route the container to the responsible save function.
        switch ($class) {
            case TabContainer::class:
                $entity = $this->saveTabContainer($entity);
                break;

            case Tab::class:
                if ($container instanceof Tab) {
                    $entity = $this->saveTab($entity, $container);
                }
                break;

            case FieldSet::class:
                if ($container instanceof FieldSet) {
                    $entity = $this->saveFieldSet($entity, $container);
                }
                break;
        }

        // Check for recursion
        foreach ($container->getElements() as $element) {
            if ($element instanceof ContainerInterface) {
                $this->saveContainer($element, $template, $entity);
            } elseif ($element instanceof Field) {
                $this->saveField($element, $template, $entity);
            }
        }
    }

    /**
     * Helper function to create a generic ConfigLayout entity.
     */
    private function createContainer(
        ContainerInterface $container,
        Template $template,
        ?Layout $parent = null
    ): Layout {
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

    private function saveFieldSet(Layout $entity, FieldSet $container): Layout
    {
        $entity->setType('theme-field-set');
        $entity->setTitle($container->getTitle());
        $this->entityManager->persist($entity);

        return $entity;
    }

    private function saveTabContainer(Layout $entity): Layout
    {
        $entity->setType('theme-tab-panel');
        $this->entityManager->persist($entity);

        return $entity;
    }

    private function saveTab(Layout $entity, Tab $container): Layout
    {
        $entity->setType('theme-tab');
        $entity->setTitle($container->getTitle());
        $this->entityManager->persist($entity);

        return $entity;
    }

    private function saveField(Field $field, Template $template, Layout $parent): void
    {
        $lessCompatible = true;
        if (\array_key_exists('lessCompatible', $field->getAttributes())) {
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

        $class = \get_class($field);

        switch ($class) {
            case Text::class:
                $data += ['type' => 'theme-text-field'];
                break;
            case Boolean::class:
                $data += ['type' => 'theme-checkbox-field'];
                break;
            case Date::class:
                $data += ['type' => 'theme-date-field'];
                break;
            case Color::class:
                $data += ['type' => 'theme-color-picker'];
                break;
            case Media::class:
                $data += ['type' => 'theme-media-selection'];
                break;
            case Number::class:
                $data += ['type' => 'numberfield'];
                break;
            case Em::class:
                $data += ['type' => 'theme-em-field'];
                break;
            case Percent::class:
                $data += ['type' => 'theme-percent-field'];
                break;
            case Pixel::class:
                $data += ['type' => 'theme-pixel-field'];
                break;
            case TextArea::class:
                $data += ['type' => 'theme-text-area-field'];
                break;
            case Selection::class:
                $data += [
                    'type' => 'theme-select-field',
                    'selection' => $field->getStore(),
                ];
                break;
        }

        $entity = $this->checkExistingElement($template->getElements(), $field->getName());

        $entity->fromArray($data);
        $entity->setTemplate($template);
        $entity->setContainer($parent);

        $this->entityManager->persist($entity);
    }

    /**
     * @param Collection<array-key, Element> $collection
     */
    private function checkExistingElement(Collection $collection, string $name): Element
    {
        foreach ($collection as $element) {
            if ($element->getName() === $name) {
                return $element;
            }
        }

        return new Element();
    }

    /**
     * @param Collection<array-key, Layout> $collection
     */
    private function checkExistingLayout(Collection $collection, string $name): Layout
    {
        foreach ($collection as $element) {
            if ($element->getName() === $name) {
                return $element;
            }
        }

        return new Layout();
    }
}
