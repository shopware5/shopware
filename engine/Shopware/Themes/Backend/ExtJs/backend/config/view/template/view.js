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
 */

/**
 * todo@all: Documentation
 */

//{namespace name=backend/config/view/form}

//{block name="backend/config/view/template/view"}
Ext.define('Shopware.apps.Config.view.template.View', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.config-template-view',

    region: 'center',
    store: 'form.Template',

    autoScroll: true,
    border: false,

    initComponent: function() {
        var me = this;

        me.store = Ext.data.StoreManager.lookup(me.store);

        Ext.applyIf(me, {
            items: [
                me.getDataView()
            ],
            dockedItems: [
                me.getPagingToolbar(),
                me.getToolbar()
            ]
        });

        me.callParent(arguments);

        me.store.load();
    },

    getDataView: function() {
        var me = this;
        return {
            xtype: 'dataview',
            tpl: me.getViewTemplate(),

            itemSelector: '.thumb-wrap',
            style: 'background: #fff',
            store: me.store
        };
    },

    getViewTemplate: function() {
        var me = this;
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',

            '<tpl if="enabled">',
                '<div class="thumb-wrap enabled" id="{template}">',
            '<tpl elseif="preview">',
                '<div class="thumb-wrap previewed" id="{template}">',
            '<tpl else>',
                '<div class="thumb-wrap" id="{template}">',
            '</tpl>',

            '<tpl if="enabled">',
                '<div class="hint enabled"><span>{/literal}{s name=template/hint_enabled}Enabled{/s}{literal}</span></div>',
            '<tpl elseif="preview">',
                '<div class="hint preview"><span>{/literal}{s name=template/hint_preview}Preview{/s}{literal}</span></div>',
            '</tpl>',

            '<div class="thumb">',
                '<div class="inner-thumb">',
                '<tpl if="previewThumb">',
                    '<img src="{previewThumb}" title="{name}" />',
                '</tpl>',
                '</div>',
            '</div>',

            '<span class="x-editable">{name}</span>',
            '   </div>',
            '</tpl>',
            '<div class="x-clear"></div>{/literal}'
        );
    },

    getPagingToolbar: function() {
        var me = this;
        return {
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store,
            dock: 'bottom'
        };
    },

    getToolbar: function() {
        var me = this;
        return {
            xtype: 'toolbar',
            ui: 'shopware-ui',
            dock: 'top',
            border: false,
            items: me.getTopBar()
        };
    },

    getTopBar:function () {
        var me = this;
        return [{
            iconCls:'sprite-application-search-result',
            text:'{s name=template/start_preview_text}Start preview{/s}',
            disabled:true,
            action:'preview'
        }, {
            iconCls:'sprite-application-search-result',
            text:'{s name=template/stop_preview_text}Stop preview{/s}',
            hidden:true,
            action:'stop-preview'
        }, {
            iconCls:'sprite-application--arrow',
            text:'{s name=template/select_template_text}Select template{/s}',
            disabled:true,
            action:'enable'
        }, {
            xtype: 'config-element-select',
            editable: false,
            name: 'shop',
            emptyText: '{s name=template/shop_empty_text}Please select a shop...{/s}',
            store: 'base.Shop'
        }, '->', {
            xtype:'config-base-search'
        }, {
            xtype:'tbspacer', width:6
        }];
    }
});
//{/block}
