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
 * @package    Article
 * @subpackage Detail
 * @version    $Id$
 * @author     shopware AG
 */

/**
 * Shopware UI - Article detail page
 * The actions component displays a small actions toolbar above the article base data.
 * It contains important article actions such as article deleting or duplication.
 */
//{namespace name="backend/article/view/main"}
//{block name="backend/article/view/detail/actions"}
Ext.define('Shopware.apps.Article.view.detail.Actions', {

    /**
     * Define that the actions field set is an extension of the Ext.form.FieldSet
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
    initComponent: function() {
        var me = this,
            mainWindow = me.subApp.articleWindow;

        mainWindow.on('storesLoaded', me.onStoresLoaded, me);
        me.title = me.snippets.title;
        me.items = me.createElements();
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events
     */
    registerEvents: function() {
        this.addEvents(
            /**
             * Event will be fired when the user clicks the duplicate button
             * @event
             * @param [Ext.data.Model] - The article record
             */
            'duplicateArticle',

            /**
             * Event will be fired when the user clicks the delete button
             * @event
             * @param [Ext.data.Model] - The article record
             */
            'deleteArticle',

            /**
             * Event will be fired when the user clicks the translate button
             * @event
             * @param [Ext.data.Model] - The article record
             */
            'translateArticle',

            /**
             * Event will be fired when the user clicks the preview button
             * @event
             * @param [Ext.data.Model] - The article record
             * @param [Ext.data.Model] - The selected shop record
             */
            'articlePreview'
        );
    },

    /**
     * Creates all elements for the article actions fieldset
     * @returns Array
     */
    createElements: function() {
        var me = this;

        me.actionsContainer = Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            padding: '0 20 0 0',
            layout: 'anchor',
            border: false,
            items: me.createActionElements()
        });

        me.previewContainer = Ext.create('Ext.container.Container', {
            columnWidth: 0.5,
            border: false,
            items: me.createPreviewElements()
        });

        return [
            me.actionsContainer,
            me.previewContainer
        ];
    },

    /**
     * Creates the action buttons
     * @returns Ext.container.Container
     */
    createActionElements: function() {
        var me = this;

        me.actionsDuplicateBtn = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-duplicate-article',
            text: me.snippets.duplicate,
            cls: 'small secondary',
            margin: '0 10 0 0',
            handler: function() {
                me.fireEvent('duplicateArticle', me.article);
            }
        });

        me.actionsDeleteBtn = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-minus-circle-frame',
            text: me.snippets.delete,
            cls: 'small secondary',
            margin: '0 10 0 0',
            handler: function() {
                me.fireEvent('deleteArticle', me.article);
            }
        });

        me.actionsTranslateBtn = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-globe-green',
            text: me.snippets.translate,
            cls: 'small secondary',
            handler: function() {
                me.fireEvent('translateArticle', me.article);
            }
        });

        return Ext.create('Ext.container.Container', {
            layout: {
                type: 'hbox',
                pack: 'start',
                align: 'stretch'
            },
            items: [
                me.actionsDuplicateBtn,
                me.actionsDeleteBtn,
                me.actionsTranslateBtn
            ]
        });
    },

    /**
     * Creates the article preview button and combobox
     * @returns Ext.container.Container
     */
    createPreviewElements: function() {
        var me = this;

        me.shopCombo = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: me.snippets.preview,
            store: me.shopStore,
            labelWidth: 155,
            flex: 1,
            queryMode: 'local',
            valueField: 'id',
            displayField: 'name',
            editable: false,
            emptyText: me.snippets.previewShopSelect
        });

        me.actionsPreviewBtn = Ext.create('Ext.button.Button', {
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
                me.actionsPreviewBtn
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
//{/block}