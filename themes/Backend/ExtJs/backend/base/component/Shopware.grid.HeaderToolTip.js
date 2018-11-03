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
 * @package    Base
 * @subpackage Component
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - HeaderToolTip Plugin for Ext.grid.Panel's
 *
 * This plugin provides an easy-to-use way to add tooltips
 * to grid cell headers.
 *
 * @example
 * Ext.create('Shopware.grid.HeaderToolTip', {
 *     showIcons: false
 * });
 *
 * @example
 * var columns = [{
 *     header: 'Error Message',
 *     tooltip: 'The Tooltip goes here,
 *     dataIndex: 'errorMsg',
 * };
 */
Ext.define('Shopware.grid.HeaderToolTip', {
    /** @lends Ext.AbstractPlugin# */

    /**
     * Extends the abstact plugin component
     * @string
     */
    extend: 'Ext.AbstractPlugin',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets
     * @string
     */
    alias: 'plugin.headertooltip',

    /**
     * List of classes to load together with this class. These aren't neccessarily loaded before this class is instantiated.
     * @array
     */
    uses: [ 'Ext.tip.ToolTip' ],

    /**
     * Property which holds the client (e.g. the Ext.grid.Panel)
     * @default null
     * @object
     */
    client: null,

    /**
     * Whether or not to display icons in the cell headers
     *
     * @default true
     * @boolean
     */
    showIcons: true,

    /**
     * The init method is invoked after initComponent method has been run for the client Component.
     *
     * @public
     * @param [object] client - Ext.Component which calls the plugin
     * @return void
     */
    init: function(client) {
        var me = this;

        me.callParent(arguments);

        // Assign the client to the plugin scope
        me.client = client;
        me.client.headerCt.on("afterrender", me.onAfterRender, me);
    },

    /**
     * Event listener method which will be fired when the client fires
     * the "afterrender" event.
     *
     * @event afterrender
     * @return void
     */
    onAfterRender: function() {
        var me = this,
            headerCt = me.client.headerCt,
            grid = me.client;

        if (me.showIcons) {
            me.wrapColumnsWithSpan(headerCt.getGridColumns());
        }

        grid.tip = me.getToolTip();
        grid.tip.on("beforeshow", me.onBeforeShow, me);
    },

    /**
     * Wraps Column header with span and adds x-tooltip class
     *
     * @params [array] columns
     * @return void
     */
    wrapColumnsWithSpan: function(columns) {
        Ext.each(columns, function(column) {
            if (column.tooltip) {
                var el = column.el;
                var tooltipclass = Ext.baseCSSPrefix + "tooltip"
                el.update('<span class="' + tooltipclass + '">' + el.dom.innerHTML + '</span>');
            }
        });
    },

    /**
     * Creates new Ext.tip.ToolTip
     *
     * @return [Ext.tip.ToolTip]
     */
    getToolTip: function() {
        var me = this;
            headerCt = me.client.headerCt;

        return Ext.create('Ext.tip.ToolTip', {
            target: headerCt.el,
            delegate: "." + Ext.baseCSSPrefix + "column-header",
            trackMouse: true,
            renderTo: Ext.getBody()
        });
    },

    /**
     * Event listener method which will be fired when the tooltip fires
     * the "beforeshow" event.
     *
     * @event beforeshow
     * @param [Ext.tip.ToolTip]
     * @return void
     */
    onBeforeShow: function(tip) {
        var me = this;
        headerCt = me.client.headerCt;

        var column = headerCt.down('gridcolumn[id=' + tip.triggerElement.id  +']');

        if (column && column.tooltip) {
            tip.update(Ext.util.Format.htmlEncode(column.tooltip));
        } else {
            tip.clearTimers();
            return false;
        }
    },

    /**
     * The plugin cleanup method which the owning Component calls at Component destruction time.
     * Removes the class member before the Component will be destroyed.
     *
     * @public
     * @return void
     */
    destroy: function() {
        this.client.headerCt.un("afterrender");

        // todo@stp throws "this.destroyMembers is not a function"
        // this.destroyMembers('client');

        this.callParent(arguments);
    }
});
