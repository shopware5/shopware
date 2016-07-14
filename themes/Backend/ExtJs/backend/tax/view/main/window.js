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
 * @package    Customer
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/tax/view/main}

/**
 * Shopware UI - Customer list main window.
 *
 * todo@all: Documentation
 */
//{block name="backend/countries/view/main/window"}
Ext.define('Shopware.apps.Tax.view.main.Window', {
    /**
     * Define that the customer main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'tax-list-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.tax-list-main-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'border',
    /**
     * Define window width
     * @integer
     */
    width:1024,
    /**
     * Define window height
     * @integer
     */
    height:500,
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,
    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-tax-main-window',
    /**
     * Set window title which is displayed in the window header
     * @string
     */
    title:'{s name=window_title}Tax{/s}',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent:function () {
        var me = this;

        var notice = Shopware.Notification.createBlockMessage('The new tax rules configuration has not yet been considered in the frontend.', 'error');

        me.items = [{
            xtype: 'container',
            region: 'north',
            padding: '5',
            items: [ notice ]
        },{
            xtype: 'tax-tree',
            region: 'west',
            store: me.treeStore
        }, {
            xtype: 'tax-rules',
            ruleStore: me.ruleStore,
            subApplication: me.subApplication,
            region: 'center'

        }];

        me.callParent(arguments);
    }
});
//{/block}
