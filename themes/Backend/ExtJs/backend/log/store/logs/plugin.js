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
 * @package    Log
 * @subpackage Store
 * @version    $Id$
 * @author VIISON GmbH
 */

/**
 * Shopware - Plugin logs store
 */
//{block name="backend/log/store/logs/plugin"}
Ext.define('Shopware.apps.Log.store.logs.Plugin', {
    extend: 'Ext.data.Store',
    model : 'Shopware.apps.Log.model.log.Plugin',
    autoLoad: false,
    pageSize: 20,
    remoteFilter: true,
    remoteSort: true,
    sorters: {
        property: 'timestamp',
        direction: 'DESC'
    },
    proxy: {
        type: 'ajax',
        api: {
            read: '{url controller=log action=getPluginLogs}',
        },
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
//{/block}
