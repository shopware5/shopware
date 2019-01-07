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
//{block name="backend/form/model/form"}
Ext.define('Shopware.apps.Form.model.Form', {

    /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * Function to copy the form
     *
     * @param [callback]
     */
    copy: function(callback) {
        Ext.Ajax.request({
            url: '{url controller="form" action="copyForm"}',
            method: 'POST',
            params : { id : this.data.id },
            success: function(response, opts) {
                if(typeof callback !== 'function') {
                    return false;
                }

                callback.call(this, arguments);
            }
        });
    },

    /**
     * The fields used for this model
     *
     * @array
     */
    fields : [
        //{block name="backend/form/model/form/fields"}{/block}
        { name : 'id',    type : 'int' },
        { name : 'active', type: 'boolean', defaultValue: true },
        { name : 'name',  type : 'string' },
        { name : 'email', type : 'email' },
        { name : 'emailSubject', type : 'string' },
        { name : 'emailTemplate', type : 'string' },
        { name : 'text', type : 'string' },
        { name : 'text2', type : 'string' },
        { name : 'shopIds' },
        { name : 'metaTitle', type : 'string' },
        { name : 'metaKeywords', type : 'string' },
        { name : 'metaDescription', type : 'string' }
    ],

    validations: [
        { field: 'name',  type: 'presence'},
        { field: 'email',  type: 'presence'},
        { field: 'emailSubject',  type: 'presence'},
        { field: 'emailTemplate', type: 'presence'}
    ],

    associations: [
        { type: 'hasMany', model: 'Shopware.apps.Form.model.Field', name: 'getFields', associationKey: 'fields'}
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
            read: '{url controller="form" action="getForms"}',
            create: '{url controller="form" action="createForm"}',
            update: '{url controller="form" action="updateForm"}',
            destroy: '{url controller="form" action="removeForm"}'
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
