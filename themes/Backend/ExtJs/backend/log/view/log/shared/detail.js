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
 * @author VIISON GmbH
 */

//{namespace name=backend/log/shared}

//{block name="backend/log/view/log/shared/detail"}
Ext.define('Shopware.apps.Log.view.log.shared.Detail', {
    extend: 'Enlight.app.Window',
    alias: 'widget.log-shared-detail-window',
    cls: Ext.baseCSSPrefix + 'log-shared-detail',
    border: false,
    autoShow: true,
    layout: 'fit',
    width: '90%',
    height: '90%',
    record: null,

    rawLogDataCopyTextAreaId: null,

    /**
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.rawLogDataCopyTextAreaId = me.id + "-raw-log-data-textarea";

        me.title = me.title + ' - ' + Ext.Date.format(me.record.get('timestamp'), 'Y-m-d H:i:s');

        me.items = {
            xtype: 'tabpanel',
            items: [{
                title: '{s name=detail/tab/info_view}Info{/s}',
                autoScroll: true,
                layout: {
                    type: 'anchor',
                    anchor: '100% 100%',
                    manageOverflow: 2
                },
                items: me.createPanelItems()
            }, {
                title: '{s name=detail/tab/raw_log_data_view}Raw Log Data{/s}',
                autoScroll: false, // The contained text area handles scrolling.
                layout: 'anchor',
                anchor: '100% 100%',
                resizable: false, // Already fits all contents.
                items: me.getBaseTextArea({
                    value: me.record.get('rawLine'),
                    layout: 'anchor',
                    anchor: '100% 100%'
                })
            }]
        };

        var dockButtons = ['->'];

        if (document.queryCommandSupported('copy')) {
            dockButtons = dockButtons.concat([{
                xtype: 'textareafield',
                id: me.rawLogDataCopyTextAreaId,
                value: me.record.get('rawLine'),
                width: 0,
                height: 0,
                listeners: {
                    activate: me.hideCopyButtonTextArea,
                    focus: me.hideCopyButtonTextArea,
                    render: me.hideCopyButtonTextArea
                }
            }, {
                text: '{s name=detail/toolbar/button/copy_log_data}Copy Log Data{/s}',
                cls: 'secondary',
                scope: me,
                handler: me.copyLogData
            }]);
        }

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

    hideCopyButtonTextArea: function() {
        // Called as an event handler. this === the text area
        var outerComponent = document.getElementById(this.getId());
        function hideChildren(el) {
            for (var i = 0; i < el.children.length; i++) {
                if (el.children[i].nodeType === Node.ELEMENT_NODE) {
                    hideEl(el.children[i]);
                }
            }
        }
        function hideEl(el) {
            /* We do not want to show the text area which contains the raw log data to be copied.
             * However, we do need it in the DOM for the browser to permit copying its contents. Also, it is not
             * possible to copy in browsers under certain conditions, e.g. when style="display: none" for the text area.
             *
             * Thus, we do our best to make the text area invisible while still allowing copying.
             */
            el.setAttribute('style', el.getAttribute('style')
                    + 'width: 0; height: 0; font-size: 0; overflow: hidden; '
                    + 'position: absolute; left: -9999px; text-indent: -9999px; margin: 0; border: 0 none;');
            // ^ Do not set padding: 0. Otherwise, Chrome will just copy newlines.
            el.setAttribute('aria-role', 'presentation');
            hideChildren(el)
        }
        hideEl(outerComponent);
    },

    copyLogData: function() {
        var me = this;
        var textarea = document.getElementById(Ext.ComponentQuery.query("#" + me.rawLogDataCopyTextAreaId)[0].getInputId());
        textarea.focus();
        textarea.select();
        var copiedSuccessfully = document.execCommand('copy');

        if (copiedSuccessfully) {
            var msg = '{s name=detail/msg/log_data_copied}Log data copied.{/s}';
        } else {
            var msg = ('{s name=detail/msg/log_data_copy_error}Log data could not be copied. Please, copy the raw '
                + 'log data manually from the raw log data tab.{/s}');
        }
        Shopware.Notification.createGrowlMessage(msg, undefined, false, false);
    },

    /**
     * @return Ext.Component[]
     */
    createPanelItems: function () {
        var me = this;

        var items = [
            Ext.create('Ext.form.FieldSet', {
                title: '{s name=detail/field_set/log}Log{/s}',
                items: me.createMainFieldSetItems()
            })
        ];

        var exception = me.record.get('exception');
        if (exception) {
             items.push(Ext.create('Ext.form.FieldSet', {
                title: '{s name=detail/field_set/exception}Exception{/s}',
                items: me.createExceptionItems(exception),
                collapsible: true
            }));
        }

        return items;
    },

    createExceptionItems: function(exception, isCause, nthCause) {
        var me = this;
        var isCause = (isCause === undefined) ? false : isCause;
        var nthCause = (nthCause === undefined) ? 0 : nthCause;

        var items = [{
            xtype: 'displayfield',
            fieldLabel: (isCause
                ? '{s name=model/field/exception_message_caused_by}Caused by{/s}'
                : '{s name=model/field/exception_message}Exception{/s}'),
            value: exception.message
        }, {
            xtype: 'displayfield',
            fieldLabel: '{s name=model/field/code}Error code{/s}',
            value: exception.code
        }, {
            xtype: 'displayfield',
            fieldLabel: '{s name=model/field/file}File{/s}',
            value: exception.file
        }, {
            xtype: 'displayfield',
            fieldLabel: '{s name=model/field/line}Line{/s}',
            value: exception.line
        }];


        if (exception.trace.length > 0) {
            items.push(me.getBaseTextArea({
                fieldLabel: '{s name=model/field/trace}Trace{/s}',
                value: me.formatStackTrace(exception.trace),
                inputAttrTpl: 'wrap="off"'
            }));
        }

        if (exception.previous) {
            items.push({
                xtype: 'fieldset',
                title: '{s name=detail/field_set/exception_cause}Causing Exception{/s}',
                items: me.createExceptionItems(exception.previous, true, nthCause + 1),
                collapsible: true,
                collapsed: true
            })
        }

        return items;
    },

    getBaseTextArea: function(customProperties) {
        var defaults = {
            xtype: 'textareafield',
            selectOnFocus: true, // To ease copy & pasting.
            readOnly: true,
            resizable: {
                handles: 's' // Only allow resizing downwards ("south")
            },
            fieldStyle: {
                fontFamily: 'monospace'
            },
            anchor: '100%' // full width
        };

        return Ext.apply(defaults, customProperties);
    },

    formatStackTrace: function(trace) {
        var traceText = '';
        for (var frameLevel = 0; frameLevel < trace.length; frameLevel++) {
            var frame = trace[frameLevel];

            // Format method calls.
            var type = Ext.String.htmlDecode(frame.type);
            if (type === '->' || type === '::') {
                // JSON-format method call arguments, but surround them with parentheses, not brackets:
                var argList = JSON.stringify(frame.args).replace(/^\[/, '(').replace(/\]$/, ')');
                traceText += (
                    frame['class'] + type + frame['function'] +
                    argList + " in " + frame.file + ":" + frame.line + "\n"
                );
                continue;
            }

            // For now, all other frame types will be formatted as JSON.
            traceText += JSON.stringify(frame) + "\n";
        }

        return traceText;
    },

    /**
     * @return Ext.Component[]
     */
    createMainFieldSetItems: function() {
        var me = this;

        return [{
            xtype: 'displayfield',
            fieldLabel: '{s name=model/field/timestamp}Date{/s}',
            value: Ext.Date.format(me.record.get('timestamp'), 'Y-m-d H:i:s')
        }, {
            xtype: 'displayfield',
            fieldLabel: '{s name=model/field/level}Level{/s}',
            value: me.record.get('level')
        }, me.getBaseTextArea({
            fieldLabel: '{s name=model/field/message}Message{/s}',
            value: me.record.get('message'),
        }), me.getBaseTextArea({
            fieldLabel: '{s name=model/field/context}Context{/s}',
            value: me.record.get('context'),
        })];
    }
});
//{/block}
