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
 * @package    Order
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/order/main}

/**
 * Shopware UI - Order list main window.
 *
 * todo@all: Documentation
 */
//{block name="backend/order/view/batch/window"}
Ext.define('Shopware.apps.Order.view.batch.Window', {
    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls: Ext.baseCSSPrefix + 'order-batch-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-batch-window',
    /**
     * Define window width
     * @integer
     */
    width:970,
    /**
     * Define window height
     * @integer
     */
    height:'90%',
    autoScroll: true,
    /**
     * Display no footer button for the detail window
     * @boolean
     */
    footerButton:false,
    /**
     * As the window has two possible modes (expert and normal), stateful property might result in problems
     * depending on mode the user chooses
     */
    stateful:false,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-order-batch-window',

    /**
     * Set layout for this component to hbox
     */
    layout: {
        align: 'stretch',
        type: 'hbox'
    },

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        title:'{s name=window_title}Batch processing{/s}',
        toolbar: {
            printReceipts: '{s name=print}Print receipts{/s}',
            layout: '{s name=layout}View as{/s}',
            expert: '{s name=expert}Expert layout{/s}',
            easy: '{s name=easy}Easy layout{/s}'
        }
    },

    /**
	 * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
	 *
	 * @return void
	 */
    initComponent: function () {
        var me = this, items = [];
        //single mode will be present if the user change the order status on the detail page
        if (me.mode !== 'single') {
            me.tbar = me.createToolbar();
            items.push(
					Ext.create('Shopware.apps.Order.view.batch.Form', {
						flex: 1,
						records: me.records,
						mode: me.mode,
						orderStatusStore: me.orderStatusStore }
					)
			);
        } else {
            me.width = 400;
            me.height = '90%';

        }
        items.push(me.createMailPanel());

        me.items = items;
        me.title = me.snippets.title;

        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Creates the email panel which is displayed on the right side of the batch window.
     * @return Ext.panel.Panel
     */
    createMailPanel: function() {
        var me = this, panel;

        panel = Ext.create('Shopware.apps.Order.view.batch.Mail', {
            mode: me.mode,
            flex: 1
        });

        if (me.mail instanceof Ext.data.Model) {
            panel.loadRecord(me.mail);
        }
        return panel;
    },


    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the "delete order" action column icon
             * which is placed in the order list in the options column
             *
             * @event
             * @param [window] - This component
             * @param [object] - The selected menu item
             */
            'changeLayout',

            /**
             * Event will be fired when the user clicks the "delete order" action column icon
             * which is placed in the order list in the options column
             *
             * @event
             * @param [window] - This component
             */
            'printReceipts'
        );
    },

    /**
     *
     */
    createToolbar: function() {
        var me = this;

        me.printButton = Ext.create('Ext.button.Button', {
            text: me.snippets.print,
            action: 'print-receipts',
            handler: function() {
                me.fireEvent('printReceipts', me);
            }
        });

        me.modeButton = Ext.create('Ext.button.Cycle', {
            showText: true,
            prependText: me.snippets.toolbar.layout + ' ',
            action: 'layout',
            menu: {
                items: [{
                    text: me.snippets.toolbar.easy,
                    layout: 'easy',
                    iconCls: 'sprite-layout-header'
                },{
                    text: me.snippets.toolbar.expert,
                    layout: 'expert',
                    checked: true,
                    iconCls: 'sprite-layout-header-3-mix'
                }]
            },
            changeHandler:function (cycleBtn, activeItem) {
                me.fireEvent('changeLayout', me, activeItem)
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock:'top',
            flex: 1,
            items:[
                me.printReceipts,
                '->',
                me.modeButton
            ]
        });
    }

});
//{/block}
