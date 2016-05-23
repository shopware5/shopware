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
 * @category    Shopware
 * @package     Emotion
 * @subpackage  View
 * @version     $Id$
 * @author      shopware AG
 */

//{namespace name=backend/emotion/view/detail}
//{block name="backend/emotion/view/detail/settings"}
Ext.define('Shopware.apps.Emotion.view.detail.Settings', {

    extend: 'Ext.form.Panel',
    alias: 'widget.emotion-detail-settings',

    title: '{s name="title/settings_tab"}Settings{/s}',

    bodyPadding: 20,
    cls: 'shopware-form',
    border: 0,
    bodyBorder: 0,
    autoScroll: true,

    defaults: {
        labelWidth: 120,
        anchor: '100%'
    },

    snippets: {
        fieldSets: {
            basicSettingsLabel: '{s name="settings/basicFieldset/title"}{/s}',
            displaySettingsLabel: '{s name="settings/displayFielset/title"}{/s}',
            landingPageSettingsLabel: '{s name="settings/landingpage_settings"}{/s}',
            timeSettingsLabel: '{s name="settings/timeFieldset/title"}{/s}'
        },
        fields: {
            nameLabel: '{s name="settings/emotion_name_field"}{/s}',
            namePlaceholder: '{s name=settings/emotion_name_empty}{/s}',
            activeLabel: '{s name="settings/active"}{/s}',
            landingPageLabel: '{s name="settings/landingpage_checkbox"}{/s}',
            categoryPlaceholder: '{s name="settings/select_categories_field"}{/s}',
            productsListingLabel: '{s name="settings/productListingLabel"}{/s}',
            productsListingBoxLabel: '{s name="settings/productListingBoxLabel"}{/s}',
            positionLabel: '{s name="settings/fieldset/position_number"}{/s}',
            positionHelpText: '{s name="settings/fieldset/position_number_help"}{/s}',
            landingPageLinkLabel: '{s name="settings/link_action"}{/s}',
            landingPageTitleLabel: '{s name=settings/seo_title}{/s}',
            landingPageKeywordsLabel: '{s name=settings/seo_keywords}{/s}',
            landingPageDescLabel: '{s name=settings/seo_description}{/s}',
            landingPageParentLabel: '{s name=settings/master_landingpage}{/s}',
            timeStartDateLabel: '{s name=settings/time_control/start_date}{/s}',
            timeEndDateLabel: '{s name=settings/time_control/end_date}{/s}',
            timeStartTimeLabel: '{s name=settings/time_control/start_time}{/s}',
            timeEndTimeLabel: '{s name=settings/time_control/end_time}{/s}',
            timeResetBtnLabel: '{s name=settings/time_control/reset}{/s}',
            shopSelectionLabel: '{s name="settings/shop_selection"}{/s}'
        }
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.mainFieldset = me.createMainFieldset();
        me.generalFieldSet = me.createGeneralFieldSet();
        me.landingPageFieldSet = me.createLandingPageFieldset();
        me.timingFieldSet = me.createTimingFieldSet();

        me.items = [
            me.mainFieldset,
            me.generalFieldSet,
            me.landingPageFieldSet,
            me.timingFieldSet
        ];

        me.callParent(arguments);
    },

    createMainFieldset: function() {
        var me = this;

        me.nameField = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.fields.nameLabel,
            emptyText: me.snippets.fields.namePlaceholder,
            anchor: '100%',
            name: 'name',
            allowBlank: false,
            labelWidth: me.defaults.labelWidth,
            translatable: true
        });

        me.positionNumberField = Ext.create('Ext.form.field.Number', {
            fieldLabel: me.snippets.fields.positionLabel,
            helpText: me.snippets.fields.positionHelpText,
            name: 'position',
            minValue: 1,
            value: 1,
            anchor: '100%',
            width: '100%',
            labelWidth: me.defaults.labelWidth
        });

        me.activeComboBox = Ext.create('Ext.form.field.Checkbox', {
            boxLabel: me.snippets.fields.activeLabel,
            name: 'active',
            inputValue: true,
            uncheckedValue: false,
            hideEmptyLabel: false,
            margin: '10 0 5 0',
            labelWidth: me.defaults.labelWidth
        });

        me.landingPageCheckbox = Ext.create('Ext.form.field.Checkbox', {
            boxLabel: me.snippets.fields.landingPageLabel,
            name: 'isLandingPage',
            inputValue: true,
            uncheckedValue: false,
            hideEmptyLabel: false,
            labelWidth: me.defaults.labelWidth,
            listeners: {
                scope: me,
                change: function(field, value) {
                    if(value) {
                        me.generalFieldSet.hide();
                        me.landingPageFieldSet.show();
                    } else {
                        me.landingPageFieldSet.hide();
                        me.generalFieldSet.show();
                    }
                }
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.fieldSets.basicSettingsLabel,
            defaults: me.defaults,
            items: [
                me.nameField,
                me.positionNumberField,
                me.activeComboBox,
                me.landingPageCheckbox
            ]
        });
    },

    createGeneralFieldSet: function() {
        var me = this;

        me.categories = Ext.create('Ext.ux.form.field.BoxSelect', {
            name: 'categories',
            emptyText: me.snippets.fields.categoryPlaceholder,
            store: me.categoryStore,
            valueField: 'id',
            displayField: 'name',
            width: '100%',
            margin: '10 0',
            labelWidth: me.defaults.labelWidth
        });

        me.listingCheckbox = Ext.create('Ext.form.field.Checkbox', {
            fieldLabel: me.snippets.fields.productsListingLabel,
            boxLabel: me.snippets.fields.productsListingBoxLabel,
            name: 'showListing',
            inputValue: true,
            uncheckedValue: false,
            labelWidth: me.defaults.labelWidth
        });

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.fieldSets.displaySettingsLabel,
            defaults: me.defaults,
            items: [
                me.categories,
                me.listingCheckbox
            ]
        });
    },

    createLandingPageFieldset: function() {
        var me = this;

        me.landingPageFields = {};

        me.landingPageFields.shopsField = Ext.create('Ext.ux.form.field.BoxSelect', {
            name: 'shops',
            emptyText: me.snippets.fields.shopSelectionLabel,
            store: me.shopStore,
            valueField: 'id',
            displayField: 'name',
            width: '100%',
            margin: '10 3 10 0',
            labelWidth: me.defaults.labelWidth
        });

        me.landingPageFields.displayField = Ext.create('Ext.form.field.Text', {
            name: 'link',
            readOnly: true,
            fieldLabel: me.snippets.fields.landingPageLinkLabel,
            labelWidth: me.defaults.labelWidth
        });

        me.landingPageFields.seoTitle = Ext.create('Ext.form.field.Text', {
            name: 'seoTitle',
            fieldLabel: me.snippets.fields.landingPageTitleLabel,
            labelWidth: me.defaults.labelWidth,
            translatable: true
        });

        me.landingPageFields.seoKeywords = Ext.create('Ext.form.field.Text', {
            name: 'seoKeywords',
            fieldLabel: me.snippets.fields.landingPageKeywordsLabel,
            labelWidth: me.defaults.labelWidth,
            translatable: true
        });

        me.landingPageFields.seoDescription = Ext.create('Ext.form.field.TextArea', {
            maxLength: 150,
            name: 'seoDescription',
            fieldLabel: me.snippets.fields.landingPageDescLabel,
            labelWidth: me.defaults.labelWidth,
            translatable: true
        });

        me.landingPageFields.configuration = Ext.create('Ext.container.Container', {
            margin: '10 0 0',
            layout: 'anchor',
            defaults: me.defaults,
            items: [
                me.landingPageFields.displayField,
                me.landingPageFields.shopsField,
                me.landingPageFields.seoTitle,
                me.landingPageFields.seoKeywords,
                me.landingPageFields.seoDescription
            ]
        });

        me.landingPageFields.parentStore = Ext.create('Shopware.apps.Emotion.store.LandingPage');
        me.landingPageFields.parentStore.getProxy().extraParams.ownId = me.emotion.get('id');

        if (me.emotion.get('parentId')) {
            me.landingPageFields.parentStore.load({ params: { id: me.emotion.get('parentId') } });
        }

        me.landingPageFields.parentLandingPage = Ext.create('Ext.form.field.ComboBox', {
            queryMode: 'remote',
            store: me.landingPageFields.parentStore,
            fieldLabel: me.snippets.fields.landingPageParentLabel,
            displayField: 'name',
            valueField: 'id',
            name: 'parentId',
            allowBlank: true,
            labelWidth: me.defaults.labelWidth,
            listeners: {
                change: function(field, newValue) {
                    if (newValue) {
                        me.landingPageFields.configuration.hide();
                    } else {
                        this.setValue('');
                        me.landingPageFields.configuration.show();
                    }
                }
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.fieldSets.landingPageSettingsLabel,
            hidden: true,
            defaults: me.defaults,
            items: [
                me.landingPageFields.parentLandingPage,
                me.landingPageFields.configuration
            ]
        });
    },

    createTimingFieldSet: function() {
        var me = this;

        me.timeFields = {};

        me.timeFields.validFrom = Ext.create('Ext.form.field.Date', {
            fieldLabel: me.snippets.fields.timeStartDateLabel,
            anchor: '100%',
            submitFormat: 'd.m.Y',
            name: 'validFrom',
            margin: '20 3 5 0',
            labelWidth: me.defaults.labelWidth
        });

        me.timeFields.validTo = Ext.create('Ext.form.field.Date', {
            fieldLabel: me.snippets.fields.timeEndDateLabel,
            anchor: '100%',
            submitFormat: 'd.m.Y',
            name: 'validTo',
            margin: '20 3 5 0',
            labelWidth: me.defaults.labelWidth
        });

        me.timeFields.validFromTime = Ext.create('Ext.form.field.Time', {
            fieldLabel: me.snippets.fields.timeStartTimeLabel,
            name: 'validFromTime',
            increment: 30,
            validationEvent: false,
            submitFormat: 'H:i',
            anchor: '100%',
            labelWidth: me.defaults.labelWidth
        });

        me.timeFields.validToTime = Ext.create('Ext.form.field.Time', {
            fieldLabel: me.snippets.fields.timeEndTimeLabel,
            name: 'validToTime',
            increment: 30,
            submitFormat: 'H:i',
            anchor: '100%',
            labelWidth: me.defaults.labelWidth
        });

        me.timeFields.resetBtn = Ext.create('Ext.Button', {
            iconCls: 'sprite-clock--minus',
            text: me.snippets.fields.timeResetBtnLabel,
            maxWidth: 240,
            handler: function() {
                var fields = [
                    me.timeFields.validFrom,
                    me.timeFields.validTo,
                    me.timeFields.validFromTime,
                    me.timeFields.validToTime
                ];

                Ext.each(fields, function(field) {
                    field.setRawValue(null);
                });
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            title: me.snippets.fieldSets.timeSettingsLabel,
            defaults: me.defaults,
            items: [
                me.timeFields.resetBtn,
                me.timeFields.validFrom,
                me.timeFields.validFromTime,
                me.timeFields.validTo,
                me.timeFields.validToTime
            ]
        });
    }
});
//{/block}
