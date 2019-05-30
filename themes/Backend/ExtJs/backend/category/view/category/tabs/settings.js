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
 * @package    Category
 * @subpackage Settings
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/main} */

/**
 * Shopware UI - Category Settings
 *
 * Shows all Category detail settings
 */
//{block name="backend/category/view/tabs/settings"}
Ext.define('Shopware.apps.Category.view.category.tabs.Settings', {
    /**
     * Parent Element Ext.container.Container
     *
     * @string
     */
    extend:'Ext.form.Panel',

    /**
     * Register the alias for this class.
     *
     * @string
     */
    alias:'widget.category-category-tabs-settings',

    cls: 'shopware-form',

    /**
     * Title of this tab
     * @string
     */
    title:'{s name=tabs/settings_title}Settings{/s}',

    /**
     * Specifies the border for this component. The border can be a single numeric
     * value to apply to all sides or it can be a CSS style specification for each
     * style, for example: '10 5 3 10'.
     *
     * Default: 0
     * @integer
     */
    border: 0,

    /**
     * Display the the contents of this tab immediately
     *
     * @boolean
     */
    autoShow : true,

    /**
     * enable auto scroll
     *
     * @boolean
     */
    autoScroll: true,

    /**
     * Used layout column
     *
     * @string
     */
    layout: 'anchor',

    /**
     * Body padding
     *
     * @integer
     */
    bodyPadding: 10,

    /**
     * Translations
     *
     * @object
     */
    snippets : {
        noticeText : '{s name=view/settings_notice}To move a category, simply click and drag it to the desired position.<br/><br/>Right clicking in the menu to the left enables you to add or delete subcategories.{/s}',
        emotionNotice : '{s name=view/settings_emotion_notice}An emotion is linked to this category{/s}',

        createSubCategoryTitle : '{s name=view/settings_create_category_title}Create sub category{/s}',
        createSubCategoryLabel : '{s name=view/settings_create_category_label}Sub category{/s}',
        createSubCategoryButton: '{s name=view/settings_create_category_button}Create sub category{/s}',

        defaultSettingsTitleEmpty : '{s name=view/settings_default_settings_title_empty}Default settings{/s}',
        defaultSettingsTitle : '{s name=view/settings_default_settings_title}Default Settings - Category: [0] (System-ID: [1]){/s}',

        defaultSettingsCategoryLabel : '{s name=view/settings_default_settings_category_label}Category{/s}',
        defaultSettingsActiveLabel : '{s name=view/settings_default_settings_active_label}Active{/s}',
        defaultSettingsBlogLabel : '{s name=view/settings_default_settings_blog_label}Blog category{/s}',

        defaultSettingsNameLabel : '{s name=view/settings_default_settings_name_label}Description{/s}',
        defaultSettingsLinkExternalPageLabel : '{s name=view/settings_default_settings_link_external_page_label}Link to an external site{/s}',
        defaultSettingsLinkExternalPageHelp : '{s name=view/settings_default_settings_link_external_page_help}The URL must begin with: http://{/s}',
        defaultSettingsLinkExternalPageTarget : '{s name=view/settings_default_settings_link_external_page_target}Target for external site{/s}',
        defaultSettingsLinkExternalPageTargetSelf : '{s name=view/settings_default_settings_link_external_page_target_self}Same window (default){/s}',
        defaultSettingsLinkExternalPageTargetBlank : '{s name=view/settings_default_settings_link_external_page_target_blank}New window{/s}',

        defaultSettingsImageLabel : '{s name=view/settings_default_settings_image_label}Image{/s}',
        defaultSettingsImageButtonText : '{s name=view/settings_default_settings_image_button_text}Select an image{/s}',
        defaultSettingsImageSupportText : '{s name=view/settings_default_settings_image_support_text}You can use this image for individual template changes. This includes, for instance, the illustration of your category.{/s}',
        errorMessageWrongFileTypeTitle : '{s name=view/error_message_wrong_file_type_title}Wrong file type{/s}',
        errorMessageWrongFileType : '{s name=view/error_message_wrong_file_type}Wrong file type selected.{/s}',

        defaultSettingsTemplateLabel : '{s name=view/settings_default_settings_template_label}Individual layout{/s}',
        defaultSettingsTemplateLabelStandard: '{s name=view/settings_default_settings_template_standard}Standard{/s}',
        defaultSettingsTemplateNotAvailable: '{s name=view/settings_default_settings_template_not_available}Not available{/s}',
        defaultSettingsHideTopLabel : '{s name=view/settings_default_settings_no_top_navigation_label}Do NOT show in top navigation.{/s}',
        defaultSettingsNoDesignSwitchLabel : '{s name=view/settings_default_settings_no_design_switch_label}Do NOT switch design.{/s}',

        defaultSettingsProductLayoutLabel: '{s name=view/settings_default_settings_box_layout_label}Product layout{/s}',
        defaultSettingsProductLayoutHelp: '{s name=view/settings_default_settings_box_layout_help}Product layout allows you to control how your products are presented on the category page. Choose between three different layouts to fine-tune your product display. You can select a layout for each category or automatically adopt the settings from the parent category.{/s}',
        defaultSettingsProductStream: '{s name=view/settings_default_settings_box_stream_label}Product stream{/s}',
        defaultSettingsProductStreamHelp: '{s name=view/settings_default_settings_box_stream_help}If a product stream is selected, the items from the stream will be used instead of the assigned ones. Subcategories do not inherit items from product streams.{/s}',

        cmsTitle : '{s name=view/settings_cms_title}CMS functions{/s}',
        cmsHeaderLabel : '{s name=view/settings_cms_header_label}Header{/s}',
        cmsTextLabel : '{s name=view/settings_cms_text_label}Text{/s}',

        metaGroupTitle : '{s name=view/settings_meta_description_title}Meta information{/s}',
        metaTitle : '{s name=view/settings_meta_title_label}Meta title{/s}',
        metaDescription : '{s name=view/settings_meta_description_label}Meta description{/s}',
        metaKeywords : '{s name=view/settings_meta_keywords_label}Meta keywords{/s}',

        attribute_title : '{s name=view/settings_attribute_title}Free text fields{/s}',

        categorySave : '{s name=view/settings_save}Save category{/s}',

        growlMessage: '{s name=window/main_title}Category{/s}'
    },

    /**
     * Single Form elements to access them from the controller
     */
    /**
     * Form part containing the form for creating a new subcategory
     *
     * @object [Ext.form.FieldSet]
     */
    createCategory  : null,

    /**
     * Form part containing the form with the default category settings
     * @object [Ext.form.FieldSet]
     */
    defaultSettings : null,

    /**
     * From part containing the form with CMS related settings
     *
     * @object [Ext.form.FieldSet]
     */
    cmsSettings     : null,

    /**
     * Form part containing the form with the metadata settings
     *
     * @object [Ext.form.FieldSet]
     */
    metaInfo        : null,

    /**
     * Form part containing the form for the six customizable fields
     *
     * @object [Ext.form.FieldSet]
     */
    attributes       : null,

    /**
     * Default field attributes
     */
    defaults: {
        anchor : '100%',
        labelWidth:155
    },

    plugins: [{
        ptype: 'translation',
        translationType: 'category'
    }],

    /**
     * Initialize the Shopware.apps.Category.view.category.tabs.Settings and defines the necessary
     * default configuration
     */
    initComponent:function ()
    {
        var me = this;
        me.items = me.getItems();

        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Creates all fields for the form
     *
     * @return array of form elements
     */
    getItems:function ()
    {
        var me = this;
        me.emotionNotice        = me.createEmotionNoticeContainer();
        me.notice               = me.getNotice();
        me.createCategory       = me.getCreateCategory();
        me.defaultSettings      = me.getDefaultFormField();
        me.cmsSettings          = me.getCmsSettings();
        me.metaInfo             = me.getMetaInfo();
        me.attributes           = me.getAttributes();
        return [
            me.emotionNotice,
            me.notice,
            me.createCategory,
            me.defaultSettings,
            me.cmsSettings,
            me.metaInfo,
            me.attributes
        ];
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function() {
        var me = this;
        /**
         * Event will be fired when a record is loaded into the settings form.
         *
         * This event can easily be captured in the controller
         * eg.
         * <code>
         * this.control({ 'recordloaded' : function(){
         *     console.log('New recored has been loaded.');
         * }
         * </code>
         *
         * @event recordloaded
         */
        me.addEvents('recordloaded');
    },

    /**
     * Creates the notice container which is displayed on top of the detail tab panel.
     * @return {[object]}
     */
    createEmotionNoticeContainer: function() {
        var me = this,
            blockMessage = Shopware.Notification.createBlockMessage(me.snippets.emotionNotice, 'notice');
        blockMessage.hide();
        return blockMessage;
    },

    /**
     * Builds and returns the notice section of the form.
     *
     * @return Ext.container.Container
     */
    getNotice : function()
    {
        var me = this;
        return {
            xtype: 'container',
            html : me.snippets.noticeText,
            cls: Ext.baseCSSPrefix + 'global-notice-text'
        }
    },

    /**
     * Builds and returns the create category section of the form.
     *
     * This is a way to create a new sub category. It will look at the category tree and takes the selected node as
     * parent node and adds an new node under it. After this has been done, the new node will be selected and loaded into
     * the form.
     *
     * @return Ext.form.FieldSet
     */
    getCreateCategory : function()
    {
        var me =  this,
            addSubCategoryItems = [];

        /*{if {acl_is_allowed privilege=create}}*/
        addSubCategoryItems.push(Ext.create('Ext.button.Button',{
            text : me.snippets.createSubCategoryButton,
            action: 'addCategory',
            cls: 'small primary',
            flex: 1
        }));
        /* {/if} */
        return Ext.create('Ext.form.FieldSet',{
            title: me.snippets.createSubCategoryTitle,
            anchor: '100%',
            defaults : me.defaults,
            disabled : true,
            items : [
                {
                    xtype : 'textfield',
                    fieldLabel : me.snippets.createSubCategoryLabel,
                    name : 'newCategoryName'
                }, {
                    xtype: 'container',
                    items: addSubCategoryItems
                }
            ]
        });
    },

    /**
     * Builds and creates the fieldset which contains the main settings for a category.
     * Those settings are
     *  - an active flag. This flag indicates if the loaded category is active or nor.
     *  - description or name for the category (this text will be displayed in the menu)
     *  - template selection. This part will be hidden if the parent node is the root node. The data for this field
     *    can be defined in the default shop settings.
     *  - hide in top navigation flag.
     *  - do not switch the design flag.
     *  - show filter group flags.
     *  - do not show filter flag.
     *
     * @return Ext.form.FieldSet
     */
    getDefaultFormField:function () {
        var me = this;
        return Ext.create('Ext.form.FieldSet', {
            title:me.snippets.defaultSettingsTitleEmpty,
            anchor:'100%',
            defaults:me.defaults,
            disabled:true,
            items:me.getDefaultSettingItems()
        });
    },

    /**
     * Returns the items for the default form fieldset
     *
     * @return { Array }
     */
    getDefaultSettingItems:function () {
        var me = this;
        // Create the template combo box and register it in the local namespace to
        // gain access from the outside.
        me.templateComboBox = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel:me.snippets.defaultSettingsTemplateLabel,
            store:me.templateStore,
            labelWidth:155,
            valueField:'template',
            displayField:'name',
            editable:true,
            allowBlank:true,
            name:'template',
            queryMode: 'local',
            helpText: '{s name="template_selection_help"}{/s}'
        });

        // Add record for default value
        me.templateStore.on('load', function(store, records) {
            var record = store.model.create({
                template: '',
                name: me.snippets.defaultSettingsTemplateLabelStandard
            });
            store.insert(0, record);
        }, me);

        me.productLayoutField = Ext.create('Shopware.apps.Base.view.element.ProductBoxLayoutSelect', {
            name: 'productBoxLayout',
            labelWidth: 155,
            storeConfig: {
                displayExtendLayout: true,
                displayBasicLayout: true,
                displayMinimalLayout: true,
                displayImageLayout: true,
                displayListLayout: true
            }
        });

        me.streamSelection = Ext.create('Shopware.form.field.ProductStreamSelection', {
            name: 'streamId',
            labelWidth: 155,
            translatable: true
        });

        return [
            {
                xtype:'checkboxgroup',
                columns:2,
                defaultType:'checkboxfield',
                margin:'3 0 0 0',
                defaults:Ext.applyIf({
                    inputValue:true,
                    uncheckedValue:false
                }, me.defaults ),
                items:[
                    {
                        boxLabel:me.snippets.defaultSettingsActiveLabel,
                        name:'active',
                        dataIndex:'active'
                    },
                    {
                        boxLabel:me.snippets.defaultSettingsBlogLabel,
                        name:'blog',
                        dataIndex:'blog',
                        listeners: {
                            change: function (element, value) {
                                me.shopSelector.setDisabled(!value)
                            }
                        }
                    }
                ]
            },
            {
                xtype:'textfield',
                fieldLabel:me.snippets.defaultSettingsNameLabel,
                dataIndex:'name',
                translationName: 'description',
                translatable: true,
                name:'name'
            },
            me.templateComboBox,
            me.productLayoutField,
            me.streamSelection,
            {
                xtype:'textfield',
                fieldLabel:me.snippets.defaultSettingsLinkExternalPageLabel,
                helpText:me.snippets.defaultSettingsLinkExternalPageHelp,
                dataIndex:'external',
                name:'external',
                translatable: true,
                translationName: 'external',
            },
            {
                fieldLabel: me.snippets.defaultSettingsLinkExternalPageTarget,
                xtype: 'combo',
                name: 'externalTarget',
                valueField: 'externalTarget',
                displayField: 'externalTarget',
                anchor: '100%',
                allowBlank: true,
                editable: true,
                store: new Ext.data.Store({
                    fields: [
                        { name: 'externalTarget' }
                    ],
                    data: [
                        { externalTarget: me.snippets.defaultSettingsLinkExternalPageTargetSelf },
                        { externalTarget: me.snippets.defaultSettingsLinkExternalPageTargetBlank }
                    ]
                }),
                translatable: true,
                translationName: 'externalTarget'
            },
            {
                xtype:'shopware-media-field',
                fieldLabel:me.snippets.defaultSettingsImageLabel,
                buttonText:me.snippets.defaultSettingsImageButtonText,
                name:'imagePath',
                readOnly:false,
                valueField:'id',
                supportText:me.snippets.defaultSettingsImageSupportText,
                multiSelect:false,
                anchor:'100%',
                removeBackground: true,
                validTypes:me.getAllowedExtensions(),
                validTypeErrorFunction:me.getExtensionErrorCallback(),
                translatable: true,
            },
            {
                xtype:'checkboxgroup',
                columns:2,
                defaultType:'checkboxfield',
                margin:'15 0 0 0',
                defaults:Ext.applyIf({
                    inputValue:true,
                    uncheckedValue:false
                }, me.defaults),
                items: me.getSettingsCheckboxes()
            },
            me.getShopSelector()
        ];
    },

    /**
     * Returns the shop selector
     *
     * @return { Shopware.form.field.ShopGrid }
     */
    getShopSelector: function () {
        var selectionFactory = Ext.create('Shopware.attribute.SelectionFactory');

        this.on('recordloaded', function (record) {
            this.shopSelector.setDisabled(!record.get('blog'));
        });

        return this.shopSelector = Ext.create('Shopware.apps.Category.view.fields.ShopGrid', {
            name: 'shops',
            fieldLabel: '{s name=label_shop}Limit to shop(s){/s}',
            helpText: '{s name=shop_helper}Limit page visibility to the following shops. If left empty, page will be accessible in all shops.{/s}',
            editable: false,
            allowSorting: false,
            height: 130,
            labelWidth: 155,
            store: selectionFactory.createEntitySearchStore('Shopware\\Models\\Shop\\Shop'),
            searchStore: selectionFactory.createEntitySearchStore('Shopware\\Models\\Shop\\Shop'),
        });
    },

    /**
     * Returns the category settings checkboxes for the default fieldset
     *
     * @return { Array }
     */
    getSettingsCheckboxes : function()
    {
        var me = this;
        return [
            {
                boxLabel:me.snippets.defaultSettingsHideTopLabel,
                name:'hideTop',
                dataIndex:'hideTop'
            }
        ];
    },

    /**
     * Builds and returns the CMS settings. A category can be set as blog article and the data can be defined here.
     * A blog entry contains the
     * - blog flag
     * - a headline and
     * - the text.
     *
     * @return Ext.form.FieldSet
     */
    getCmsSettings : function()
    {
        var me = this;
        return Ext.create('Ext.form.FieldSet',{
            title: me.snippets.cmsTitle,
            anchor: '100%',
            defaults : me.defaults,
            disabled : true,
            items : [
                {
                    xtype : 'textfield',
                    fieldLabel : me.snippets.cmsHeaderLabel,
                    name : 'cmsHeadline',
                    translatable: true,
                    translationName: 'cmsheadline'
                } , {
                    xtype : 'tinymce',
                    fieldLabel : me.snippets.cmsTextLabel,
                    height: 100,
                    name : 'cmsText',
                    translatable: true,
                    translationName: 'cmstext'
                }
            ]
        });
    },

    /**
     * Builds and returns the meta data section.
     * Fields
     *  - Meta Descriptions
     *  - Meta Keywords
     *
     * @return Ext.form.FieldSet
     */
    getMetaInfo : function()
    {
        var me = this;
        return Ext.create('Ext.form.FieldSet',{
            title: me.snippets.metaGroupTitle,
            anchor: '100%',
            defaults : me.defaults,
            disabled : true,
            items : [{
                xtype : 'textfield',
                fieldLabel  : me.snippets.metaTitle,
                name : 'metaTitle',
                translatable: true,
                translationName: 'metatitle'
            },{
                xtype : 'textareafield',
                fieldLabel  : me.snippets.metaDescription,
                name : 'metaDescription',
                translatable: true,
                translationName: 'metadescription'
            }, {
                xtype : 'textfield',
                fieldLabel : me.snippets.metaKeywords,
                name : 'metaKeywords',
                translatable: true,
                translationName: 'metakeywords'
            }
            ]
        });
    },

    /**
     * Builds and returns the six free attributes each category may have.
     * Each of this six fields can store up to 255 chars.
     *
     * @return Ext.form.FieldSet
     */
    getAttributes : function() {
        var me = this;

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: 's_categories_attributes',
            fieldSetPadding: 0
        });
        return me.attributeForm;
    },

    /**
     * Helper Method which returns the method which should be called if some selected image file has a wrong extension.
     *
     * @return string
     */
    getExtensionErrorCallback :  function() {
        return 'onExtensionError';
    },

    /**
     * Helper method to show an error if the user selected an wrong file type
     */
    onExtensionError : function() {
        var me = this;
        Shopware.Notification.createGrowlMessage(me.snippets.errorMessageWrongFileTypeTitle, me.snippets.errorMessageWrongFileType, me.snippets.growlMessage);
    },

    /**
     * Helper method to set the allowed file extension for the media manager
     *
     * @return array of strings
     */
    getAllowedExtensions : function() {
        return [ 'gif', 'png', 'jpeg', 'jpg', 'svg' ]
    }
});
//{/block}
