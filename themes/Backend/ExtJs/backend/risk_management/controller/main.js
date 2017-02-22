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
 * @package    RiskManagement
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - RiskManagement backend module
 *
 * Main controller of the risk-management module.
 * It only creates the main-window.
 */

Ext.define('Shopware.apps.RiskManagement.controller.Main', {
    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.app.Controller',

    requires: [ 'Shopware.apps.RiskManagement.controller.RiskManagement' ],

    /**
     * Init-function to create the main-window and assign the riskStore
     */
    init: function() {
        var me = this;

        me.subApplication.paymentStore = me.subApplication.getStore('Payments');
        me.subApplication.areasStore = me.subApplication.getStore('Areas').load();
        me.subApplication.subShopStore = me.subApplication.getStore('Subshops').load();

        me.mainWindow = me.getView('main.Window').create({
            paymentStore: me.subApplication.paymentStore,
            areasStore: me.subApplication.areasStore,
            subShopStore: me.subApplication.subShopStore
        });
        me.callParent(arguments);
    }
});
