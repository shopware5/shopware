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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/risk_management/main}

/**
 * Shopware UI - riskManagement Window View
 *
 * This view creates the main-window and the main components.
 * It also adds the paymentStore to the panel.
 */
//{block name="backend/risk_management/view/main/window"}
Ext.define('Shopware.apps.RiskManagement.view.main.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window_title}Risk management{/s}',
    cls: Ext.baseCSSPrefix + 'risk_management-window',
    alias: 'widget.risk_management-main-window',
    border: 0,
    bodyBorder: false,
    autoShow: true,
    layout: 'border',
    height: '90%',
    width: 925,

    stateful:true,
    stateId:'shopware-risk_management-window',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [{
            xtype: 'risk_management-main-panel',
            paymentStore: me.paymentStore
        }];

        me.callParent(arguments);
    }
});
//{/block}
