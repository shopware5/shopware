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
 * @package    UserManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/view/detail}

/**
 * Shopware UI - Media Manager Main Window
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/view/detail/settings"}
Ext.define('Shopware.apps.Emotion.view.detail.Settings', {
	extend: 'Ext.form.Panel',
    title: '{s name=title/settings_tab}Settings{/s}',
    alias: 'widget.emotion-detail-settings',
    bodyPadding: 20,
    border: 0,
    bodyBorder: 0,
    autoScroll: true,

    // Default settings for all underlying items
    defaults: {
        labelWidth: 200,
        anchor: '100%'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.categoryPathStore = Ext.create('Shopware.apps.Emotion.store.CategoryPath');
        me.categoryPathStore.getProxy().extraParams.parents = true;
        me.categoryPathStore.load();

        var gridStore = Ext.create('Ext.data.Store', {
            fields: [ 'display', 'value' ],
            data: [{
                display: '{s name=settings/one_column}1 column{/s}', value: 1
            }, {
                display: '{s name=settings/two_columns}2 columns{/s}', value: 2
            }, {
                display: '{s name=settings/three_columns}3 columns{/s}', value: 3
            }, {
                display: '{s name=settings/four_columns}4 columns{/s}', value: 4
            }]
        });

        me.nameField = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=settings/emotion_name_field}Emotion name{/s}',
            emptyText: '{s name=settings/emotion_name_empty}My new emotion{/s}',
            name: 'name'
        });

        me.landingPageCheckbox = Ext.create('Ext.form.field.Checkbox', {
            fieldLabel: '{s name=settings/landingpage_checkbox}Landingpage{/s}',
            boxLabel: '{s name=settings/landingpage_box_label}This emotion is a landing page{/s}',
            name: 'isLandingPage',
            inputValue: true,
            uncheckedValue: false,
            listeners: {
                scope: me,
                change: function(field, value) {
                    if(value) {
                        me.categoryNameField.hide().setDisabled(true);
                        me.listingCheckbox.hide();
                        me.landingPageFieldSet.show();
                    } else {
                        me.categoryNameField.show().setDisabled(false);
                        me.landingPageFieldSet.hide();
                        me.listingCheckbox.show();
                    }
                }
            }
        });

        me.categoryNameField = Ext.create('Shopware.form.field.PagingComboBox', {
            anchor: '100%',
            name: 'categoryId',
            emptyText: '{s name=settings/select_category_empty}Please select...{/s}',
            pageSize: 15,
            fieldLabel: '{s name=settings/select_category_field}Select a category{/s}',
            store: me.categoryPathStore ,
            valueField: 'id',
            displayField: 'name'
        });

        me.gridComboBox = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=settings/select_grid_field}Select a grid{/s}',
            name: 'cols',
            store: gridStore,
            displayField: 'display',
            valueField: 'value',
            emptyText: '{s name=settings/select_grid_empty}Please select...{/s}'
        });

        me.activeComboBox = Ext.create('Ext.form.field.Checkbox', {
            fieldLabel: '{s name=settings/active}Active{/s}',
            boxLabel: '{s name=settings/active_box_label}Emotion will be visible in the store front{/s}',
            name: 'active',
            inputValue: true,
            uncheckedValue:false
        });

        me.listingCheckbox = Ext.create('Ext.form.field.Checkbox', {
            fieldLabel: '{s name=settings/listing}Listing{/s}',
            boxLabel: '{s name=settings/listing_box_label}Listing will be visible under the emotion{/s}',
            name: 'showListing',
            inputValue: true,
            uncheckedValue: false
        });

        me.timingFieldSet =  me.createTimingFieldSet();
        me.landingPageFieldSet = me.createLandingpageFieldset();
        me.additionalFieldSet = me.createAdditionalSettingsFieldset();

        me.items = [ me.nameField, me.landingPageCheckbox, me.categoryNameField, me.gridComboBox, me.activeComboBox, me.listingCheckbox, me.timingFieldSet, me.landingPageFieldSet, me.additionalFieldSet ];
        me.callParent(arguments);

        me.loadRecord(me.emotion);
    },

    createTimingFieldSet: function() {
        var me = this;

        var validFrom = Ext.create('Ext.form.field.Date', {
            anchor: '100%',
            fieldLabel: '{s name=settings/time_control/start_date}Start date{/s}',
            name: 'validFrom'
        });

        var validTo = Ext.create('Ext.form.field.Date', {
            anchor: '100%',
            fieldLabel: '{s name=settings/time_control/end_date}End date{/s}',
            name: 'validTo'
        });

        var validFromTime = Ext.create('Ext.form.field.Time', {
            name: 'validFromTime',
            fieldLabel: '{s name=settings/time_control/start_time}Start time{/s}',
            increment: 30,
            anchor: '100%'
        });

        var validToTime = Ext.create('Ext.form.field.Time', {
            name: 'validToTime',
            fieldLabel: '{s name=settings/time_control/end_time}End time{/s}',
            increment: 30,
            anchor: '100%'
        })

        return {
            xtype: 'fieldset',
            title: '{s name=settings/time_control/title}Time-controlled activation{/s}',
            layout: 'column',
            items: [{
                xtype: 'container',
                columnWidth: 1,
                margin: '0 0 10',
                items: [{
                    xtype: 'button',
                    iconCls: 'sprite-clock--minus',
                    text: '{s name=settings/time_control/reset}Reset time-controlled activation{/s}',
                    handler: function() {
                        var fields = [ validFrom, validTo, validFromTime, validToTime ];

                        Ext.each(fields, function(field) {
                            field.setRawValue(null);
                        });
                    }
                }]
            },{
                xtype: 'container',
                columnWidth: .5,
                margin: '0 10 0 0',
                layout: 'anchor',
                items: [ validFrom, validTo ]
            }, {
                xtype: 'container',
                columnWidth: .5,
                layout: 'anchor',
                margin: '0 0 0 10',
                items: [ validFromTime, validToTime ]
            }]
        };
    },

    createLandingpageFieldset: function() {
        var me = this, fieldset;

        var displayField = Ext.create('Ext.form.field.Display', {
            name: 'link',
            fieldLabel: '{s name=settings/link_action}Link to the landingpage{/s}'
        });

        var mediaSelection = Ext.create('Shopware.MediaManager.MediaSelection', {
            anchor: '100%',
            fieldLabel: '{s name=settings/teaser_image}Teaser image{/s}',
            name: 'landingPageTeaser'
        });

        var seoKeywords = Ext.create('Ext.form.field.Text', {
            name: 'seoKeywords',
            fieldLabel: '{s name=settings/seo_keywords}SEO-Keywords{/s}'
        });

        var seoDescription = Ext.create('Ext.form.field.TextArea', {
            maxLength:150,
            name: 'seoDescription',
            fieldLabel: '{s name=settings/seo_description}SEO-Description{/s}'
        });

        var returnCats = [];
        if(me.emotion.get('categories') && !Ext.isObject(me.emotion.get('categories'))) {
            var categories =  me.emotion.get('categories');

            Ext.each(categories, function(category) {
                returnCats.push(category.id);
            });
            me.emotion.set('categories', returnCats);
        }

        me.categorySearchField = Ext.create('Ext.ux.form.field.BoxSelect', {
            anchor: '100%',
            width: '100%',
            name: 'categories',
            fieldLabel: '{s name=settings/select_categories_field}Select categorie(s){/s}',
            store: me.categoryPathStore,
            valueField: 'id',
            displayField: 'name',
            value: returnCats
        });

        var store = Ext.create('Ext.data.Store', {
            fields: ['display', 'value'],
            data: [{
                display: '{s name=position/lefttop}Left top{/s}',
                value: 'leftTop'
            },{
                display: '{s name=position/leftmiddle}Left middle{/s}',
                value: 'leftMiddle'
            }, {
                display: '{s name=position/leftbottom}Left bottom{/s}',
                value: 'leftBottom'
            }]
        });

        me.positionSelection = Ext.create('Ext.form.field.ComboBox', {
            queryMode: 'local',
            fieldLabel: '{s name=settings/select_position}Select position{/s}',
            store: store,
            displayField: 'display',
            valueField: 'value',
            name: 'landingPageBlock'
        });

        fieldset = Ext.create('Ext.form.FieldSet', {
            margin: '15 0 0',
            title: '{s name=settings/landingpage_settings}Landingpage settings{/s}',
            layout: 'anchor',
            collapsible: true,
            hidden: true,
            defaults: me.defaults,
            items: [ displayField, mediaSelection, seoKeywords, seoDescription, me.categorySearchField, me.positionSelection ]
        });

        return fieldset;
    },

    createAdditionalSettingsFieldset: function() {
        var me = this, fieldset;

        var noticeContainer = Ext.create('Ext.container.Container', {
            margin: '0 0 15',
            html: '{s name=settings/fieldset/notice_container}In the advanced settings you have the option to specify your emotion individually to your shop. Please note that some settings can affect your storefront negatively or even destroy it completely.{/s}',
            style: 'font-style: italic; color: #999'
        });

        var boxHeightField = Ext.create('Ext.form.field.Number', {
            fieldLabel: '{s name=settings/fieldset/cell_height}Cell height{/s}',
            name: 'cellHeight',
            supportText: '{s name=settings/fieldset/cell_height_info}Needed for calculating the complete height{/s}'
        });

        var containerWidthField = Ext.create('Ext.form.field.Number', {
            fieldLabel: '{s name=settings/fieldset/container_width}Container width{/s}',
            name: 'containerWidth',
            supportText: '{s name=settings/fieldset/container_width_info}Container width in pixel (px){/s}'
        });

        var articleBoxHeight = Ext.create('Ext.form.field.Number', {
            name: 'articleHeight',
            fieldLabel: '{s name=settings/fieldset/article_height}Height of an article box{/s}'
        });

        var tplComboBox = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=settings/fieldset/select_template}Select Template{/s}',
            name: 'tempalte',
            value: 'Standard',
            emptyText: '{s name=settings/fieldset/select_template_empty}Please select...{/s}'
        });

        var actionToolbar = Ext.create('Ext.toolbar.Toolbar', {
            unstyled: true,
            ui: 'shopware-ui',
            style: 'background: #fff; border: 0 none',
            items: ['->', {
                xtype: 'button',
                text: '{s name=settings/fieldset/create_grid}Create a new grid{/s}',
                disabled: true,
                iconCls: 'sprite-grid',
                action: 'emotion-detail-settings-create-new-grid'
            }]
        });

        fieldset = Ext.create('Ext.form.FieldSet', {
            title: '{s name=settings/fieldset/advanced_settings}Advanced settings{/s}',
            margin: '15 0 0',
            layout: 'anchor',
            collapsible: true,
            collapsed: true,
            defaults: me.defaults,
            items: [ noticeContainer, boxHeightField, articleBoxHeight, containerWidthField, tplComboBox, actionToolbar ]
        });

        return fieldset;
    }
});
//{/block}