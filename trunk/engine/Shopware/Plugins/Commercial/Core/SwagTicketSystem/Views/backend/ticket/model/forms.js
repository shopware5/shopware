/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    TicketSystem
 * @subpackage Main
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */
//{block name="backend/ticket/model/forms"}
Ext.define('Shopware.apps.Ticket.model.Forms', {
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
		//{block name="backend/ticket/model/list/fields"}{/block}
        { name:'id', type: 'int' },
        { name: 'name', type: 'string' },
        { name: 'email', type: 'string' },
        { name: 'email_subject', type: 'string' },
        { name: 'email_template', type: 'string' },
        { name: 'isocode', type: 'string' },
        { name: 'text', type: 'string' },
        { name: 'text2', type: 'string' },
        { name: 'ticketTypeid', type: 'int' }
    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy:{
        /**
         * Set proxy type to ajax
         * @string
         */
        type:'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            create: '{url action="createForm"}',
            read: '{url action="getForms"}',
            update: '{url action="updateForm"}',
            destroy: '{url action="destroyForm"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    },
    associations: [
       { type: 'hasMany', model: 'Shopware.apps.Ticket.model.FormField', name: 'getFormFields', associationKey: 'fields' },
       { type: 'hasMany', model: 'Shopware.apps.Ticket.model.Mapping', name: 'getFormMapping', associationKey: 'mapping' }
   ]
});
//{/block}
