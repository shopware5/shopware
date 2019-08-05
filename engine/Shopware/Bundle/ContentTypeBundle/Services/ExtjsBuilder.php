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

use Shopware\Bundle\ContentTypeBundle\Structs\Field;
use Shopware\Bundle\ContentTypeBundle\Structs\Type;
use Shopware_Components_Snippet_Manager as Snippets;

class ExtjsBuilder implements ExtjsBuilderInterface
{
    /**
     * @var Snippets
     */
    private $snippets;

    public function __construct(Snippets $snippets)
    {
        $this->snippets = $snippets;
    }

    public function buildModelFields(Type $type): array
    {
        $fields = [
            [
                'name' => 'id',
                'type' => 'int',
            ],
        ];

        foreach ($type->getFields() as $field) {
            $fields[] = [
                'name' => $field->getName(),
                'type' => $field->getType()::getExtjsType(),
                'useNull' => !$field->isRequired(),
            ];
        }

        return $fields;
    }

    public function buildColumns(Type $type): array
    {
        $fields = [];

        foreach ($type->getFields() as $field) {
            if (!$field->isShowListing()) {
                continue;
            }

            $this->translateField($type, $field);

            $fields[$field->getName()] = [
                'header' => $field->getLabel(),
            ];
        }

        return $fields;
    }

    public function buildFieldSets(Type $type): array
    {
        $sets = [];

        foreach ($type->getFieldSets() as $fieldSet) {
            $fields = [];

            foreach ($fieldSet->getFields() as $field) {
                $this->translateField($type, $field);

                $fields[$field->getName()] = [
                    'fieldLabel' => $field->getLabel(),
                    'xtype' => $field->getType()::getExtjsField(),
                    'anchor' => '100%',
                    'translatable' => (bool) $field->isTranslatable(),
                    'supportText' => $field->getDescription(),
                    'helpText' => $field->getHelpText(),
                    'allowBlank' => !$field->isRequired(),
                ];

                $fields[$field->getName()] = array_merge($fields[$field->getName()], $field->getType()::getExtjsOptions($field), $field->getOptions());
            }

            $sets[] = array_merge([
                'title' => $fieldSet->getLabel(),
                'autoScroll' => true,
                'fields' => $fields,
                'anchor' => '100%',
            ], $fieldSet->getOptions());
        }

        return $sets;
    }

    private function translateField(Type $type, Field $field): void
    {
        $namespace = $this->snippets->getNamespace($type->getSnippetNamespaceBackend());

        $field->setLabel($namespace->get(strtolower($field->getName()) . '_label', $field->getLabel(), true));

        if ($helpText = $namespace->get(strtolower($field->getName()) . '_helpText')) {
            $field->setHelpText($helpText);
        } elseif ($field->getHelpText()) {
            $namespace->get(strtolower($field->getName()) . '_helpText', $field->getHelpText(), true);
        }

        if ($description = $namespace->get(strtolower($field->getName()) . '_description')) {
            $field->setDescription($description);
        } elseif ($field->getDescription()) {
            $namespace->get(strtolower($field->getName()) . '_description', $field->getDescription(), true);
        }
    }
}
