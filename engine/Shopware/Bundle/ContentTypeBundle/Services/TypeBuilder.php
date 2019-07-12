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

namespace Shopware\Bundle\ContentTypeBundle\Services;

use Shopware\Bundle\ContentTypeBundle\Field\DummyField;
use Shopware\Bundle\ContentTypeBundle\Field\TypeField;
use Shopware\Bundle\ContentTypeBundle\Field\TypeGrid;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;
use Shopware\Bundle\ContentTypeBundle\Structs\Fieldset;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;

class TypeBuilder
{
    /**
     * @var array
     */
    private $fields;

    public function __construct(array $fields, array $types)
    {
        $this->fields = $fields;

        foreach (array_keys($types) as $type) {
            $this->fields[$type . '-field'] = TypeField::class;
            $this->fields[$type . '-grid'] = TypeGrid::class;
        }
    }

    public function createType(string $name, array $type): Type
    {
        $class = new Type();
        $class->setName($type['name']);
        $class->setInternalName($name);

        if (isset($type['source'])) {
            $class->setSource($type['source']);
        }

        if (isset($type['showInFrontend'])) {
            $class->setShowInFrontend($type['showInFrontend']);
        }

        if (isset($type['menuIcon'])) {
            $class->setMenuIcon($type['menuIcon']);
        }

        if (isset($type['menuPosition'])) {
            $class->setMenuPosition($type['menuPosition']);
        }

        if (isset($type['menuParent'])) {
            $class->setMenuParent($type['menuParent']);
        }

        if (isset($type['custom'])) {
            $class->setCustom($type['custom']);
        }

        if (isset($type['viewTitleFieldName'])) {
            $class->setViewTitleFieldName($type['viewTitleFieldName']);
        }

        if (isset($type['viewDescriptionFieldName'])) {
            $class->setViewDescriptionFieldName($type['viewDescriptionFieldName']);
        }

        if (isset($type['viewImageFieldName'])) {
            $class->setViewImageFieldName($type['viewImageFieldName']);
        }

        if (isset($type['viewMetaTitleFieldName'])) {
            $class->setViewMetaTitleFieldName($type['viewMetaTitleFieldName']);
        }

        if (isset($type['viewMetaDescriptionFieldName'])) {
            $class->setViewMetaDescriptionFieldName($type['viewMetaDescriptionFieldName']);
        }

        if (isset($type['seoUrlTemplate'])) {
            $class->setSeoUrlTemplate($type['seoUrlTemplate']);
        }

        if (isset($type['seoRobots'])) {
            $class->setSeoRobots($type['seoRobots']);
        }

        $fieldSets = [];
        $fields = [];
        foreach ($type['fieldSets'] as $fieldSet) {
            $fieldSet = $this->createFieldset($fieldSet);
            $fieldSets[] = $fieldSet;
            $fields = array_merge($fields, $fieldSet->getFields());
        }

        $class->setFields($fields);
        $class->setFieldSets($fieldSets);

        return $class;
    }

    public function createFieldset(array $fieldset): Fieldset
    {
        $class = new Fieldset();

        if (isset($fieldset['label'])) {
            $class->setLabel($fieldset['label']);
        }

        if (isset($fieldset['options'])) {
            $class->setOptions($fieldset['options']);
        }

        $fields = [];
        foreach ($fieldset['fields'] as $field) {
            $fields[] = $this->createField($field);
        }

        $class->setFields($fields);

        return $class;
    }

    public function createField(array $field): Field
    {
        $class = new Field();
        $class->setName($field['name']);
        $class->setLabel($field['label']);
        $class->setTypeName($field['type']);
        $className = $this->getClassByAlias($field['type']) ?: $field['type'];

        if (empty($className) || !class_exists($className)) {
            $className = DummyField::class;
        }

        $class->setType(new $className());

        if (isset($field['showListing'])) {
            $class->setShowListing($field['showListing']);
        }

        if (isset($field['searchAble'])) {
            $class->setSearchAble($field['searchAble']);
        }

        if (isset($field['translatable'])) {
            $class->setTranslatable((bool) $field['translatable']);
        }

        if (isset($field['description'])) {
            $class->setDescription($field['description']);
        }

        if (isset($field['helpText'])) {
            $class->setHelpText($field['helpText']);
        }

        if (isset($field['custom'])) {
            $class->setCustom($field['custom']);
        }

        if (isset($field['options'])) {
            $class->setOptions($field['options']);
        }

        if (isset($field['store'])) {
            $class->setStore($field['store']);
        }

        if (isset($field['required'])) {
            $class->setRequired($field['required']);
        }

        return $class;
    }

    public function getClassByAlias(string $alias)
    {
        return $this->fields[$alias] ?? null;
    }
}
