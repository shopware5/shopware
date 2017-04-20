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
 * @package    NewsletterManager
 * @subpackage Model
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Model - Mailing model
 * Model for the 'mailing' store
 */
//{block name="backend/newsletter_manager/model/mailing"}
Ext.define('Shopware.apps.NewsletterManager.model.Mailing', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        //{block name="backend/newsletter_manager/model/mailing/fields"}{/block}
        { name: 'id', type: 'int', useNull: true },
        { name: 'date', type: 'date', dateFormat: 'Y-m-d' },
        { name: 'locked', type: 'date' },
        { name: 'subject', type: 'string' },
        { name: 'status', type: 'int' },
        { name: 'addresses', type: 'int' },
        { name: 'customerGroup', type: 'string' },
        { name: 'senderName', type: 'string' },
        { name: 'senderMail', type: 'string' },
        { name: 'recipients', type: 'int' },
        { name: 'publish', type: 'boolean' },
        { name: 'read', type: 'string', defaultValue: 0 },
        { name: 'clicked', type: 'int', defaultValue: 0 },
        { name: 'revenue', type: 'float', defaultValue: 0 },
        { name: 'plaintext', type: 'boolean' },
        { name: 'languageId', type: 'int' },
        { name: 'timedDelivery', type: 'date' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping
         * @object
         */
        api: {
            create: '{url action="createNewsletter"}',
            update: '{url action="updateNewsletter"}',
            destroy:'{url action="deleteNewsletter"}'
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
     * Define the associations of the mailing model.
     * @array
     */
    associations:[
        // Addresses which have already received this mail
//        { type:'hasMany', model:'Shopware.apps.NewsletterManager.model.Recipient', name:'getAddresses', associationKey:'addresses' },
        // Container elements
        { type:'hasMany', model:'Shopware.apps.NewsletterManager.model.Container', name:'getContainers', associationKey:'containers' },
        // Groups which this newsletter addresses
        { type:'hasMany', model:'Shopware.apps.NewsletterManager.model.RecipientGroup', name:'getGroups', associationKey:'groups' },
        // Orders which made after reading this mail
        { type:'hasMany', model:'Shopware.apps.Base.model.Order', name:'getOrders', associationKey:'orders' }
    ]

});
//{/block}
