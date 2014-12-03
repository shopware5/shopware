
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.view.detail.Container', {
    extend: 'Ext.container.Container',
    cls: 'plugin-manager-detail-page',
    alias: 'widget.plugin-manager-detail-page',
    padding: 30,
    minWidth: 780,

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    initComponent: function() {
        var me = this;

        me.items = [
            me.createMessageContainer(),
            me.createHeadlineContainer(),
            me.createTopContainer(),
            me.createInformationTab()
        ];

        me.callParent(arguments);
    },

    loadRecord: function(plugin) {
        var me = this;

        me.plugin = plugin;

        me.updateDescription(plugin);

        me.updateHeadline(plugin);

        me.updatePictures(plugin);

        me.updateComments(plugin);

        me.updateChangeLog(plugin);

        me.updateMetaData(plugin);

        me.updateConfiguration(plugin);

        var event = 'plugin-reloaded-' + me.plugin.get('technicalName');

        Shopware.app.Application.on(event, function(updated) {
            me.updateMetaData(updated);
            me.updateConfiguration(updated);
            me.hideLoadingMask();
        });

        event = 'plugin-bought-' + me.plugin.get('technicalName');

        Shopware.app.Application.on(event, function(bought) {
            me.displayBoughtMessage();
        });
    },

    displayBoughtMessage: function() {
        var me = this;

        me.boughtMessage = Shopware.Notification.createBlockMessage(
            '{s name="plugin_bought_message"}{/s}',
            'success'
        );

        me.messageContainer.add(me.boughtMessage);

        Ext.Function.defer(function() {
            try {
                me.boughtMessage.getEl().slideOut('t', { duration: 1000 });
            } catch (e) {
                me.boughtMessage.hide();
            }
        }, 6000);
    },

    updateConfiguration: function(plugin) {
        var me = this;

        me.configurationContainer.hide();
        me.configurationContainer.removeAll();

        if (plugin.get('formId') && plugin.get('installationDate') !== null) {
            me.informationTab.showTab(0);
            me.informationTab.navigationClick(0);
        } else {
            me.informationTab.hideTab(0);
            me.informationTab.navigationClick(1);
            me.configurationContainer.show();
            return;
        }

        me.configurationForm = Ext.create('Shopware.form.PluginPanel', {
            padding: 10,
            formId: plugin.get('formId'),
            descriptionField: false
        });

        me.configurationContainer.add(me.configurationForm);

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            style: 'background: #fff !important',
            dock: 'bottom',
            items: ['->', {
                xtype: 'button',
                cls: 'primary save-button',
                text: '{s name="save"}{/s}',
                handler: function() {
                    Shopware.app.Application.fireEvent(
                        'save-plugin-configuration',
                        plugin,
                        me.configurationForm
                    );
                }
            }]
        });

        me.configurationContainer.add(me.toolbar);
        me.configurationContainer.show();
    },

    updateDescription: function(plugin) {
        var me = this;

        me.descriptionContainer.removeAll();

        var description = plugin.get('description');

        if (!description || description.length <= 0) {
            me.informationTab.hideTab(1);
            return;
        }

        me.informationTab.showTab(1);

        me.descriptionContainer.add({
            xtype: 'component',
            html: '<h1 class="store-plugin-detail-description-headline">{s name="product_information"}{/s} <span class="plugin-name">' + plugin.get('label') + '</span></h1>' + plugin.get('description')
        });
    },

    updateMetaData: function(plugin) {
        var me = this, content;

        me.metaDataContainer.removeAll();

        content = Ext.create('Shopware.apps.PluginManager.view.detail.Meta', {
            plugin: plugin
        });
        me.metaDataContainer.add(content);

        if (plugin.isLocalPlugin() || plugin.allowDummyUpdate()) {
            content = Ext.create('Shopware.apps.PluginManager.view.detail.Actions', {
                plugin: plugin
            });
            me.metaDataContainer.add(content);

        } else {
            content = Ext.create('Shopware.apps.PluginManager.view.detail.Prices', {
                prices: plugin['getPricesStore'],
                plugin: plugin
            });

            me.metaDataContainer.add(content);

            me.metaDataContainer.add({
                xtype: 'component',
                cls: 'store-plugin-detail-star-description',
                html: '* {s name="vat_info"}{/s}'
            });
        }
    },

    updateHeadline: function(plugin) {
        var me = this;

        var content = Ext.create('Shopware.apps.PluginManager.view.detail.Header', {
            plugin: plugin
        });

        me.headlineContainer.removeAll();
        me.headlineContainer.add(content);
    },

    updatePictures: function(plugin) {
        var me = this;

        var content = Ext.create('Shopware.apps.PluginManager.view.components.ImageSlider', {
            store: plugin['getPicturesStore'],
            flex: 1
        });

        me.pictureContainer.removeAll();

        if (!plugin.hasStoreData()) {
            return null;
        }

        me.pictureContainer.add(content);
    },

    updateComments: function(plugin) {
        var me = this;

        var comments = Ext.create('Shopware.apps.PluginManager.view.detail.Comments', {
            plugin: plugin
        });

        if (comments.commentCount <= 0) {
            me.informationTab.hideTab(3);
            return;
        } else {
            me.informationTab.showTab(3);
        }

        me.commentContainer.add(comments);
    },

    updateChangeLog: function(plugin) {
        var me = this, items = [];

        me.changelogContainer.removeAll();

        var changelog = plugin.get('changelog');

        if (!changelog || changelog.length <= 0) {
            me.informationTab.hideTab(2);
            return;
        }

        me.informationTab.showTab(2);

        Ext.each(changelog, function(value) {
            var version = value.version;

            if (value.creationDate) {
                var date = Ext.util.Format.date(value.creationDate.date);
                version = version + '<div class="date">' + date + '</div>';
            }

            items.push({
                xtype: 'container',
                cls: 'changelog',
                layout: { type: 'hbox', align: 'stretch' },
                items: [{
                    xtype: 'component',
                    cls: 'version',
                    html: 'v ' + version,
                    width: 120
                }, {
                    xtype: 'component',
                    cls: 'version-changelog',
                    html: value.text,
                    flex: 1
                }]
            });
        });

        me.changelogContainer.add(items);
        me.changelogContainer.enable();
    },

    createMessageContainer: function() {
        var me = this;

        me.messageContainer = Ext.create('Ext.container.Container', {
            cls: 'message-container',
            margin: '0 0 20',
            items: []
        });

        return me.messageContainer;
    },

    createHeadlineContainer: function() {
        var me = this;

        me.headlineContainer = Ext.create('Ext.container.Container', {
            height: 130,
            margin: '0 0 15',
            layout: { type: 'hbox', align: 'stretch' },
            items: [ ]
        });

        return me.headlineContainer;
    },

    createTopContainer: function() {
        var me = this;

        me.pictureContainer = Ext.create('Ext.container.Container', {
            width: 430,
            height: 400,
            layout: { type: 'vbox', align: 'stretch' },
            items: [ ]
        });

        me.metaDataContainer = Ext.create('Ext.container.Container', {
            flex: 1,
            cls: 'store-plugin-detail-meta-data-wrapper',
            layout: { type: 'vbox', align: 'stretch' },
            items: [ ]
        });

        return Ext.create('Ext.container.Container', {
            height: 400,
            margin: '0 0 50',
            layout: { type: 'hbox', align: 'stretch' },
            items: [
                me.headlineContainer,
                me.pictureContainer,
                me.metaDataContainer
            ]
        });
    },

    createInformationTab: function() {
        var me = this;

        me.configurationContainer = Ext.create('Ext.container.Container', {
            title: '{s name="configuration"}{/s}',
            cls: 'store-plugin-detail-configuration-container',
            flex: 1,
            layout: {
                type: 'vbox',
                align: 'stretch'
            }
        });

        me.changelogContainer = Ext.create('Ext.container.Container', {
            title: '{s name="changelog"}{/s}',
            cls: 'store-plugin-detail-changelog-container',
            flex: 1,
            layout: {
                type: 'vbox',
                align: 'stretch'
            }
        });

        me.descriptionContainer = Ext.create('Ext.container.Container', {
            title: '{s name="description"}{/s}',
            cls: 'plugin-description-container',
            flex: 1,
            layout: {
                type: 'vbox',
                align: 'stretch'
            }
        });

        me.commentContainer = Ext.create('Ext.container.Container', {
            title: '{s name="comments"}{/s}',
            flex: 1,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            cls: 'plugin-comment-container'
        });

        me.informationTab = Ext.create('PluginManager.tab.Panel', {
            margin: '0 0 25',
            items: [
                me.configurationContainer,
                me.descriptionContainer,
                me.changelogContainer,
                me.commentContainer
            ]
        });

        return me.informationTab;
    }

});