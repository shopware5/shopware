/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article Detail page - Sidebar
 * The option component contains different configuration elements for the article.
 * It contains the button field set to translate, duplicate and delete the article,
 * the preview field set to select a shop of a combo box and preview the article in the frontend detail page.
 * A rapid categorization to grant the user an easy way to assign the article to different categories.
 * And a file upload field set to upload article images.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/detail/sidebar/option"}
Ext.define('Shopware.apps.Article.view.detail.sidebar.Option', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.form.Panel',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-sidebar-option',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title:'{s name=detail/sidebar/options/title}Options{/s}',
        articleOptions:'{s name=detail/sidebar/options/article_options}Article options{/s}',
        duplicate:'{s name=detail/sidebar/options/duplicate}Duplicate{/s}',
        delete:'{s name=detail/sidebar/options/delete}Delete{/s}',
        translate:'{s name=detail/sidebar/options/translate}Translate{/s}',

        shop:'{s name=detail/sidebar/options/shop}Select shop{/s}',
        articlePreview:'{s name=detail/sidebar/options/article_preview}Article preview{/s}',
        categoryCombo:'{s name=detail/sidebar/options/select_category}Select category{/s}',
        selectedCategories:'{s name=detail/sidebar/options/selected_categories}Assigned categories{/s}',
        categoryColumns: {
            name:'{s name=detail/sidebar/options/columns/name}Name{/s}',
            actionTooltip:'{s name=detail/sidebar/options/tooltip}Delete entry{/s}'
        },
        rapidCategorization: '{s name=detail/sidebar/options/rapid_categorization}Rapid categorization{/s}',
        upload: '{s name=detail/sidebar/options/upload_button}Select image{/s}',
        dropZone: '{s name=detail/sidebar/options/drop_zone}Upload files via drag&drop{/s}',
        imageUpload: '{s name=detail/sidebar/options/image_field_set}Upload images directly{/s}',
        empty:'{s name=empty}Please select...{/s}'
    },

    /**
     * Contains the field set defaults.
     */
    defaults: {
        labelWidth: 155,
        padding: 10
    },

    bodyPadding: 10,
    autoScroll: true,

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
    initComponent:function () {
        var me = this;
        me.title = me.snippets.title;
        me.items = me.createElements();
        me.registerEvents();
        me.addCls(Ext.baseCSSPrefix + 'article-sidebar-option');
        me.callParent(arguments);
    },

    /**
     * Creates the elements for the options panel.
     * @return array
     */
    createElements: function() {
        var me = this;

        me.buttonContainer = me.createButtonContainer();
        me.articlePreview = me.createArticlePreview();
        me.rapidCategorization = me.createRapidCategorization();
        me.imageContainer = me.createImageContainer();

        return [ me.buttonContainer, me.articlePreview, me.rapidCategorization, me.imageContainer ];
    },

    /**
     * Registers additional custom component events.
     * @return void
     */
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
            'articlePreview',

            /**
             * Event will be fired when the user select an item of the category combo box.
             *
             * @event
             * @param [array] - Array of the selected items
             * @param [Ext.grid.Panel] - The category list
             */
            'addCategory',

            /**
             * Event will be fired when the user want to upload images over the button.
             *
             * @event
             * @param [object]
             */
            'mediaUpload'
        );
    },

    /**
     * Creates the container for the copy, delete and translation button
     * @return Ext.form.FieldSet
     */
    createButtonContainer: function() {
        var me = this;

        me.duplicateButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-blue-folder--plus',
            text: me.snippets.duplicate,
            cls: 'small secondary',
            margin: '0 10 0 0',
            handler: function() {
                me.fireEvent('duplicateArticle', me.article);
            }
        });

        me.deleteButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-minus-circle-frame',
            text: me.snippets.delete,
            cls: 'small secondary',
            margin: '0 10 0 0',
            handler: function() {
                me.fireEvent('deleteArticle', me.article);
            }
        });

        me.translateButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-globe-green',
            text: me.snippets.translate,
            cls: 'small secondary',
            handler: function() {
                me.fireEvent('translateArticle', me.article);
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.articleOptions,
            layout: 'hbox',
            defaults: {
                flex: 1
            },
            items: [
				/*{if {acl_is_allowed privilege=save}}*/
                me.duplicateButton,
				/*{/if}*/
				/*{if {acl_is_allowed privilege=delete}}*/
                me.deleteButton,
				/*{/if}*/
                me.translateButton
            ]
        });

    },

    /**
     * Creates the container for the article preview. Contains a combo box for the shop selection and
     * a button which displays the article in the selected shop.
     * @return Ext.form.FieldSet
     */
    createArticlePreview: function() {
        var me = this;

        me.shopCombo = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: me.snippets.shop,
            store: me.shopStore,
            labelWidth: 75,
            queryMode: 'local',
            valueField: 'id',
            displayField: 'name',
            editable: false,
            emptyText: me.snippets.empty
        });

        me.previewButton = Ext.create('Ext.button.Button', {
            iconCls: 'sprite-globe--arrow',
            cls: 'small secondary',
            margin: '2 0 0',
            handler: function() {
                me.fireEvent('articlePreview', me.article, me.shopCombo);
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.articlePreview,
            layout: 'column',
            items: [
                {
                    xtype:'container',
                    columnWidth: 0.85,
                    items: [ me.shopCombo ]
                },
                {
                    xtype:'container',
                    columnWidth: 0.15,
                    margin: '0 0 0 5',
                    items: [ me.previewButton ]
                }
            ]
        });
    },

    /**
     * Creates the field set for the rapid categorization. Contains a grid with displays already
     * selected categories and a search field to add more categories.
     * @return Ext.form.FieldSet
     */
    createRapidCategorization: function() {
        var me = this;

        me.categorySearch = Ext.create('Ext.form.field.ComboBox', {
            anchor: '100%',
            name: 'categoryId',
            margin: '0 0 15',
            fieldLabel: me.snippets.categoryCombo,
            store: Ext.create('Shopware.apps.Article.store.CategoryPath'),
            valueField: 'id',
            displayField: 'name',
            listeners: {
                select: function(combo, records) {
                    me.fireEvent('addCategory', records, me.categoryList);
                    combo.setValue('');
                }
            }
        });

        me.categoryList = Ext.create('Shopware.apps.Article.view.category.List', {
            title: me.snippets.selectedCategories,
            anchor: '100%',
            article: me.article,
            height: 115,
            minHeight: 115,
            maxHeight: 115,
            //This grid needs no selection model, paging and toolbar, so we override this function
            getGridSelModel: function() {},
            createPagingBar: function() {},
            createToolbar: function() {}
        });

        return Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            title: me.snippets.rapidCategorization,
            items: [
                me.categorySearch,
                me.categoryList
            ]
        });
    },

    /**
     * Creates the field set for the article image upload. To upload the article images, the container contains
     * a drag and drop zone and a file upload field.
     * @return Ext.form.FieldSet
     */
    createImageContainer: function() {
        var me = this, fieldset;
        
        fieldset = Ext.create('Ext.form.FieldSet', {
            layout: 'anchor',
            title: me.snippets.imageUpload
        });

        me.uploadField  = Ext.create('Ext.form.field.File', {
            buttonOnly: false,
            labelWidth: 100,
            anchor: '100%',
            name: 'fileId',
            margin: '0 0 15',
            buttonText : me.snippets.upload,
            listeners: {
                scope: this,
                afterrender: function(btn) {
                    btn.fileInputEl.dom.multiple = true;
                },
                change: function(field) {
                    me.fireEvent('mediaUpload', field)
                }
            },
            buttonConfig : {
                iconCls: 'sprite-inbox-upload',
                cls: 'small secondary'
            }
        });
        
        if(Ext.isIE || Ext.isSafari) {
	    	var form = Ext.create('Ext.form.Panel', {
	    		unstyled: true,
	    		border: 0,
	    		bodyBorder: 0,
	    		style: 'background: transparent',
	    		bodyStyle: 'background: transparent',
		    	url: '{url controller="mediaManager" action="upload"}?albumID=-1',
		    	items: [ me.uploadField ]
	    	});
	    	me.uploadField = form;
        }
        
	    fieldset.add(me.uploadField);
        
        var config = { dropZoneConfig: { height: 85, hideOnLegacy: true, showInput: false } };
        me.dropZone = Ext.create('Shopware.apps.Article.view.image.DropZone', config);
        me.dropZone.mediaDropZone.height = 60;
   
        fieldset.add(me.dropZone);
        return fieldset;
    }

});
//{/block}
