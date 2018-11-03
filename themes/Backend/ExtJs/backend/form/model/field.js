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
 *
 * @category   Shopware
 * @package    Form
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/form/model/field"}
Ext.define('Shopware.apps.Form.model.Field', {

    /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields : [
        //{block name="backend/form/model/field/fields"}{/block}
        { name : 'id',       type : 'int' },
        { name : 'name',     type : 'string' },
        { name : 'label',    type : 'string' },
        { name : 'typ',      type : 'string' },
        { name : 'class',    type : 'string' },
        { name : 'value',    type : 'string' },
        { name : 'note',     type : 'string' },
        { name : 'errorMsg', type : 'string' },
        { name : 'required', type : 'bool' },
        { name : 'position', type : 'int' }
    ],

    validations: [
        { field: 'name',  type: 'presence'},
        { field: 'label', type: 'presence'},
        { field: 'name',  type: 'presence'}
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read: '{url controller="form" action="getFields"}',
            create: '{url controller="form" action="createField"}',
            update: '{url controller="form" action="updateField"}',
            destroy: '{url controller="form" action="removeField"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});
//{/block}
