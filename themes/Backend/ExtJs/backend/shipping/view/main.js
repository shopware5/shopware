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
 * @package    Shipping
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/*{namespace name=backend/shipping/view/main}*/

/**
 * Shopware UI - Shipping Costs
 */
//{block name="backend/shipping/view/main"}
Ext.define('Shopware.apps.Shipping.view.Main', {
    extend      : 'Enlight.app.Window',

    /**
     * Layout to use
     * @string
     */
    layout      : 'fit',

    /**
     * Alias for the main window
     * @string
     */
    alias       : 'widget.dispatchGrid',

    /**
     * Width to use
     * @integer
     */
    width       : 990,

    /**
     * Hight
     * @integer
     */
    height      : 480,

    /**
     * Allow state store
     * @boolean
     */
    stateful    : true,

    /**
     * Define state store id
     * @string
     */
    stateId     : 'dispatchList',

    /**
     * Name of the window
     * @string
     */
    title       : '{s name=title}Shipping costs management{/s}',
    /**
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = [{
            xtype: 'shipping-list',
            customerGroupStore: me.customerGroupStore,
            shopStore: me.shopStore,
            dispatchStore: me.dispatchStore
        }];
        me.callParent(arguments);
    }
});
//{/block}
