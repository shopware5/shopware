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

Ext.define('Shopware.form.field.MediaGrid', {
    extend: 'Shopware.form.field.GridView',
    cls: 'media-multi-selection',
    alias: 'widget.shopware-form-field-media-grid',
    baseBodyCls: Ext.baseCSSPrefix + 'form-item-body media-multi-selection-body',

    createToolbarItems: function() {
        var me = this;
        var items = me.callParent(arguments);

        if (me.helpText) {
            items.push('->');
            items.push(me.createHelp(me.helpText));
        }
        return items;
    },

    createItemTemplate: function() {
        return '{literal}' +
            '<img src="{thumbnail}" title="{name}" />' +
        '{/literal}';
    },

    createSearchField: function() {
        var me = this;

        me.selectButton = Ext.create('Ext.button.Button', {
            text: '{s name="media/grid/select_media"}{/s}',
            iconCls: 'sprite-inbox-select',
            handler: function() {
                me.openMediaManager()
            }
        });
        return me.selectButton;
    },

    openMediaManager: function() {
        var me = this;

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.MediaManager',
            layout: 'small',
            eventScope: me,
            mediaSelectionCallback: me.onSelectMedia,
            selectionMode: true
        });
    },

    onSelectMedia: function(button, window, selection) {
        var me = this;

        Ext.each(selection, function(record) {
            me.addItem(record);
        });

        window.close();
    },

    insertGlobeIcon: function(icon) {
        var me = this;
        icon.set({
            cls: Ext.baseCSSPrefix + 'translation-globe sprite-globe',
            style: 'position: absolute;width: 16px; height: 16px;display:block;cursor:pointer;top:6px;right:8px;'
        });
        if (me.searchField.el) {
            icon.insertAfter(me.searchField.el);
        }
    },

    createHelp:function (text) {
        var icon = Ext.create('Ext.Component', {
            html: '<span style="margin-top: 4px !important;" class="'+Ext.baseCSSPrefix + 'form-help-icon'+'"></span>',
            width: 24,
            height: 24,
            margin: '0 30 0 0'
        });

        icon.on('afterrender', function() {
            Ext.tip.QuickTipManager.register({
                target: icon.el,
                cls: Ext.baseCSSPrefix + 'form-tooltip',
                title: '',
                text: text,
                width: 225,
                anchorToTarget: true,
                anchor: 'right',
                anchorSize: {
                    width: 24,
                    height: 24
                },
                defaultAlign: 'tr',
                showDelay: 250,
                dismissDelay: 10000
            });
        });

        return icon;
    }
});
