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

namespace Shopware\Components\MultiEdit\Resource\Product;

/**
 * Grammar product resource. Will generate the grammar understood by the frontend lexer with all the supported columns
 */
class Grammar
{
    /**
     * Reference to an instance of the DqlHelper
     *
     * @var DqlHelper
     */
    protected $dqlHelper;

    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * @param DqlHelper                   $dqlHelper
     * @param \Enlight_Event_EventManager $eventManager
     */
    public function __construct($dqlHelper, $eventManager)
    {
        $this->dqlHelper = $dqlHelper;
        $this->eventManager = $eventManager;
    }

    /**
     * @return DqlHelper
     */
    public function getDqlHelper()
    {
        return $this->dqlHelper;
    }

    /**
     * @return \Enlight_Event_EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Generates attributes from column names. Attributes have a name which is known to the lexer and some
     * rules regarding the supported operators.
     * Most operator rules can be generated from the table definition.
     *
     * @throws \RuntimeException When the column was not defined
     *
     * @return array
     */
    public function generateAttributesFromColumns()
    {
        $columns = $this->getDqlHelper()->getAttributes();
        $columnInfo = [];

        foreach ($this->getDqlHelper()->getEntities() as $entity) {
            list($entity, $prefix) = $entity;
            $newMapping = [];
            $mappings = $this->getDqlHelper()->getEntityManager()->getClassMetadata($entity)->fieldMappings;
            foreach ($mappings as $key => $value) {
                $newMapping[strtoupper($prefix . '.' . $key)] = $value;
            }
            $columnInfo = array_merge($columnInfo, $newMapping);
        }

        $attributes = [];

        foreach ($columns as $column) {
            $mapping = $columnInfo[$column];
            $type = $mapping['type'];
            $formattedColumn = strtoupper($column);

            switch ($type) {
                case 'integer':
                case 'decimal':
                case 'float':
                    $attributes[$formattedColumn] = ['>', '>=', '<', '<=', '=', '!=', 'ISNULL', 'IN', 'NOT IN'];
                    break;
                case 'text':
                case 'string':
                    $attributes[$formattedColumn] = ['=', '~', '!~', 'IN', '!=', 'ISNULL', 'NOT IN'];
                    break;
                case 'boolean':
                    $attributes[$formattedColumn] = ['ISTRUE', 'ISFALSE', 'ISNULL'];
                    break;
                case 'date':
                    $attributes[$formattedColumn] = ['>', '>=', '<', '<=', '=', 'ISNULL'];
                    break;
                case 'datetime':
                    $attributes[$formattedColumn] = ['>', '>=', '<', '<=', '=', 'ISNULL'];
                    break;
                default:
                    // Allow custom types. If not event handles the unknown type
                    // an exception will be thrown
                    if ($event = $this->getEventManager()->notifyUntil(
                        'SwagMultiEdit_Product_Grammar_generateAttributesFromColumns_Type_' . ucfirst(strtolower($type)),
                        [
                            'subject' => $this,
                            'type' => $type,
                            'mapping' => $mapping,
                        ]
                    )) {
                        $attributes[$formattedColumn] = $event->getReturn();
                    } else {
                        throw new \RuntimeException(sprintf('Column with type %s was not configured, yet', $type));
                    }
            }
        }

        return $attributes;
    }

    /**
     * Returns an array which represents the grammar of out product resource
     *
     * @return array
     */
    public function getGrammar()
    {
        $grammar = [
            'nullaryOperators' => [
                'HASIMAGE' => '',
                'HASNOIMAGE' => '',
                'ISMAIN' => '',
                'HASPROPERTIES' => '',
                'HASCONFIGURATOR' => '',
                'HASBLOCKPRICE' => '',
            ],
            'unaryOperators' => [
                'ISTRUE' => '',
                'ISFALSE' => '',
                'ISNULL' => '',
            ],
            'binaryOperators' => [
                'IN' => ['('],
                'NOT IN' => ['('],
                '>=' => ['/(^-{0,1}[0-9.]+$)/', '/"(.*?)"/'],
                '=' => ['/(^-{0,1}[0-9.]+$)/', '/"(.*?)"/'],
                '!=' => ['/(^-{0,1}[0-9.]+$)/', '/"(.*?)"/'],
                '!~' => ['/"(.*?)"/'],
                '~' => ['/"(.*?)"/'],
                '>' => ['/(^-{0,1}[0-9.]+$)/', '/"(.*?)"/'],
                '<=' => ['/(^-{0,1}[0-9.]+$)/', '/"(.*?)"/'],
                '<' => ['/(^-{0,1}[0-9.]+$)/', '/"(.*?)"/'],
            ],
            'subOperators' => ['(', ')'],
            'boolOperators' => ['AND', 'OR'],
            'values' => ['/"(.*?)"/', '/^-{0,1}[0-9.]+$/'],
            'attributes' => $this->generateAttributesFromColumns(),
        ];

        // Allow users to add own operators / rules
        $grammar = $this->getEventManager()->filter(
            'SwagMultiEdit_Product_Grammar_getGrammar_filterGrammar',
            $grammar,
            ['subject' => $this]
        );

        return $grammar;
    }
}
