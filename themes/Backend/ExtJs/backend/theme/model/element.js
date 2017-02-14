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

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/theme/main}

//{block name="backend/theme/model/element"}

Ext.define('Shopware.apps.Theme.model.Element', {
    extend: 'Shopware.data.Model',

    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'type', type: 'string' },
        { name: 'name', type: 'string' },
        { name: 'helpText', type: 'string', mapping: 'help' },
        { name: 'position', type: 'int' },
        { name: 'defaultValue', useNull: true },
        { name: 'selection', useNull: true },
        { name: 'fieldLabel', type: 'string', useNull: true },
        { name: 'supportText', type: 'string', useNull: true },
        { name: 'allowBlank', type: 'boolean', defaultValue: true },
        { name: 'attributes' },

        //no type definition for auto typing
        { name: 'tab' },

        //mapping fields which used only for the form field generation
        { name: 'value', mapping: 'defaultValue' },
        { name: 'xtype', type: 'string', mapping: 'type' },
        { name: 'elementId', type: 'int', mapping: 'id' }
    ],

    associations: [
        {
            type: 'hasMany',
            model: 'Shopware.apps.Theme.model.ConfigValue',
            name: 'getConfigValues',
            associationKey: 'values'
        }
    ]
});


//{/block}
