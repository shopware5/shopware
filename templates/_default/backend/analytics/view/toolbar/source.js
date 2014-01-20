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
 * @package    Analytics
 * @subpackage Source
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * todo@all: Documentation
 */
//{namespace name=backend/analytics/view/main}
//{block name="backend/analytics/view/main/toolbar"}
Ext.define('Shopware.apps.Analytics.view.toolbar.Source', {
    extend: 'Ext.form.field.Picker',
    alias: 'widget.analytics-toolbar-source',
    emptyText: '{s name=toolbar/source/empty}Select shops to compare{/s}',

    width: 200,
    treeWidth: 150,

    initComponent: function () {
        var me = this;
        Ext.apply(me, {
            listeners: {
                scope: me,
                collapse: me.valueSelected
            }
        });
        //me.addEvents('groupSelected');
        me.callParent(arguments);
    },

    createPicker: function () {
        var me = this;
        me.picker = new Ext.tree.Panel({
            height: 300,
            autoScroll: true,
            floating: true,
            resizable: true,
            focusOnToFront: false,
            shadow: true,
            ownerCt: this.ownerCt,
            //useArrows: true,
            store: me.store,
            root: me.root,
            rootVisible: false
            //listeners: {
            //    scope: this,
            //    select: this.valueSelected
            //}
        });
        return me.picker;
    },

    alignPicker: function () {
        var me = this, picker, isAbove, aboveSfx = '-above';
        if (this.isExpanded) {
            picker = me.getPicker();
            if (me.matchFieldWidth) {
                if (me.bodyEl.getWidth() > this.treeWidth) {
                    picker.setWidth(me.bodyEl.getWidth());
                } else picker.setWidth(this.treeWidth);
            }
            if (picker.isFloating()) {
                picker.alignTo(me.inputEl, "", me.pickerOffset);
                isAbove = picker.el.getY() < me.inputEl.getY();
                me.bodyEl[isAbove ? 'addCls' : 'removeCls'](me.openCls
                    + aboveSfx);
                picker.el[isAbove ? 'addCls' : 'removeCls'](picker.baseCls
                    + aboveSfx);
            }
        }
    },

    valueSelected: function (picker, value, options) {
        var me = this,
            dataText = '',
            dataValue = '',
            values = me.picker.getChecked();

        Ext.each(values, function (value, key) {
            if (key !== 0) {
                dataText += ', ';
                dataValue += ',';
            }
            dataText += value.data.text;
            dataValue += value.data.id;
        });

        this.setValue(dataText);
        this.fireEvent('select', this, values);
        //Ext.Function.defer(function () {
        //    this.collapse();
        //}, 1500, this);
    }
});
//{/block}