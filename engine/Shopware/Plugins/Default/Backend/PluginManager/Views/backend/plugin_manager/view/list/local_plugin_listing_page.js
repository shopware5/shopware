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
 * @package    PluginManager
 * @subpackage List
 * @version    $Id$
 * @author shopware AG
 */
// {namespace name=backend/plugin_manager/translation}

// {block name="backend/plugin_manager/view/list/local_plugin_listing_page"}
Ext.define('Shopware.apps.PluginManager.view.list.LocalPluginListingPage', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.plugin-manager-local-plugin-listing',
    cls: 'plugin-manager-local-plugin-listing',

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },
    viewConfig: {
        markDirty: false
    },

    /**
     * @var boolean
     */
    hasTriedLogin: false,

    configure: function() {
        return {
            addButton: false,
            pageSizeCombo: false,
            deleteButton: false,
            deleteColumn: false,
            editColumn: false,
            columns: {
                label: {
                    flex: 2,
                    header: '{s name="plugin_name"}Plugin name{/s}',
                    groupable: false,
                    renderer: this.nameRenderer,
                    editor: null
                },
                version: {
                    width: 60,
                    header: '{s name="version"}Version{/s}',
                    groupable: false,
                    editor: null
                },
                installationDate: {
                    header: '{s name="installed_on"}Installed on{/s}',
                    groupable: false,
                    renderer: this.dateRenderer,
                    editor: null
                },
                updateDate: {
                    header: '{s name="updated_on"}Updated on{/s}',
                    groupable: false,
                    renderer: this.dateRenderer,
                    editor: null
                },
                licenceCheck: {
                    flex: 2,
                    sortable: false,
                    groupable: false,
                    cls: 'licence-column',
                    header: '{s name="licence"}License{/s}',
                    renderer: this.licenceRenderer,
                    editor: null
                },
                active: this.createActiveColumn,
                author: {
                    header: '{s name="from_producer"}Developed by{/s}',
                    groupable: false,
                    renderer: this.authorRenderer,
                    editor: null
                }
            }
        };
    },

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        Shopware.app.Application.on('plugin-reloaded', function(plugin) {
            me.store.load();
            me.hideLoadingMask();
        });
    },

    createActionColumn: function () {
        var me = this;

        var actionColumn = me.callParent(arguments);

        actionColumn.width = 120;
        return actionColumn;
    },

    createSelectionModel: function() { },

    createActiveColumn: function() {
        var me = this,
            items = [];

        items.push({
            tooltip: '{s name="activate_deactivate"}Activate / Deactivate{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                if (record.allowActivate()) {
                    me.activatePluginEvent(record);
                } else if (record.allowDeactivate()) {
                    me.deactivatePluginEvent(record);
                }
            },
            getClass: function(value, metaData, record) {
                if (!record.allowActivate() && !record.allowDeactivate()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }

                if (record.allowActivate()) {
                    return 'sprite-ui-check-box-uncheck';
                } else {
                    return 'sprite-ui-check-box';
                }
            }
        });

        return {
            xtype: 'actioncolumn',
            width: 60,
            align: 'center',
            header: '{s name="active"}Active{/s}',
            groupable: false,
            items: items
        };
    },

    searchEvent: function(field, value) {
        var me = this;

        me.store.clearFilter();

        value = value.toLowerCase();

        me.store.filterBy(function(record, id) {
            var description = record.get('description') + '';
            var name = record.get('label') + '';
            var technicalName = record.get('technicalName') + '';
            var producer = '';

            if (record['getProducerStore']) {
                producer = record['getProducerStore'].first().get('name') + '';
            }

            name = name.toLowerCase();
            technicalName = technicalName.toLowerCase();
            producer = producer.toLowerCase();
            description = description.toLowerCase();

            return (
                name.indexOf(value) > -1 ||
                description.indexOf(value) > -1 ||
                producer.indexOf(value) > -1 ||
                technicalName.indexOf(value) > -1
            );
        });
    },

    createFeatures: function() {
        var me = this,
            items = me.callParent(arguments);

        me.groupingFeature = Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: new Ext.XTemplate(
                '{literal}{name:this.formatName} ({rows.length} Plugins){/literal}',
                {
                    formatName: function(name) {
                        switch (name) {
                            case 2:
                                return '{s name="group_headline_installed"}Installed{/s}';
                            case 1:
                                return '{s name="group_headline_deactivated"}Inactive{/s}';
                            case 0:
                                return '{s name="group_headline_uninstalled"}Uninstalled{/s}';
                        }
                    }
                }
            )
        });

        items.push(me.groupingFeature);
        return items;
    },

    createPagingbar: function () {
        var me = this,
            pagingBar = me.callParent(arguments);

        /* {if {acl_is_allowed privilege=install}} */
        pagingBar.insert(12, me.createSafeModeCheckbox());
        /* {/if} */

        pagingBar.insert(13, {
            xtype: 'tbseparator',
            cls: 'separator-first'
        });

        return pagingBar;
    },

    nameRenderer: function(value, metaData, record) {
        var name = record.get('label');

        if (!record.get('localIcon') || record.get('localIcon') == 'false') {
            return name;
        }

        return '<div style="display: inline-block; position:relative; top: 2px; margin-right: 6px; width:16px; height:16px; background:url(' + record.get('localIcon') + ') no-repeat"></div>' + name;
    },

    authorRenderer: function(value, metaData, record) {
        if (!record || !record['getProducerStore']) {
            return value;
        }

        var producer = record['getProducerStore'].first();
        var website = producer.get('website');

        if (producer.get('name') == 'shopware AG' && !website) {
            website = 'http://www.shopware.com';
        }

        if (website && website.length > 0) {
            return '<a href="' + website + '" target="_blank">' + producer.get('name') + '</a>';
        } else {
            return producer.get('name');
        }
    },

    dateRenderer: function(value) {
        if (!value || !value.hasOwnProperty('date')) {
            return value;
        }
        var date = this.formatDate(value.date);
        return Ext.util.Format.date(date);
    },

    licenceRenderer: function(value, metaData, record) {
        var me = this;

        if (!record || !record['getLicenceStore']) {
            return;
        }
        var result = '';
        try {
            var licence = record['getLicenceStore'].first(),
                price = licence['getPriceStore'].first(),
                type = me.getTextForPriceType(price.get('type')),
                expiration = licence.get('expirationDate');
            result += type;
            if (price.get('type') == 'unlicensed') {
                result = Ext.String.format('<div style="color: [0]">[1]</div>', '#ff0000', result);
            }
        } catch (e) {
            return result;
        }

        if (!expiration) {
            return result;
        }

        if (expiration) {
            var expirationDate = new Date(expiration.date),
                today = new Date();
            result += '<br><span class="label">{s name="till"}until{/s}: </span><span class="date">' + Ext.util.Format.date(expiration.date) + '</span>';

            if (expirationDate < today) {
                result = Ext.String.format('<div style="color: [0]">[1]</div>', '#ff0000', result);
            }
        }

        return result;
    },

    createToolbarItems: function() {
        var me = this,
            items = me.callParent(arguments);

        Ext.Array.insert(items, 0, [
            me.createUploadButton(),
            me.createLicenseRefreshButton()
        ]);

        return items;
    },

    createUploadButton: function() {
        var me = this;

        me.uploadButton = Ext.create('Ext.button.Button', {
            text: '{s name="upload_plugin"}Upload plugin{/s}',
            iconCls: 'sprite-plus-circle',
            handler: function() {
                me.fireEvent('open-plugin-upload');
            }
        });

        return me.uploadButton;
    },

    createLicenseRefreshButton: function() {
        var me = this;

        me.uploadButton = Ext.create('Ext.button.Button', {
            text: '{s name="refresh_license"}Synchronize licenses{/s}',
            iconCls: 'sprite-license-key',
            handler: function() {
                me.refreshPluginLicenses();
            },
            disabled: !Shopware.app.Application.sbpAvailable
        });

        return me.uploadButton;
    },

    refreshPluginLicenses: function() {
        var me = this;
        me.setLoading(true);

        Ext.Ajax.request({
            url: '{url controller="PluginManager" action="getPluginInformation"}',
            params: {
                force: true
            },
            success: function (response) {
                response = JSON.parse(response.responseText);

                if (response.data.live) {
                    Shopware.Notification.createGrowlMessage('', '{s name="refresh_license_success"}{/s}');

                    me.store.load();
                    me.setLoading(false);
                } else if (response.data.shopSecretMissing && !me.hasTriedLogin) {
                    Shopware.app.Application.on('destroy-login', function (window, userPressed) {
                        if (userPressed) {
                            me.setLoading(false);
                        }
                    });
                    Shopware.app.Application.fireEvent('open-login', function (response) {
                        me.hasTriedLogin = true;
                        setTimeout(function () {
                            me.refreshPluginLicenses();
                            me.setLoading(false);
                        }, 1000);
                    });
                    Shopware.Notification.createGrowlMessage('', '{s name="refresh_license_login"}{/s}');
                } else if (response.data.shopSecretMissing) {
                    Shopware.Notification.createGrowlMessage('', '{s name="refresh_license_no_token"}{/s}');
                    me.setLoading(false);
                } else {
                    Shopware.Notification.createGrowlMessage('', '{s name="refresh_license_unknown"}{/s}');
                    me.setLoading(false);
                }
            }
        });
    },

    updateSafeModeCheckbox: function() {
        var me = this,
            state = me.checkInSafeMode();

        me.suspendSafeModeToggle = true;
        me.safeModeCheckbox.setDisabled(!state.inSafeMode && !state.hasActiveThirdPartyPlugins);
        me.safeModeCheckbox.setValue(state.inSafeMode);
        me.suspendSafeModeToggle = false;
    },

    createSafeModeCheckbox: function () {
        var me = this,
            state = me.checkInSafeMode(),
            label = '{s name="safe_mode"}Safe Mode{/s}';

        me.safeModeCheckbox = Ext.create('Ext.form.field.Checkbox', {
            fieldLabel: label,
            labelStyle: 'width:65px; margin-top: 2px;',
            checked: state.inSafeMode,
            disabled: !state.inSafeMode && !state.hasActiveThirdPartyPlugins,
            dock: 'bottom',
            handler: Ext.bind(me.onToggleSafeMode, me)
        });

        Shopware.app.Application.on('plugin-state-changed', Ext.bind(me.updateSafeModeCheckbox, me));

        return me.safeModeCheckbox;
    },

    onToggleSafeMode: function () {
        var me = this,
            state;

        if (me.suspendSafeModeToggle) {
            return;
        }
        state = me.checkInSafeMode();

        if (state.inSafeMode) {
            me.toggleSafeMode();
            return;
        }

        Ext.Msg.confirm(
            '{s name="safemodepopup/title"}{/s}',
            '{s name="safemodepopup/warning"}{/s}',
            function (button) {
                if (button == 'yes') {
                    me.toggleSafeMode();
                } else {
                    me.safeModeCheckbox.setRawValue(false);
                    me.safeModeCheckbox.lastValue = false;
                }
            }
        );
    },

    toggleSafeMode: function () {
        var content,
            msg = Shopware.Notification;

        var toggleSafeMode = Ext.Ajax.request({
            async: false,
            url: '{url controller=PluginManager action=toggleSafeMode}',
            method: 'GET',
            params: { }
        });

        var response = Ext.decode(toggleSafeMode.responseText);

        var title = '{s name="title/safe_mode"}{/s}';
        if (response.inSafeMode) {
            content = '{s name="content/safe_mode_on"}{/s}';
        } else {
            content = '{s name="content/safe_mode_off"}{/s}';
        }
        Shopware.app.Application.fireEvent('clear-all-cache');
        msg.createGrowlMessage(title, content);
        Shopware.app.Application.fireEvent('reload-local-listing');
    },

    checkInSafeMode: function () {
        var checkInSafeMode = Ext.Ajax.request({
            async: false,
            url: '{url controller=PluginManager action=isInSafeMode}',
            method: 'GET',
            params: { }
        });

        return Ext.decode(checkInSafeMode.responseText);
    },

    createActionColumnItems: function() {
        var me = this, items = [];

        items.push({
            iconCls: 'sprite-pencil',
            tooltip: '{s name="open"}Open{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.displayPluginEvent(record);
            }
        });

        items.push({
            iconCls: 'sprite-plus-circle',
            tooltip: '{s name="install"}Install{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.updateDummyPluginEvent(record);
            },
            getClass: function(value, metaData, record) {
                if (!record.allowDummyUpdate()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
            }
        });

        items.push({
            iconCls: 'sprite-minus-circle',
            tooltip: '{s name="install_uninstall"}Install / Uninstall{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                if (record.allowInstall()) {
                    me.registerConfigRequiredEvent(record);

                    me.installPluginEvent(record);
                } else {
                    me.uninstallPluginEvent(record);
                }
            },
            getClass: function(value, metaData, record) {
                if (record.allowDummyUpdate()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }

                if (record.allowInstall()) {
                    return 'sprite-plus-circle';
                }
            }
        });

        items.push({
            iconCls: 'sprite-arrow-continue',
            tooltip: '{s name="reinstall"}Reinstall{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.reinstallPluginEvent(record);
            },
            getClass: function(value, metaData, record) {
                if (!record.allowReinstall()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
            }
        });

        items.push({
            iconCls: 'sprite-arrow-circle-135',
            tooltip: '{s name="update_plugin"}Update{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.updatePluginEvent(record);
            },
            getClass: function(value, metaData, record) {
                if (!record.allowUpdate()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
                this.items[4].tooltip = '{s name="install_update"}Install update{/s} (v ' + record.get('availableVersion') + ')';
            }
        });

        items.push({
            iconCls: 'sprite-bin-metal-full',
            tooltip: '{s name="delete"}Delete{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.deletePluginEvent(record);
            },
            getClass: function(value, metaData, record) {
                if (!record.allowDelete()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
            }
        });

        items.push({
            iconCls: 'sprite-arrow-circle-225-left',
            tooltip: '{s name="local_update"}Update{/s}',
            handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                me.executePluginUpdateEvent(record);
            },
            getClass: function(value, metaData, record) {
                if (!record.allowLocalUpdate()) {
                    return Ext.baseCSSPrefix + 'hidden';
                }
            }
        });

        return items;
    }
});
// {/block}
