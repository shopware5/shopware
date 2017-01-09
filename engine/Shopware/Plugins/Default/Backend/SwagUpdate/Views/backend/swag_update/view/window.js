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

//{namespace name=backend/swag_update/main}
//{block name="backend/swag_update/view/window"}
Ext.define('Shopware.apps.SwagUpdate.view.Window', {

    extend:'Enlight.app.Window',

    alias:'widget.update-main-window',

    cls: Ext.baseCSSPrefix + 'swag-update-window',

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    width: 755,
    height: 715,

    title: '{s name="window_title"}Update Check{/s}',

    changelog: null,

    requirementsStore: null,

    pluginsStore: null,

    initComponent:function () {
        var me = this;

        me.items = [
            me.createTabPanel(),
            /*{if {acl_is_allowed privilege=update resource=swagupdate}}*/
            me.createBackupContainer()
            /*{/if}*/
        ];

        /*{if {acl_is_allowed privilege=update resource=swagupdate}}*/
        me.dockedItems = [ me.createToolbar() ];
        /*{/if}*/

        me.callParent(arguments);
    },

    createBackupContainer: function() {
        var me = this;

        me.backupField = Ext.create('Ext.form.field.Checkbox', {
            name: 'backup',
            cls: 'confirm-check-box',
            inputValue: true,
            uncheckedValue: false,
            style: {
                marginLeft: '10px',
                marginTop: '2px'
            },
            boxLabel: '{s name="checkbox_backup_label"}I have made a backup and want to process the update.{/s}',
            listeners: {
                change: function(field, newValue) {
                    if (newValue) {
                        me.fireEvent(
                            'validateUpdate',
                            me,
                            me.backupField,
                            me.requirementsStore,
                            me.pluginsStore
                        );
                    } else {
                        me.updateButton.setDisabled(true);
                    }
                }
            }
        });

        return Ext.create('Ext.container.Container', {
            height: 30,
            background: '#fff',
            items: [ me.backupField ]
        })
    },

    createTabPanel: function() {
        var me = this;

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            flex: 1,
            items: [
                me.createChangelogTab(),
                me.createRequirementsTab(),
                me.createPluginsTab()
            ]
        });

        return me.tabPanel;
    },

    createChangelogTab: function() {
        var me = this;

        var text = Ext.String.format(
            '<div class="swag-update-changelog">[0]</div>',
            me.changelog.get('changelog')
        );

        return Ext.create('Ext.panel.Panel', {
            title: '{s name="tabs/release_notes"}Release Notes{/s}',
            padding: 0,
            cls: 'swag-update-changelog-panel',
            autoScroll: true,
            html: text
        });
    },

    createRequirementsTab: function() {
        var me = this;

        me.requirementsGrid = Ext.create('Ext.grid.Panel', {
            border: false,
            store: me.requirementsStore,
            columns: [
            me.createErrorLevelColumn(),
            {
                header: '{s name="columns/message"}Message{/s}',
                dataIndex: 'message',
                flex: 2
            }],
            dockedItems: [{
                xtype: 'pagingtoolbar',
                store: me.requirementsStore,
                dock: 'bottom'
            }]
        });

        return Ext.create('Ext.container.Container', {
            layout: 'fit',
            title: '{s name="tabs/requirements"}Requirements{/s}',
            items: [me.requirementsGrid]
        })
    },

    createPluginsTab: function() {
        var me = this;

        me.pluginsGrid = Ext.create('Ext.grid.Panel', {
            border: false,
            store: me.pluginsStore,
            columns: [ me.createErrorLevelColumn(), {
                header: '{s name="columns/plugin"}Plugin{/s}',
                dataIndex: 'name',
                flex: 1
            }, {
                header: '{s name="columns/message"}Message{/s}',
                dataIndex: 'message',
                flex: 3
            }],
            dockedItems: [{
                xtype: 'pagingtoolbar',
                store: me.pluginsStore,
                dock: 'bottom'
            }]
        });

        return Ext.create('Ext.container.Container', {
            layout: 'fit',
            title: 'Plugins',
            items: [me.pluginsGrid]
        })
    },

    createErrorLevelColumn: function() {
        return {
            xtype: 'actioncolumn',
            width: 60,
            header: '{s name="requirements/columns/status"}Status{/s}',
            items: [{
                getClass: function(value, metadata, record) {
                    if (record.get('errorLevel') == 20) {
                        return 'sprite-cross';
                    } else if (record.get('errorLevel') == 10) {
                        return 'sprite-exclamation';
                    } else {
                        return 'sprite-tick';
                    }
                }
            }]
        };
    },

    createToolbar: function() {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls:'secondary',
            name: 'save-article-button',
            text: '{s name="cancel"}Cancel{/s}',
            handler: function() {
                me.destroy();
            }
        });

        me.updateButton = Ext.create('Ext.button.Button', {
            cls:'primary',
            name: 'save-article-button',
            text: '{s name="start_update"}Start update{/s}',
            disabled: true,
            handler: function() {
                me.updateButton.disable();
                me.fireEvent('startUpdate', me);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: [ '->', me.cancelButton, me.updateButton ]
        });
    }
});
//{/block}
