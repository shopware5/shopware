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
 * Shopware Model - recipient model
 * This model holds a recipient record
 */
//{block name="backend/newsletter_manager/model/recipient"}
Ext.define('Shopware.apps.NewsletterManager.model.Recipient', {
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
        //{block name="backend/newsletter_manager/model/recipient/fields"}{/block}
        { name: 'id', type: 'int' },
        { name: 'isCustomer', type: 'boolean' },
        { name: 'email', type: 'string' },
        { name: 'groupId', type: 'int' },
        { name: 'lastNewsletterId', type: 'int' },
        { name: 'added', type: 'date' },
        { name: 'doubleOptinConfirmed', type: 'date' }
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
            create: '{url action="createRecipient"}',
            update: '{url action="updateRecipient"}'
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
     * Define the associations of the order model.
     * One order has a customer, many details, billing- & shipping address and a payment information.
     * @array
     */
    associations:[
        { type:'hasMany', model:'Shopware.apps.NewsletterManager.model.NewsletterGroup', name:'getGroup', associationKey:'newsletterGroup' },
        { type:'hasMany', model:'Shopware.apps.NewsletterManager.model.Mailing', name:'getLastNewsletter', associationKey:'lastNewsletter' },
        { type:'hasMany', model:'Shopware.apps.Base.model.Customer', name:'getCustomer', associationKey:'customer' }
    ]

});
//{/block}
