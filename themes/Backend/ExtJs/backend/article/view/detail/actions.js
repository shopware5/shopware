//{namespace name=backend/article/view/main}
Ext.define('Shopware.apps.Article.view.detail.Actions', {

    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend: 'Ext.form.FieldSet',

    /**
     * The Ext.container.Container.layout for the fieldset's immediate child items.
     * @object
     */
    layout: 'column',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.article-actions-field-set',

    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-actions-field-set',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets: {
        title: '{s name=detail/actions/title}Article actions{/s}',
        duplicate: '{s name=detail/actions/duplicate}Duplicate{/s}',
        delete: '{s name=detail/actions/delete}Delete{/s}',
        translate: '{s name=detail/actions/translate}Translate{/s}',
        preview: '{s name=detail/actions/preview}Article preview{/s}',
        previewShopSelect: '{s name=detail/actions/preview_select_shop}Select shop{/s}'
    },

    initComponent: function() {
        var me = this;
            mainWindow = me.subApp.articleWindow;

        mainWindow.on('storesLoaded', me.onStoresLoaded, me);
        me.title = me.snippets.title;
        me.items = me.createElements();
        me.registerEvents();
        me.callParent(arguments);
    },

    registerEvents: function() {
        this.addEvents(
            /**
             *
             * @event
             * @param [Ext.data.Model] - The article record
             */
            'duplicateArticle',

            /**
             *
             * @event
             * @param [Ext.data.Model] - The article record
             */
            'deleteArticle',

            /**
             *
             * @event
             * @param [Ext.data.Model] - The article record
             */
            'translateArticle',

            /**
             * Event will be fired when the user clicks the preview button which displayed
             * in the option panel of the sidebar.
             *
             * @event
             * @param [Ext.data.Model] - The article record
             * @param [Ext.data.Model] - The selected shop record
             */
            'articlePreview'
        );
    },

    createElements: function() {
        var me = this;

        me.leftContainer = Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            defaults: {
                labelWidth: 155,
                anchor: '100%'
            },
            padding: '0 20 0 0',
            layout: 'anchor',
            border: false,
            items: me.createLeftElements()
        });

        me.rightContainer = Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            layout: 'anchor',
            defaults: {
                labelWidth: 155,
                anchor: '100%'
            },
            border: false,
            items: me.createRightElements()
        });

        return [ me.leftContainer, me.rightContainer ];
    },

    createLeftElements: function() {
        var me = this;

        me.duplicateButtonNew = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-duplicate-article',
            text: me.snippets.duplicate,
            cls: 'small secondary',
            margin: '0 10 0 0',
            handler: function() {
                me.fireEvent('duplicateArticle', me.article);
            }
        });

        me.deleteButtonNew = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-minus-circle-frame',
            text: me.snippets.delete,
            cls: 'small secondary',
            margin: '0 10 0 0',
            handler: function() {
                me.fireEvent('deleteArticle', me.article);
            }
        });

        me.translateButtonNew = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-globe-green',
            text: me.snippets.translate,
            cls: 'small secondary',
            handler: function() {
                me.fireEvent('translateArticle', me.article);
            }
        });

        return Ext.create('Ext.container.Container', {
            layout: 'hbox',
            items: [
                me.duplicateButtonNew,
                me.deleteButtonNew,
                me.translateButtonNew
            ]
        });
    },

    createRightElements: function() {
        var me = this;

        me.shopCombo = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: me.snippets.preview,
            store: me.shopStore,
            labelWidth: 155,
            anchor: '100%',
            queryMode: 'local',
            valueField: 'id',
            displayField: 'name',
            editable: false,
            emptyText: me.snippets.previewShopSelect
        });

        me.previewButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-globe--arrow',
            cls: 'small secondary',
            margin: '2 0 0',
            handler: function() {
                me.fireEvent('articlePreview', me.article, me.shopCombo);
            }
        });

        return Ext.create('Ext.container.Container', {
            layout: 'hbox',
            items: [
                me.shopCombo,
                me.previewButton
            ]
        });
    },

    onStoresLoaded: function(article, stores) {
        var me = this;
        me.article = article;
        me.shopStore = stores['shops'];
        me.shopCombo.bindStore(me.shopStore);
    }
});