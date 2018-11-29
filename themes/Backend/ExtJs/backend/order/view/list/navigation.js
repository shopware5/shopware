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
 * Shopware UI - Order list navigation
 *
 * Displayed on the left side of the order list module.
 */
//{block name="backend/order/view/list/navigation"}
Ext.define('Shopware.apps.Order.view.list.Navigation', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.order-list-navigation',

    /**
     * Set border layout for the panel content
     * @string
     */
    layout:'anchor',
    /**
     * Set css class for this component
     * @string
     */
    cls:Ext.baseCSSPrefix + 'order-list-navigation',

    /**
     * Sets the width of the panel
     * @integer
     */
    width: 390,

    /**
     * Initialed the info panel is collapsed
     * @boolean
     */
    collapsed:false,

    /**
     * Define that the info panel can be collapsed
     * @boolean
     */
    collapsible:true,

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        title:'{s name=navigation_title}Navigation options{/s}'
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
    initComponent:function () {
        var me = this;

        me.items = me.getPanels();
        me.title = me.snippets.title;
        me.callParent(arguments);
    },

    getPanels: function() {
        var me = this;

        return [
            Ext.create('Shopware.apps.Order.view.list.Filter', {
                documentStore: me.documentStore,
                orderStatusStore: me.orderStatusStore,
                paymentStatusStore: me.paymentStatusStore
            })
        ];
    }
});
//{/block}
