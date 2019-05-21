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
 * @category    Shopware
 * @package     Base
 * @subpackage  Attribute
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name="backend/attributes/fields"}

Ext.define('Shopware.form.field.GridView', {
    extend: 'Shopware.form.field.Grid',
    alias: 'widget.shopware-form-field-grid-view',

    createItems: function() {
        var me = this, items = [];
        me.grid = me.createGrid();
        me.toolbar = me.createToolbar();

        items.push(me.toolbar);
        items.push(me.grid);

        if (me.supportText) {
            items.push(me.createSupportText(me.supportText));
        }
        return items;
    },

    createSupportText: function(supportText) {
        return Ext.create('Ext.Component', {
            html: '<div>'+supportText+'</div>',
            cls: Ext.baseCSSPrefix +'form-support-text'
        });
    },

    createToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolbarItems(),
            ui: 'shopware-ui',
            dock: 'top'
        });
    },

    createToolbarItems: function() {
        var me = this;
        me.searchField = me.createSearchField();
        me.deleteButton = me.createDeleteButton();
        return [me.deleteButton, me.searchField]
    },

    createDeleteButton: function() {
        var me = this;
        return Ext.create('Ext.button.Button', {
            text: '{s name="grid/view/delete"}{/s}',
            disabled: true,
            iconCls: 'sprite-minus-circle-frame',
            handler: function () {
                me.onDeleteItems()
            }
        });
    },

    createGrid: function() {
        var me = this;
        return Ext.create('Ext.view.View', {
            store: me.store,
            itemSelector: '.item',
            flex: 1,
            multiSelect: true,
            height: 196,
            cls: 'form-field-grid-view',
            padding: 10,
            autoScroll: true,
            tpl: me.createTemplate(),
            listeners: {
                selectionchange: me.onSelectItem,
                scope: me
            }
        });
    },

    createTemplate: function() {
        var me = this;

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="item">' +
                    me.createItemTemplate() +
                '</div>' +
            '</tpl>{/literal}'
        );
    },

    createItemTemplate: function() {
        return '{literal}<span>{label}</span>{/literal}';
    },

    onDeleteItems: function() {
        var me = this;
        var selModel = me.grid.getSelectionModel();
        Ext.each(selModel.getSelection(), function(record) {
            me.removeItem(record);
        });
    },

    onSelectItem: function(view, records) {
        var me = this;

        if (records.length > 0) {
            me.deleteButton.enable();
        } else {
            me.deleteButton.disable()
        }
    }
});
