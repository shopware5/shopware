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

// {namespace name=backend/swag_update/main}
// {block name="backend/swag_update/view/window"}
Ext.define('Shopware.apps.SwagUpdate.view.Window', {

    extend: 'Enlight.app.Window',

    alias: 'widget.update-main-window',

    cls: Ext.baseCSSPrefix + 'swag-update-window',

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    width: 755,
    height: 715,

    bodyStyle: 'border-bottom-width: 0 !important; border-radius: 0; -webkit-border-radius: 0; -moz-border-radius: 0;',

    title: '{s name="window_title"}Update Check{/s}',

    changelog: null,

    requirementsStore: null,

    pluginsStore: null,

    initComponent: function () {
        var me = this;

        me.items = [
            me.createTabPanel(),
            /* {if {acl_is_allowed privilege=update resource=swagupdate}} */
            me.createBackupContainer()
            /* {/if} */
        ];

        /* {if {acl_is_allowed privilege=update resource=swagupdate}} */
        me.dockedItems = [ me.createToolbar() ];
        /* {/if} */

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
            style: {
                background: '#EBEDEF'
            },
            layout: 'vbox',
            items: [
                me.backupField,
                me.createHintContainer()
            ]
        });
    },

    /**
     * @return { Ext.container.Container }
     */
    createHintContainer: function() {
        var me = this;

        me.hintContainer = Ext.create('Ext.container.Container', {
            margin: '0 10 10 10',
            width: '100%',
            hidden: true
        });

        return me.hintContainer;
    },

    /**
     * @param { number } pluginCount
     */
    showHintContainer: function(pluginCount) {
        var me = this;

        me.hintContainer.tpl = me.createHintContainerTemplate(pluginCount);
        me.hintContainer.show();
    },

    /**
     * @param { number } pluginCount
     * @return { Ext.XTemplate }
     */
    createHintContainerTemplate: function(pluginCount) {
        var message = Ext.String.format('{s name="plugin/update/message"}{/s}', pluginCount);

        return new Ext.XTemplate(
            '<div class="shopware-ui block-message notice">' +
            '   <div class="sprite-exclamation" style="float:left; width: 16px; height: 16px; margin: 1px 3px 2px 2px;"></div>' +
            '   <div style="float:left; width: 85%; margin: 3px 0 2px 10px; font-size: 11px">' + message + '</div>' +
            '</div>'
        );
    },

    /**
     * @return { Ext.tab.Panel }
     */
    createTabPanel: function() {
        var me = this;

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            flex: 1,
            items: [
                me.createChangelogTab(),
                me.createRequirementsTab(),
                me.createPluginsTab()
            ],
            listeners: {
                tabchange: function(panel, tab) {
                    if (tab.isPluginTab) {
                        me.fireEvent('addPluginTooltips', me);
                    }
                }
            }
        });

        return me.tabPanel;
    },

    /**
     * @return { Ext.panel.Panel }
     */
    createChangelogTab: function() {
        var me = this;

        var text = Ext.String.format(
            '<div class="swag-update-changelog">[0]</div>',
            me.changelog.get('changelog')
        );

        return Ext.create('Ext.panel.Panel', {
            style: {
                borderBottom: '1px solid #A4B5C0'
            },
            title: '{s name="tabs/release_notes"}Release Notes{/s}',
            padding: 0,
            cls: 'swag-update-changelog-panel',
            autoScroll: true,
            html: text
        });
    },

    /**
     * @return { Ext.container.Container }
     */
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
                    flex: 2,
                    allowHtml: true
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
        });
    },

    /**
     * @return { Ext.container.Container }
     */
    createPluginsTab: function() {
        var me = this;

        me.pluginsGrid = Ext.create('Ext.grid.Panel', {
            border: false,
            store: me.pluginsStore,
            columns: me.createPluginGridColumns(),
            dockedItems: [{
                xtype: 'pagingtoolbar',
                store: me.pluginsStore,
                dock: 'bottom'
            }]
        });

        return Ext.create('Ext.container.Container', {
            isPluginTab: true,
            itemId: 'update-plugin-tab',
            layout: 'fit',
            title: 'Plugins',
            items: [me.pluginsGrid]
        });
    },

    /**
     * @return { Array }
     */
    createPluginGridColumns: function() {
        var me = this;

        return [{
            header: '{s name="requirements/columns/status"}Compatible{/s}',
            dataIndex: 'errorLevel',
            flex: 0.6,
            renderer: Ext.bind(me.tickRenderer, me)
        }, {
            header: '{s name="columns/is_latest_version"}Latest version{/s}',
            dataIndex: 'updatable',
            flex: 0.6,
            renderer: Ext.bind(me.tickRenderer, me)
        }, {
            header: '{s name="columns/plugin"}Plugin{/s}',
            dataIndex: 'name',
            flex: 1
        }, {
            header: '{s name="columns/message"}Message{/s}',
            dataIndex: 'message',
            flex: 3
        }, {
            xtype: 'actioncolumn',
            width: 26,
            items: [{
                handler: Ext.bind(me.onClickShowPluginUpdateDetails, me),
                getClass: Ext.bind(me.onGetClass, me)
            }]
        }];
    },

    /**
     * @param { Ext.grid.Panel } grid
     * @param { number } index
     * @param { number } colIndex
     * @param { object } eOpts
     * @param { event } event
     * @param { Ext.data.Model } record
     */
    onClickShowPluginUpdateDetails: function(grid, index, colIndex, eOpts, event, record) {
        if (record.get('updatable')) {
            this.fireEvent('showPluginUpdateDetails', grid, index);
        }
    },

    /**
     * @param { string | number } value
     * @param { object } metadata
     * @param { Ext.data.Model } record
     * @return { string | null }
     */
    onGetClass: function(value, metadata, record) {
        if (record.get('updatable')) {
            return 'sprite-arrow-circle-315';
        }

        if (record.get('updatableAfterUpgrade')) {
            return 'sprite-exclamation';
        }

        return 'x-hide-display';
    },

    /**
     * @param { string } value
     * @param { object } meta
     * @param { Ext.data.Model } record
     * @return { string }
     */
    tickRenderer: function(value, meta, record) {
        var divClass,
            divStyle = 'style="width: 16px; height: 16px; margin: 0 auto;"',
            divId = value === true ? 'id="' + record.data.technicalName + '"' : null;

        if (value == 20) {
            divClass = 'class="sprite-cross"';
        } else if (value === true || value == 10) {
            divClass = 'class="sprite-exclamation"';
        } else {
            divClass = 'class="sprite-tick"';
        }

        return '<div ' + [ divId, divClass, divStyle ].join(' ') + '></div>';
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
            cls: 'secondary',
            name: 'save-article-button',
            text: '{s name="cancel"}Cancel{/s}',
            handler: function() {
                me.destroy();
            }
        });

        me.updateButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            name: 'save-article-button',
            text: '{s name="start_update"}Start update{/s}',
            disabled: true,
            handler: function() {
                me.updateButton.disable();
                me.fireEvent('startUpdate', me);
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            border: false,
            dock: 'bottom',
            items: [ '->', me.cancelButton, me.updateButton ]
        });
    }
});
// {/block}
