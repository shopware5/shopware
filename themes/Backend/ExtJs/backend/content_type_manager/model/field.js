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

// {block name="backend/content_type_manager/model/field"}
Ext.define('Shopware.apps.ContentTypeManager.model.Field', {
    extend: 'Ext.data.Model',

    /**
     * @var array
     */
    fields: [
        {
            name: 'name',
            type: 'string'
        },
        {
            name: 'type',
            type: 'string'
        },
        {
            name: 'label',
            type: 'string'
        },
        {
            name: 'required',
            type: 'boolean',
            defaultValue: false
        },
        {
            name: 'showListing',
            type: 'boolean',
            defaultValue: true
        },
        {
            name: 'searchAble',
            type: 'boolean',
            defaultValue: true
        },
        {
            name: 'translatable',
            type: 'boolean',
            defaultValue: true
        },
        {
            name: 'helpText',
            type: 'string',
        },
        {
            name: 'description',
            type: 'string',
        },
        {
            name: 'custom',
            type: 'any',
            useNull: true,
            defaultValue: null
        },
        {
            name: 'options',
            type: 'object',
            useNull: true,
            defaultValue: null
        },
        {
            name: 'flags',
            type: 'object',
            defaultValue: {}
        }
    ],
});
// {/block}
