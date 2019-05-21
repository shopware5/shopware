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
 * @package    Systeminfo
 * @subpackage View
 * @version    $Id$
 * @author     shopware AG
 */

//{namespace name=backend/log/system}

//{block name="backend/log/view/system/list"}
Ext.define('Shopware.apps.Log.view.system.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.log-system-list',
    ui: 'shopware-ui',
    autoScroll: true,
    store: 'SystemLogs',
    region: 'center',
    border: false,

    /**
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.columns = me.getColumns();
        me.dockedItems = [{
            dock: 'bottom',
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        }, me.createToolbar()];

        me.callParent(arguments);
    },

    /**
     *  @return Ext.grid.Column[]
     */
    getColumns: function () {
        var me = this;

        var columns = [{
            xtype: 'datecolumn',
            header: '{s name=model/field/date}Date{/s}',
            dataIndex: 'date',
            width: 150,
            format: Ext.Date.defaultFormat + ' H:i:s'
        }, {
            header: '{s name=model/field/channel}Channel{/s}',
            dataIndex: 'channel',
            width: 100,
            sortable: false
        }, {
            header: '{s name=model/field/level}Level{/s}',
            dataIndex: 'level',
            width: 100,
            sortable: false
        }, {
            header: '{s name=model/field/message}Message{/s}',
            dataIndex: 'message',
            flex: 1,
            sortable: false,
            allowHtml: true,
            renderer: function (value) {
                return Ext.String.htmlEncode(value);
            }
        }, {
            header: '{s name=model/field/code}Code{/s}',
            dataIndex: 'code',
            width: 75,
            sortable: false
        }, {
            xtype: 'actioncolumn',
            width: 30,
            items: [{
                iconCls: 'sprite-magnifier',
                action: 'openLog',
                tooltip: '{s name=grid/action/tooltip/open_log}Open log{/s}',
                handler: function (view, rowIndex, colIndex, item, event, record) {
                    me.fireEvent('openLog', record);
                }
            }]
        }];

        return columns;
    },

    createToolbar: function () {
        var me = this;

        me.downloadButton = Ext.create('Ext.Button', {
            iconCls: 'sprite-drive-download',
            text: '{s name=toolbar/download}Download{/s}',
            action: 'download',
            disabled: true,
            handler: function () {
                var file = me.logFileCombo.getValue(),
                    link = "{url action=downloadLogFile}"
                        + "?logFile=" + encodeURIComponent(file);
                window.open(link, '_blank');
            }
        });

        me.logFileCombo = me.createLogFileCombo();

        return {
            xtype: 'toolbar',
            ui: 'shopware-ui',
            dock: 'top',
            border: false,
            items: [me.logFileCombo, me.downloadButton]
        };
    },

    createLogFileCombo: function () {
        var me = this;

        var combo = Ext.create('Shopware.form.field.PagingComboBox', {
            name: 'categoryId',
            pageSize: 15,
            labelWidth: 155,
            forceSelection: true,
            width: 400,
            fieldLabel: '{s name=toolbar/file}File{/s}',
            store: me.logFilesStore,
            valueField: 'name',
            displayField: 'name',
            disableLoadingSelectedName: true
        });

        combo.store.on('load', function (store) {
            var record = store.findRecord('default', true);
            if (record) {
                combo.setValue(record.get('name'));
            }
        }, this, {
            single: true
        });

        combo.store.load();

        combo.on('change', function () {
            var value = combo.getValue();
            if (value) {
                me.downloadButton.enable();
                me.store.getProxy().extraParams = {
                    logFile: combo.getValue()
                };
                me.store.loadPage(1);
            } else {
                me.downloadButton.disable();
            }
        }, this);

        return combo;
    }
});
//{/block}
