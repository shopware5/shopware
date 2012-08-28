/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

/**
 * Overrides the Ext.button.Button to provide
 * an additional HTML5 data attribute to provide
 * a better adressing in selenium ui tests.
 *
 * @category   Enlight
 * @package    Enlight_ExtJs
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
Ext.override(Ext.button.Button, {

    /** Suffix for the data attribute */
    dataSuffix: 'action',

    afterRender: function() {
        var me = this;
        me.callOverridden(arguments);

        if(me.action) {
            var dom = me.getEl().dom.children[0].children[0];
            dom.setAttribute('data-' + me.dataSuffix, me.action);
        }
    },

    initComponent: function() {
        var me = this;
        me.callParent(arguments);

        me.addEvents(

            'click',

            'toggle',

            'mouseover',

            'mouseout',

            'menushow',

            'menuhide',

            'menutriggerover',

            'menutriggerout'
        );

        if (me.menu) {
            // Flag that we'll have a splitCls
            me.split = true


            // retrieve menu by id or instantiate instance if needed
            me.menu = Ext.menu.Manager.get(me.menu);

            // Add an additional class to the menu for styling purpose
            if(me.menuCls && me.menuCls.length) {
                me.menu.setUI('default shopware-ui');
                me.menu.addCls(me.menuCls);
            }

            me.menu.ownerCt = me;
        }

        // Accept url as a synonym for href
        if (me.url) {
            me.href = me.url;
        }

        // preventDefault defaults to false for links
        if (me.href && !me.hasOwnProperty('preventDefault')) {
            me.preventDefault = false;
        }

        if (Ext.isString(me.toggleGroup)) {
            me.enableToggle = true;
        }
    },

    /**
     * Shows this button's menu (if it has one)
     */
    showMenu: function() {
        var me = this;
        if (me.rendered && me.menu) {
            if (me.tooltip && me.getTipAttr() != 'title') {
                Ext.tip.QuickTipManager.getQuickTip().cancelShow(me.btnEl);
            }
            if (me.menu.isVisible()) {
                me.menu.hide();
            }

            if(me.menu && me.menuOffset) {
                me.menu.showBy(me.el, me.menuAlign, me.menuOffset);
            } else {
                me.menu.showBy(me.el, me.menuAlign);
            }
        }
        return me;
    }
});
