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
 * @package    Log
 * @subpackage View
 */

//{namespace name=backend/log/system}

//{block name="backend/log/view/system/detail"}
Ext.define('Shopware.apps.Log.view.system.Detail', {
    extend: 'Enlight.app.Window',
    alias: 'widget.systeminfo-log-detail',
    border: false,
    autoShow: true,
    layout: 'fit',
    record: null,
    title: '{s name=detail/title}Log{/s}',
    width: '90%',
    height: 700,

    /**
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.title + ' - ' + Ext.Date.format(me.record.get('date'), 'Y-m-d H:i:s');

        me.items = me.createPanelItems();

        var dockButtons = ['->'];

        dockButtons.push({
            text: '{s name=detail/toolbar/button/close}Close{/s}',
            cls: 'secondary',
            scope: me,
            handler: me.destroy
        });

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items: dockButtons
        }];

        me.callParent(arguments);
    },

    /**
     * @return Ext.Component[]
     */
    createPanelItems: function () {
        var me = this;

        var items = [
            Ext.create('Ext.form.Panel', {
                bodyPadding: '10 10 50 10',
                autoScroll: true,
                items: me.createMainFieldSetItems()
            })
        ];

        return items;
    },

    getBaseTextArea: function(customProperties) {
        var defaults = {
            xtype: 'textareafield',
            selectOnFocus: true,
            readOnly: true,
            fieldStyle: 'font: 13px monospace !important; white-space: pre; overflow: scroll; overflow-y: scroll; overflow-x: scroll; overflow:-moz-scrollbars-vertical;',
            anchor: '100%',
            height: 230
        };
        return Ext.apply(defaults, customProperties);
    },

    formatMessage: function(message) {
        message = message.replace(new RegExp(' #', 'g'), "\n#");
        message = message.replace(new RegExp('[^ ]+/engine/', 'g'), "/engine/");
        message = message.replace(new RegExp('[^ ]+/vendor/', 'g'), "/vendor/");
        message = message.replace(new RegExp('[^ ]+/custom/', 'g'), "/custom/");
        return message;
    },

    /**
     * @return Ext.Component[]
     */
    createMainFieldSetItems: function() {
        var me = this;

        return [{
            xtype: 'displayfield',
            fieldLabel: '{s name=model/field/date}Date{/s}',
            value: Ext.Date.format(me.record.get('date'), Ext.Date.defaultFormat + ' H:i:s')
        }, {
            xtype: 'displayfield',
            fieldLabel: '{s name=model/field/channel}Channel{/s}',
            value: me.record.get('channel')
        }, {
            xtype: 'displayfield',
            fieldLabel: '{s name=model/field/level}Level{/s}',
            value: me.record.get('level')
        }, me.getBaseTextArea({
            fieldLabel: '{s name=model/field/message}Message{/s}',
            value: me.formatMessage(me.record.get('message'))
        }), me.getBaseTextArea({
            fieldLabel: '{s name=model/field/context}Context{/s}',
            value: me.formatMessage(me.record.get('context'))
        })];
    }
});
//{/block}
