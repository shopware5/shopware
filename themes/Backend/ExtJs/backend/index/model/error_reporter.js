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
 * todo@all: Documentation
 */
//{block name="backend/index/model/main/error_reporter"}
Ext.define('Shopware.apps.Index.model.ErrorReporter', {
    extend: 'Ext.data.Model',
    fields: [
        //{block name="backend/index/model/main/error_reporter/fields"}{/block}
        'id', 'message', 'filename', 'linenumber', 'created' ],
    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller="error_reporter" action="get"}',
            create: '{url controller="error_reporter" action="save"}',
            update: '{url controller="error_reporter" action="edit"}',
            destroy: '{url controller="error_reporter" action="delete"}'
        },
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
