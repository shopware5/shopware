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

// {block name="backend/content_type_manager/model/type"}
Ext.define('Shopware.apps.ContentTypeManager.model.Type', {
    extend: 'Shopware.data.Model',

    /**
     * configure the model
     * @returns { Object }
     */
    configure: function () {
        return {
            controller: 'ContentTypeManager',
            detail: 'Shopware.apps.ContentTypeManager.view.detail.Type'
        };
    },

    /**
     * @var array
     */
    fields: [
        {
            name: 'id',
            type: 'string'
        },
        {
            name: 'internalName',
            type: 'string'
        },
        {
            name: 'name',
            type: 'string'
        },
        {
            name: 'singularName',
            type: 'string'
        },
        {
            name: 'source',
            type: 'string'
        },
        {
            name: 'showInFrontend',
            type: 'bool',
            defaultValue: false
        },
        {
            name: 'menuIcon',
            type: 'string',
            defaultValue: 'sprite-application-block'
        },
        {
            name: 'viewTitleFieldName',
            type: 'string',
        },
        {
            name: 'viewDescriptionFieldName',
            type: 'string',
        },
        {
            name: 'viewImageFieldName',
            type: 'string',
        },
        {
            name: 'viewMetaTitleFieldName',
            type: 'string',
        },
        {
            name: 'viewMetaDescriptionFieldName',
            type: 'string',
        },
        {
            name: 'groupingState',
            type: 'int',
            convert: function (value, record) {
                if (record.get('source')) {
                    return 1;
                }

                return 0;
            }
        },
        {
            name: 'seoUrlTemplate',
            type: 'string',
            defaultValue: '{literal}{$type.name}/{$item[$type.viewTitleFieldName]}{/literal}'
        },
        {
            name: 'seoRobots',
            type: 'string',
            defaultValue: 'index,follow'
        },
        {
            name: 'controllerName',
            type: 'string'
        }
    ],

    associations: [
        {
            type: 'hasMany',
            model: 'Shopware.apps.ContentTypeManager.model.Field',
            name: 'getFields',
            associationKey: 'fields'
        },
        {
            type: 'hasMany',
            model: 'Shopware.apps.ContentTypeManager.model.Url',
            name: 'getUrls',
            associationKey: 'urls'
        },
    ]
});
// {/block}
