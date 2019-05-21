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

//{block name="backend/theme/model/theme"}

Ext.define('Shopware.apps.Theme.model.Theme', {
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'Theme',
            detail: 'Shopware.apps.Theme.view.create.Theme'
        };
    },

    fields: [
        { name : 'id', type: 'int', useNull: true },
        { name : 'name', type: 'string' },
        { name : 'template', type: 'string' },
        { name : 'description', type: 'string' },
        { name : 'author', type: 'string' },
        { name : 'path', type: 'string' },
        { name : 'license', type: 'string' },
        { name : 'screen', type: 'string' },
        { name : 'esi', type: 'boolean' },
        { name : 'emotion', type: 'boolean' },
        { name : 'style', type: 'boolean' },
        { name : 'version', type: 'int' },
        { name : 'pluginId', type: 'int' },

        { name : 'themeInfo', type: 'string' },

        { name : 'parentId', type: 'int', useNull: true, defaultValue: null },

        { name : 'screen', type: 'string' },
        { name : 'hasConfig', type: 'int' },
        { name : 'hasConfigSet', type: 'boolean' },

        { name : 'enabled', type: 'boolean', defaultValue: false },
        { name : 'preview', type: 'boolean', defaultValue: false }
    ],

    associations: [
        {
            relation: 'OneToMany',
            type: 'hasMany',
            model: 'Shopware.apps.Theme.model.Layout',
            name: 'getLayout',
            associationKey: 'configLayout'
        },
        {
            //only for save action.
            type: 'hasMany',
            model: 'Shopware.apps.Theme.model.ConfigValue',
            name: 'getConfigValues',
            associationKey: 'values'
        },
        {
            //read only
            type: 'hasMany',
            model: 'Shopware.apps.Theme.model.ConfigSet',
            name: 'getConfigSets',
            associationKey: 'configSets'
        }
    ]

});

//{/block}
