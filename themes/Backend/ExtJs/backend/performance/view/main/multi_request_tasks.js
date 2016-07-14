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
    extend: 'Enlight.app.SubWindow',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.performance-main-multi-request-tasks',

    /**
     * Define window width
     * @integer
     */
    width: 360,

    /**
     * Define window height
     * @integer
     */
    height: 450,

    /**
     * Display no footer button for the detail window
     * @boolean
     */
    footerButton: false,

    /**
     * Set vbox layout and stretch align to display the toolbar on top and the button container
     * under the toolbar.
     * @object
     */
    layout: {
        align: 'stretch',
        type: 'vbox'
    },

    /**
     * If the modal property is set to true, the user can't change the window focus to another window.
     * @boolean
     */
    modal: true,

    /**
     * The body padding is used in order to have a smooth side clearance.
     * @integer
     */
    bodyPadding: 10,

    /**
     * Disable the close icon in the window header
     * @boolean
     */
    closable: false,

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
     * Disables the minimize button in the window header
     * @boolean
     */
    minimizable: false,

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        cancel:'{s name=progress/cancel}Cancel process{/s}',
        start:'{s name=progress/start}Start process{/s}',
        close:'{s name=progress/close}Close window{/s}',
        seo: {
            article: '{s name=progress/articles}[0] of [1] article urls{/s}',
            category: '{s name=progress/category}[0] of [1] category urls{/s}',
            emotion: '{s name=progress/emotion}[0] of [1] emotion urls{/s}',
            blog: '{s name=progress/blog}[0] of [1] blog urls{/s}',
            static: '{s name=progress/static}[0] of [1] static urls{/s}',
            content: '{s name=progress/content}[0] of [1] content urls{/s}',
            supplier: '{s name=progress/supplier}[0] of [1] supplier urls{/s}'
        },
        seoListing: {
            article: '{s name=listing/articles}Article URLs{/s}',
            category: '{s name=listing/category}Category URLs{/s}',
            emotion: '{s name=listing/emotion}Emotion URLs{/s}',
            blog: '{s name=listing/blog}Blog URLs{/s}',
            static: '{s name=listing/static}Static URLs{/s}',
            content: '{s name=listing/content}Content URLs{/s}',
            supplier: '{s name=listing/supplier}Supplier URLs{/s}'
        },
        httpCacheWarmer: {
            initialArticle: '{s name=progress/initialArticles}Article URLs...{/s}',
            initialCategory: '{s name=progress/initialCategory}Category URLs...{/s}',
            initialBlog: '{s name=progress/initialBlog}Blog URLs...{/s}',
            initialStatic: '{s name=progress/initialStatic}Static URLs...{/s}',
            initialSupplier: '{s name=progress/initialSupplier}Supplier URLs...{/s}',
            article: '{s name=progress/articles}[0] of [1] article URLs{/s}',
            category: '{s name=progress/category}[0] of [1] category URLs{/s}',
            blog: '{s name=progress/blog}[0] of [1] blog URLs{/s}',
            static: '{s name=progress/httpCacheWarmer/static}[0] of [1] static URLs{/s}',
            supplier: '{s name=progress/supplier}[0] of [1] supplier URLs{/s}'
        }
    },

    batchSize: 50,

    currentType: 'seo',


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
        me.items = me.createItems();
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
        var me = this;

        me.articleProgress = me.createProgressBar('article', me.snippets.seoListing.article);
        me.categoryProgress = me.createProgressBar('category', me.snippets.seoListing.category);
        me.emotionProgress = me.createProgressBar('emotion', me.snippets.seoListing.emotion);
        me.staticProgress = me.createProgressBar('static', me.snippets.seoListing.static);
        me.blogProgress = me.createProgressBar('blog', me.snippets.seoListing.blog);
        me.contentProgress = me.createProgressBar('content', me.snippets.seoListing.content);
        me.supplierProgress = me.createProgressBar('supplier', me.snippets.seoListing.supplier);

        return [
            me.createShopCombo(),
            {
                xtype: 'container',
                padding: '20 0',
                items: [
                    me.articleProgress,
                    me.categoryProgress,
                    me.emotionProgress,
                    me.blogProgress,
                    me.staticProgress,
                    me.contentProgress,
                    me.supplierProgress
                ]
            },
            me.createBatchSizeCombo(),
            me.createButtons()
        ];
    },

    /**
     * Helper function to create the window items for the seo index
     * @returns Array
     */
    createHttpCacheWarmerItems: function() {
        var me = this;

        me.articleProgress = me.createProgressBar('article', me.snippets.httpCacheWarmer.initialArticle);
        me.categoryProgress = me.createProgressBar('category', me.snippets.httpCacheWarmer.initialCategory);
        me.staticProgress = me.createProgressBar('static', me.snippets.httpCacheWarmer.initialStatic);
        me.blogProgress = me.createProgressBar('blog', me.snippets.httpCacheWarmer.initialBlog);
        me.supplierProgress = me.createProgressBar('supplier', me.snippets.httpCacheWarmer.initialSupplier);

        return [
            me.createShopCombo(),
            {
                xtype: 'container',
                padding: '20 0',
                items: [
                    me.articleProgress,
                    me.categoryProgress,
                    me.blogProgress,
                    me.staticProgress,
                    me.supplierProgress
                ]
            },
            me.createBatchSizeCombo(),
            me.createButtons()
        ];
    },


    /**
     * Creates the shop combo box for the multi request window
     * for the seo and search index generation.
     */
    createShopCombo: function() {
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
                    me.fireEvent('onShopSelected', me, this.getValue());
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
            store: Ext.create('Ext.data.Store', {
                fields: [
                    { name: 'batchSize',  type: 'int' }
                ],
                data : [
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
        var me = this;

        return Ext.create('Ext.ProgressBar', {
            animate: true,
            name: name,
            text: text,
            margin: '0 0 15',
            style: 'border-width: 1px !important;',
            cls:'left-align'
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
                }
                else if (me.currentType === 'httpCacheWarmer') {
                    me.fireEvent('startHttpCacheWarmUp', me);
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
            flex: 1,
            action: 'closeWindow',
            cls: 'secondary',
            handler: function() {
                me.destroy();
            }
        });
    },

    /**
     * Creates the button container for the close and cancel button
     *
     * @return Ext.container.Container
     */
    createButtons: function() {
        var me = this;

        me.startButton  = me.createStartButton();
        me.closeButton  = me.createCloseButton();
        me.cancelButton = me.createCancelButton();

        return Ext.create('Ext.container.Container', {
            layout: 'hbox',
            items: [
                me.startButton,
                me.cancelButton,
                me.closeButton
            ]
        });
    }
});
//{/block}
