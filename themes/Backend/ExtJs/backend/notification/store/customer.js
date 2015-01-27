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
 * @package    Notification
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - for notification backend module.
 *
 * The article store loads the article notification data
 */
//{block name="backend/notification/store/customer"}
Ext.define('Shopware.apps.Notification.store.Customer', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend:'Ext.data.Store',
    /**
     * Auto load the store after the component
     * is initialized
     * @boolean
     */
    autoLoad:false,

    /**
     * to upload all selected items in one request
     * @boolean
     */
    batch: true,
    /**
     * sets remote sorting true
     * @boolean
     */
    remoteSort: true,

    remoteFilter : true,
    /**
     * Amount of data loaded at once
     * @integer
     */
    pageSize:20,
    /**
     * Define the used model for this store
     * @string
     */
    model:'Shopware.apps.Notification.model.Customer'
});
//{/block}
