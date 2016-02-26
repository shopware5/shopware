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
 * @author shopware AG
 */

//{namespace name=backend/log/main}

//{block name="backend/log/view/log/detail"}
Ext.define('Shopware.apps.Log.view.log.Detail', {
    extend: 'Enlight.app.Window',
    title: '{s name=window_title}Log{/s}',
    cls: Ext.baseCSSPrefix + 'log-detail',
    alias: 'widget.log-detail-window',
    border: false,
    autoShow: true,
    layout: 'fit',
    height: 300,
    minHeight: 300,
    width: 350,
    minWidth: 350,
    log: null,

    snippets: {
        'close': '{s name="detail/close_window"}Close window{/s}',
        'user': '{s name="detail/user"}User{/s}',
        'date': '{s name="detail/date"}Date{/s}',
        'ipAddress': '{s name="detail/ip_address"}IP-Address{/s}',
        'module': '{s name="detail/module"}Module{/s}',
        'message': '{s name="detail/message"}Message{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this,
            log = me.log;

        me.items = [{
            xtype: 'panel',
            layout: 'anchor',
            border: false,
            bodyPadding: 10,
            defaults: {
                anchor: '100%'
            },
            items: me.getPanelItems(log)
        }];

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items: [
                '->',
                {
                    text : me.snippets.close,
                    scope : this,
                    cls: 'secondary',
                    handler : me.destroy
                }
            ]
        }];

        me.callParent(arguments);
    },

    /**
     * Returns the items of the log detail window.
     *
     * @param { Object } log
     * @returns { Array }
     */
    getPanelItems: function (log) {
        var me = this,
            snippets = me.snippets;

        return [{
            xtype: 'displayfield',
            fieldLabel: snippets.user,
            value: log.user
        }, {
            xtype: 'displayfield',
            fieldLabel: snippets.date,
            value: Ext.util.Format.date(log.date) + ' ' + Ext.util.Format.date(log.date, window.timeFormat)
        }, {
            xtype: 'displayfield',
            fieldLabel: snippets.ipAddress,
            value: log.ip_address
        }, {
            xtype: 'displayfield',
            fieldLabel: snippets.module,
            value: log.key
        }, {
            xtype: 'textareafield',
            fieldLabel: snippets.message,
            selectOnFocus: true,
            value: log.text,
            anchor: '100% -100',
            readOnly: true
        }]
    }
});
//{/block}
