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
 * @package    Mail
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{block name="backend/mail/model/mail"}
Ext.define('Shopware.apps.Mail.model.Mail', {

    /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     *
     * @array
     */
    fields : [
        //{block name="backend/mail/model/mail/fields"}{/block}
        { name: 'id',          type: 'int' },
        { name: 'name',        type: 'string' },
        { name: 'fromName',    type: 'string' },
        { name: 'fromMail',    type: 'email' },
        { name: 'subject',     type: 'string' },
        { name: 'content',     type: 'string' },
        { name: 'contentHtml', type: 'string' },
        { name: 'isHtml',      type: 'boolean' },
        { name: 'attachment',  type: 'string' },
        { name: 'type',        type: 'string' },
        { name: 'context' },
        { name: 'contextPath' }
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
            read: '{url controller="mail" action="getMails"}',
            create: '{url controller="mail" action="createMail"}',
            update: '{url controller="mail" action="updateMail"}',
            destroy: '{url controller="mail" action="removeMail"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data'
        }
    },

    /**
     * Function to copy the form
     *
     * @param [callback]
     */
    copy: function(callback) {
        Ext.Ajax.request({
            url: '{url controller="mail" action="copyMail"}',
            method: 'POST',
            params : { id : this.data.id },
            success: function(response, opts) {
                if(typeof callback !== 'function') {
                    return false;
                }

                callback.call(this, true, response);
            },
            failure: function(response, opts) {
                if(typeof callback !== 'function') {
                    return false;
                }

                callback.call(this, false, response);
            }
        });
    }
});
//{/block}
