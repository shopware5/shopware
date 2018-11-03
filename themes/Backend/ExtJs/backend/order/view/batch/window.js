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
 * @package    Order
 * @subpackage View
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
     *
     * @type { String }
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     *
     * @type { String }
     */
    cls: Ext.baseCSSPrefix + 'order-batch-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     *
     * @type { String }
     */
    alias:'widget.order-batch-window',
    /**
     * Define window width
     *
     * @type { Number }
     */
    width: 600,
    /**
     * Define window height
     *
     * @type { String }
     */
    height:'90%',

    /**
     * @type { Boolean }
     */
    autoScroll: true,
    /**
     * Display no footer button for the detail window
     *
     * @type { Boolean }
     */
    footerButton:false,
    /**
     * As the window has two possible modes (expert and normal), stateful property might result in problems
     * depending on mode the user chooses
     *
     * @type { Boolean }
     */
    stateful:false,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-order-batch-window',

    /**
     * @type { String }
     */
    title:'{s name=window_title}Batch processing{/s}',

    /**
     * Set layout for this component to hbox
     *
     * @type { Object }
     */
    layout: {
        align: 'stretch',
        type: 'hbox'
    },

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     */
    initComponent: function () {
        var me = this;

        me.items = Ext.create('Shopware.apps.Order.view.batch.Form', {
            flex: 1,
            records: me.records,
            orderStatusStore: me.orderStatusStore,
            paymentStatusStore: me.paymentStatusStore
        });

        me.callParent(arguments);
    }

});
//{/block}
