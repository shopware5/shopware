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

//{namespace name=backend/performance/main}

//{block name="backend/performance/view/main/multi_request_tasks"}
Ext.define('Shopware.apps.Performance.view.main.MultiRequestTasks', {

    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.Window',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.performance-main-multi-request-tasks',

    /**
     * If the modal property is set to true, the user can't change the window focus to another window.
     * @boolean
     */
    modal: true,

    /**
     * Disable window resize
     * @boolean
     */
    resizable: false,

    /**
     * Disables the maximize button in the window header
     * @boolean
     */
    maximizable: false,

    /**
     * Remove the height property of Enlight.app.Window
     * @null
     */
    height: null,

    /**
     * Set width of the window
     * @integer
     */
    width: 360,

    /**
     * Disables the minimize button in the window header
     * @boolean
     */
    minimizable: false,

    seo: {
        article: {
            initialText: '{s name=listing/articles}Product URLs{/s}',
            progressText: '{s name=progress/articles}[0] of [1] product urls{/s}',
            requestUrl: '{url controller="Seo" action="seoArticle"}'
        },
        category: {
            initialText: '{s name=listing/category}Category URLs{/s}',
            progressText: '{s name=progress/category}[0] of [1] category urls{/s}',
            requestUrl: '{url controller="Seo" action="seoCategory"}'
        },
        emotion: {
            initialText: '{s name=listing/emotion}Emotion URLs{/s}',
            progressText: '{s name=progress/emotion}[0] of [1] emotion urls{/s}',
            requestUrl: '{url controller="Seo" action="seoEmotion"}'
        },
        blog: {
            initialText: '{s name=listing/blog}Blog URLs{/s}',
            progressText: '{s name=progress/blog}[0] of [1] blog urls{/s}',
            requestUrl: '{url controller="Seo" action="seoBlog"}'
        },
        static: {
            initialText: '{s name=listing/static}Static URLs{/s}',
            progressText: '{s name=progress/static}[0] of [1] static urls{/s}',
            requestUrl: '{url controller="Seo" action="seoStatic"}'
        },
        content: {
            initialText: '{s name=listing/content}Content URLs{/s}',
            progressText: '{s name=progress/content}[0] of [1] content urls{/s}',
            requestUrl: '{url controller="Seo" action="seoContent"}'
        },
        supplier: {
            initialText: '{s name=listing/supplier}Supplier URLs{/s}',
            progressText: '{s name=progress/supplier}[0] of [1] supplier urls{/s}',
            requestUrl: '{url controller="Seo" action="seoSupplier"}'
        },
        contentType: {
            initialText: '{s name=listing/contentTypes}{/s}',
            progressText: '{s name=progress/contentTypes}{/s}',
            requestUrl: '{url controller="Seo" action="seoContentType"}'
        }
    },

    httpCache: {
        category: {
            providerLabel: '{s name=progress/categoryLabel}Categories{/s}',
            requestUrl: '{url controller="Performance" action="warmUpCache" resource=category}',
        },
        emotion: {
            providerLabel: '{s name=progress/emotionLabel}Shopping Worlds{/s}',
            requestUrl: '{url controller="Performance" action="warmUpCache" resource=emotion}',
        },
        blog: {
            providerLabel: '{s name=progress/blogLabel}Blogs{/s}',
            requestUrl: '{url controller="Performance" action="warmUpCache" resource=blog}',
        },
        manufacturer: {
            providerLabel: '{s name=progress/manufacturerLabel}Suppliers{/s}',
            requestUrl: '{url controller="Performance" action="warmUpCache" resource=manufacturer}',
        },
        static: {
            providerLabel: '{s name=progress/staticLabel}Static Pages{/s}',
            requestUrl: '{url controller="Performance" action="warmUpCache" resource=static}',
        },
        product: {
            providerLabel: '{s name=progress/productLabel}Products{/s}',
            requestUrl: '{url controller="Performance" action="warmUpCache" resource=product}',
        },
        variantswitch: {
            providerLabel: '{s name=progress/variantSwitchLabel}Variant switch{/s}',
            requestUrl: '{url controller="Performance" action="warmUpCache" resource=variantswitch}',
        },
        productwithnumber: {
            providerLabel: '{s name=progress/productWithNumberLabel}Products with product number{/s}',
            requestUrl: '{url controller="Performance" action="warmUpCache" resource=productwithnumber}',
        },
        productwithcategory: {
            providerLabel: '{s name=progress/productWithCategoryLabel}Products with category parameter{/s}',
            requestUrl: '{url controller="Performance" action="warmUpCache" resource=productwithcategory}',
        }
    },

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        cancel: '{s name=progress/cancel}Cancel process{/s}',
        start: '{s name=progress/start}Start process{/s}',
        close: '{s name=progress/close}Close window{/s}'
    },

    /**
     * How many items should be worked in batch in one AJAX-request to the server
     */
    batchSize: 50,

    /**
     * For HTTPCache, how many URLs should be called concurrently?
     */
    concurrencySize: 2,

    /**
     * Type of generation
     */
    currentType: 'seo',

    /**
     * Current step of the single progressbar
     */
    current: {},

    /**
     * Disable border of the window
     */
    border:false,

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function () {
        var me = this;
        me.registerEvents();

        me.items = [{
            xtype: 'panel',
            unstyled: true,
            bodyPadding: 10,
            layout: 'anchor',
            style: 'background-color: #ebedef',
            defaults: {
                anchor: '100%'
            },
            items: me.createItems()
        }];

        me.dockedItems = [{
            xtype: 'toolbar',
            items: me.createButtons(),
            ui: 'shopware-ui',
            dock: 'bottom'
        }];

        Object.keys(me.httpCache).forEach(function (key) {
            me.current[key] = 0;
        });

        me.callParent(arguments);
    },

    /**
     * Helper function to create the window items.
     */
    createItems: function() {
        var me = this;

        if (me.currentType === 'seo') {
            return me.createSeoItems();
        } else if (me.currentType === 'httpCacheWarmer') {
            this.width = 480;
            return me.createHttpCacheWarmerItems();
        } else {
            return me.createSearchIndexItems();
        }
    },

    /**
     * Helper function to create the window items for the seo index
     * @returns Array
     */
    createSeoItems: function() {
        var me = this,
            items = [];

        me.iterateConfig('seo', function(err, config, configName) {
            if (err) {
                throw err;
            }

            me[configName + 'Bar'] = me.createProgressBar(configName, config.initialText);
            items.push(me[configName + 'Bar']);
        });

        return [
            me.createShopCombo('seo'),
            {
                xtype: 'container',
                padding: '20 0',
                items: items
            },
            me.createBatchSizeCombo()
        ];
    },

    /**
     * Helper function to create the window items for the seo index
     * @returns Array
     */
    createHttpCacheWarmerItems: function() {
        var me = this;

        me.progressBar = me.createProgressBar('httpCache', '{s name=progress/initialProgressbar}{/s}');

        return [
            {
                title: '{s name="fieldset/cache/settings"}{/s}',
                xtype: 'fieldset',
                layout: 'anchor',
                defaults: {
                    anchor: '100%'
                },
                items: [
                    me.createShopCombo('httpCache'),
                    {
                        xtype: 'container',
                        padding: '20 0',
                        margin: '0 5 0 105',
                        items: me.progressBar,
                    },
                    me.createBatchSizeCombo(),
                    me.createConcurrencySizeCombo()
                ]
            },
            {
                title: '{s name="fieldset/cache/settingsAdvanced"}{/s}',
                xtype: 'fieldset',
                collapsible: true,
                collapsed: true,
                items: me.createSettingsItems()
            }];
    },

    createSettingsItems: function() {
        var items = {
                0: [],
                1: [],
            },
            me = this;

        var i = 1;
        Object.keys(this.httpCache).forEach(function (name) {
            var index = (i % 2 === 0) ? 1 : 0;
            items[index].push({
                name: name,
                xtype: 'checkbox',
                fieldLabel: me.httpCache[name].providerLabel,
                inputValue: true,
                uncheckedValue: false,
                labelWidth: 155,
                listeners: {
                    change: function () {
                        if (me.shopCombo.getValue()) {
                            me.fireEvent('onShopSelected', me, me.shopCombo.getValue(), 'httpCache');
                        }
                    }
                }
            });
            i++;
        });

        me.settingsForm = Ext.create('Ext.form.Panel', {
            layout: {
                type: 'vbox',
                align : 'stretch',
                pack  : 'start',
            },
            padding: 10,
            bodyStyle : 'background:none',
            border: 0,
            items: [
                me.createSettingsInfoText(),
                {
                    flex: 2,
                    xtype: 'container',
                    layout: {
                        type: 'hbox',
                        pack: 'start',
                        align: 'stretch'
                    },
                    items: [
                        me.createSettingsLeftItems(items[0]),
                        me.createSettingsRightItems(items[1])
                    ]
                },
                {
                    xtype: 'container',
                    items: [
                        me.createSaveButton()
                    ]
                }
            ],
        });

        Ext.Ajax.request({
            url: '{url controller=UserConfig action=get}',
            params: {
                name: 'httpCache'
            },
            callback: function (request, success, response) {
                var config = Ext.JSON.decode(response.responseText);

                if (!Object.keys(config).length) {
                    config = me.getDefaultConfiguration();
                }

                Object.keys(config).forEach(function (key) {
                    me.settingsForm.down('[name="' + key + '"]').setValue(config[key]);
                });
            }
        });

        return me.settingsForm;
    },

    createSettingsInfoText: function() {
        return {
            xtype: 'container',
            html: '<span class="x-form-item-label" style="margin-top: 0;margin-bottom: 10px;">{s name=listing/optionsLabel}{/s}</span>',
            columnWidth: 1,
            flex: 0.5
        };
    },

    createSettingsLeftItems: function(items) {
        return {
            xtype: 'container',
            items: items,
            width: '48%',
            style: {
                'margin-right': '45px'
            }
        };
    },

    createSettingsRightItems: function(items) {
        return {
            xtype: 'container',
            items: items,
            width: '48%',
        };
    },

    /**
     * Helper method to create a descriptive text
     * @param html
     * @returns Ext.container.Container
     */
    createDescriptionContainer: function(html) {
        return Ext.create('Ext.container.Container', {
            style: 'color: #999; font-style: italic; margin: 15px 0 0 0;',
            html: html
        });
    },

    /**
     * Creates the shop combo box for the multi request window
     * for the seo and search index generation.
     */
    createShopCombo: function(taskName) {
        var me = this;

        me.shopCombo = Ext.create('Ext.form.field.ComboBox', {
            forceSelection: true,
            store: Ext.create('Shopware.apps.Performance.store.Shop').load(),
            valueField: 'id',
            displayField: 'name',
            queryMode: 'remote',
            fieldLabel: 'Shop',
            editable: false,
            listeners: {
                select: function() {
                    me.fireEvent('onShopSelected', me, this.getValue(), taskName);
                }
            }
        });

        return me.shopCombo;
    },

    /**
     * Helper function to create the window items for the search index
     */
    createSearchIndexItems: function() {

    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
        this.addEvents(
            'onShopSelected',
            'multiRequestTasksCancelProcess',
            'startSeoIndex'
        );
    },

    createConcurrencySizeCombo: function() {
        var me = this;

        me.concurrencySizeCombo = Ext.create('Ext.form.ComboBox', {
            fieldLabel: '{s name=multi_request/concurrency/label}Concurrency{/s}',
            helpText: '{s name=multi_request/concurrency/help}How many URLs should be requested in parallel? Default: 2{/s}',
            name: 'concurrencySize',
            margin: '0 0 10 0',
            allowBlank: false,
            value: me.concurrencySize,
            editable: true,
            displayField: 'concurrencySize',
            validator: function (value) {
                if (value > me.batchSizeCombo.getValue()) {
                    me.startButton.disable();
                    return '{s name=multi_request/concurrency/field_error}The Concurrency field can not have a greater value than the Batch size field{/s}';
                }
                me.batchSizeCombo.clearInvalid();

                if (me.shopCombo.value !== undefined) {
                    me.startButton.enable();
                }

                return true;
            },
            store: Ext.create('Ext.data.Store', {
                fields: [
                    { name: 'concurrencySize', type: 'int' }
                ],
                data: [
                    { concurrencySize: '1' },
                    { concurrencySize: '2' },
                    { concurrencySize: '3' },
                    { concurrencySize: '4' },
                    { concurrencySize: '5' },
                    { concurrencySize: '6' },
                    { concurrencySize: '7' },
                    { concurrencySize: '8' },
                    { concurrencySize: '9' },
                    { concurrencySize: '10' }
                ]
            })
        });

        return me.concurrencySizeCombo;
    },

    createBatchSizeCombo: function() {
        var me = this;

        me.batchSizeCombo = Ext.create('Ext.form.ComboBox', {
            fieldLabel: '{s name=multi_request/batch/label}Batch size{/s}',
            helpText: '{s name=multi_request/batch/help}How many records should be processed per request? Default: 5000{/s}',
            name: 'batchSize',
            margin: '0 0 10 0',
            allowBlank: false,
            value: me.batchSize,
            editable: true,
            displayField: 'batchSize',
            validator: function (value) {
                if (value < me.concurrencySizeCombo.getValue()) {
                    me.startButton.disable();
                    return '{s name=multi_request/concurrency/field_error}The Concurrency field can not have a greater value than the Batch size field{/s}';
                }
                me.concurrencySizeCombo.clearInvalid();

                if (me.shopCombo.value !== undefined) {
                    me.startButton.enable();
                }

                return true;
            },
            store: Ext.create('Ext.data.Store', {
                fields: [
                    { name: 'batchSize', type: 'int' }
                ],
                data: [
                    { batchSize: '1' },
                    { batchSize: '5' },
                    { batchSize: '10' },
                    { batchSize: '20' },
                    { batchSize: '30' },
                    { batchSize: '50' },
                    { batchSize: '75' },
                    { batchSize: '100' },
                    { batchSize: '150' },
                    { batchSize: '200' },
                    { batchSize: '250' },
                    { batchSize: '500' },
                    { batchSize: '1000' },
                    { batchSize: '1500' }
                ]
            })
        });

        return me.batchSizeCombo;
    },

    /**
     * Creates the progress which displays the progress status for the document creation.
     */
    createProgressBar: function(name, text) {
        return Ext.create('Ext.ProgressBar', {
            animate: true,
            name: name,
            text: text,
            margin: '0 0 15',
            style: 'border-width: 1px !important;',
            cls: 'left-align'
        });
    },

    /**
     * Creates the cancel button which allows the user to cancel the document creation in the
     * batch window. Event will be handled in the batch controller.
     */
    createStartButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.start,
            cls: 'primary',
            action: 'start',
            disabled: true,
            handler: function() {
                this.hide();
                me.cancelButton.show();
                me.closeButton.disable();
                if (me.currentType === 'seo') {
                    me.fireEvent('startSeoIndex', me);
                } else if (me.currentType === 'httpCacheWarmer') {
                    Ext.Msg.confirm('{s name=progress/confirmCacheClearTitle}{/s}', '{s name=progress/confirmCacheClearText}{/s}', function (response) {
                        if (response === 'yes') { // Clear cache and start
                            me.clearHttpCacheAndStart();
                        } else if(response === 'no') { // Just start
                            me.fireEvent('startHttpCacheWarmUp', me);
                        } else { // Cancel
                            me.startButton.show();
                            me.cancelButton.hide();
                            me.closeButton.enable();
                        }
                    });
                }
            }
        });
    },

    /**
     * Creates the cancel button which allows the user to cancel the document creation in the
     * batch window. Event will be handled in the batch controller.
     */
    createCancelButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.cancel,
            cls: 'primary',
            action: 'cancel',
            disabled: false,
            hidden: true,
            handler: function() {
                this.disable();
                me.fireEvent('multiRequestTasksCancelProcess', me);
            }
        });
    },

    /**
     * Creates the close button which allows the user to close the window. The window closing is handled over this
     * button to prevent that the user close the window while the batch process is already working.
     * So the user have to wait until the process are finish or the user can clicks the cancel button.
     * The button will enabled after the batch process are finish or the cancel event are fired and the batch process
     * successfully canceled.
     */
    createCloseButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.close,
            action: 'closeWindow',
            cls: 'secondary',
            handler: function() {
                me.destroy();
            }
        });
    },

    createSaveButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: '{s name="progress/standardSave"}{/s}',
            cls: 'primary',
            style: {
                float: 'right'
            },
            handler: function() {
                if (me.settingsForm) {
                    me.saveWarmupConfiguration();
                }
            }
        });
    },

    /**
     * Creates the button container for the close and cancel button
     *
     * @return { array }
     */
    createButtons: function() {
        var me = this;

        me.startButton = me.createStartButton();
        me.closeButton = me.createCloseButton();
        me.cancelButton = me.createCancelButton();

        return [
            '->',
            me.closeButton,
            me.startButton,
            me.cancelButton
        ];
    },

    /**
     * Iterates over the seo/httpCache config, depending on the "configName" parameter using the given callback.
     *
     * @param { string } configName
     * @param { function(object, string) } callback
     * @oaram { object } scope
     * @return { boolean }
     */
    iterateConfig: function(configName, callback, scope) {
        scope = scope || this;

        if (!this[configName]) {
            callback.apply(scope, [ new Error(Ext.String.format('Configuration [0] not found', configName)) ]);
            return false;
        }

        for (var key in this[configName]) {
            var config;

            if (!this[configName].hasOwnProperty(key)) {
                continue;
            }

            config = this[configName][key];
            callback.apply(scope, [null, config, key]);
        }

        return true;
    },

    /**
     * Adds a progress bar to the SEO URL generator / HttpCache warmer.
     * Leave param 'target' empty to add the new progress bar to both windows.
     *
     * @param { object } configuration
     * @param { string } name
     * @param { string } [target]
     */
    addProgressBar: function(configuration, name, target) {
        if (!target) {
            this.seo[name] = configuration;
            this.httpCache[name] = configuration;

            return;
        }

        this[target][name] = configuration;
    },

    saveWarmupConfiguration: function () {
        Ext.Ajax.request({
            url: '{url controller=UserConfig action=save}',
            params: {
                name: 'httpCache',
                config: Ext.JSON.encode(this.settingsForm.getValues())
            },
            success: function () {
                Shopware.Notification.createGrowlMessage(
                    '{s name="progress/standardSaveGrowlTitle"}{/s}',
                    '{s name="progress/standardSaveGrowlText"}{/s}'
                );
            }
        });
    },

    /**
     * Clear http cache before warming
     */
    clearHttpCacheAndStart: function () {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=Cache action=clearCache cache=Config}',
            params:{
                'cache[http]'   : 'on',
            },
            success: function () {
                me.fireEvent('startHttpCacheWarmUp', me);
            }
        });
    },

    getDefaultConfiguration: function () {
        var config = {};

        Object.keys(this.httpCache).forEach(function (key) {
            config[key] = true;
        });

        return config;
    }
});
//{/block}
