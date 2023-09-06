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
 * @package    Partner
 * @subpackage Store
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Store - for partner backend module.
 *
 * The list store loads and stores the list model
 */
//{block name="backend/partner/store/list"}
Ext.define('Shopware.apps.Partner.store.List', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend: 'Ext.data.Store',
    /**
     * Auto load the store after the component
     * is initialized
     * @boolean
     */
    autoLoad: false,

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

    remoteFilter: true,
    /**
     * Amount of data loaded at once
     * @integer
     */
    pageSize: 30,
    /**
     * Define the used model for this store
     * @string
     */
    model: 'Shopware.apps.Partner.model.Main'
});
//{/block}
