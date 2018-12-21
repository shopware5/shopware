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
 */

/**
 * Overrides the Ext.grid.column.Actio to provide
 * an additional HTML5 data attribute to provide
 * a better adressing in selenium ui tests.
 */
//{block name="extjs/overrides/action"}
Ext.override(Ext.grid.column.Action, {

    /** Suffix for the data attribute */
    dataSuffix: 'action',

    constructor: function(config) {
        var me = this,
            cfg = Ext.apply({}, config),
            items = cfg.items || [me],
            i,
            item;

        // This is a Container. Delete the items config to be reinstated after construction.
        delete cfg.items;
        me.callParent([cfg]);

        // Items is an array property of ActionColumns
        me.items = items;

        // todo@all in Extjs 4.1 this has to be moved into the new defaultRenderer()-function
        me.renderer = function(v, meta) {
//          Allow a configured renderer to create initial value (And set the other values in the "metadata" argument!)
            v = Ext.isFunction(cfg.renderer) ? cfg.renderer.apply(this, arguments)||'' : '';

            meta.tdCls += ' ' + Ext.baseCSSPrefix + 'action-col-cell';
            for (i = 0; i < me.items.length; i++) {
                item = me.items[i];
                item.disable = Ext.Function.bind(me.disableAction, me, [i]);
                item.enable = Ext.Function.bind(me.enableAction, me, [i]);

                var dataString = '';
                if (item.action) {
                    dataString = ' data-' + me.dataSuffix + '="' + item.action + '" ';
                }
                //'" data-' + me.dataSuffix + '="' + item.action +

                v += '<img alt="' + (item.altText || me.altText) + '" src="' + (item.icon || Ext.BLANK_IMAGE_URL) +

                        '" class="' + Ext.baseCSSPrefix + 'action-col-icon ' + Ext.baseCSSPrefix + 'action-col-' + String(i) + ' ' + (item.disabled ? Ext.baseCSSPrefix + 'item-disabled' : ' ') + (item.iconCls || '') +
                        ' ' + (Ext.isFunction(item.getClass) ? item.getClass.apply(item.scope||me.scope||me, arguments) : (me.iconCls || '')) + '"' +
                        ((item.tooltip) ? ' data-qtip="' + item.tooltip + '"' : '') + dataString + ' />';
            }
            return v;
        };
    }
});
//{/block}