/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    NewsletterManager
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - newsletter group store
 * Stores the custom newsletter groups
 */
// {block name="backend/newsletter_manager/store/newsletter_group"}
Ext.define('Shopware.apps.NewsletterManager.store.NewsletterGroup', {
    extend: 'Ext.data.Store',
    // Do not load data, when not explicitly requested
    autoLoad: false,
    model: 'Shopware.apps.NewsletterManager.model.NewsletterGroup',
    remoteFilter: true,
    remoteSort: true,

    /**
     * Amount of data loaded at once
     * @integer
     */
    pageSize: 1000,

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
            read: '{url controller=NewsletterManager action="getNewsletterGroups"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
// {/block}
