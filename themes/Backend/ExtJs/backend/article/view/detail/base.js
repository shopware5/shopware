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
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page
 * The base component contains the configuration elements for the article head data, like the article name,
 * article order number, etc.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/detail/base"}
Ext.define('Shopware.apps.Article.view.detail.Base', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend:'Ext.form.FieldSet',

    /**
     * The Ext.container.Container.layout for the fieldset's immediate child items.
     * @object
     */
    layout: 'column',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-base-field-set',

    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-base-field-set',

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        titleEdit: '{s name=detail/base/title_edit}Head data - last edit [0]{/s}',
        titleNew: '{s name=detail/base/title_new}Head data{/s}',
        empty: '{s name=empty}Please select...{/s}',
        name: '{s name=detail/base/description}Article description{/s}',
        supplier: '{s name=detail/base/supplier}Supplier{/s}',
        number: '{s name=detail/base/number}Article number{/s}',
        active: '{s name=detail/base/active}Active{/s}',
        configurator: {
            fieldLabel: '{s name=detail/base/configurator_field}Configurator{/s}'
        },
        tax: '{s name=detail/base/tax}Tax{/s}',
        template: '{s name=detail/base/template}Template{/s}',
        priceGroupActive: '{s name=detail/base/price_group_active}Active price group{/s}',
        priceGroup: '{s name=detail/base/price_group_select}Select price group{/s}',
        purchasePrice: '{s name=detail/base/purchase_price}Purchase price{/s}',
        numberValidation: '{s name=detail/base/number_validation}The inserted article number already exists!{/s}',
        mainDetailAdditionalText: '{s name=detail/base/main_detail_additional_text}Varianten-Zusatztext{/s}',
        regexNumberValidation: '{s name=detail/base/regex_number_validation}The inserted article number contains illegal characters!{/s}'
    },

    /**
     * Contains the field set defaults.
     */
    defaults: {
        labelWidth: 155
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
    initComponent:function () {
        var me = this,
            mainWindow = me.subApp.articleWindow;

        mainWindow.on('storesLoaded', me.onStoresLoaded, me);

        me.title = me.snippets.titleNew;
        me.items = me.createElements();
        me.callParent(arguments);
    },

    /**
     * The setTitle function checks if the detail window opened with an article record or not.
     * If an article passed, the last change date will be displayed in the field set header.
     */
    changeTitle: function() {
        var me = this;

        if (me.article instanceof Ext.data.Model) {
            var changed = Ext.util.Format.date(me.article.get('changed')) + ' ' + Ext.util.Format.date(me.article.get('changed'), 'H:i:s');
            me.title = Ext.String.format(me.snippets.titleEdit, changed);
        } else {
            me.title = me.snippets.titleNew;
        }
        me.setTitle(me.title);
    },

    /**
     * Creates the both containers for the field set
     * to display the form fields in two columns.
     *
     * @return Ext.container.Container[] Contains the left and right container
     */
    createElements:function () {
        var leftContainer, rightContainer, me = this;

        leftContainer = Ext.create('Ext.container.Container', {
            columnWidth:0.5,
            defaults: {
                labelWidth: 155,
                anchor: '100%'
            },
            padding: '0 20 0 0',
            layout: 'anchor',
            border:false,
            items:me.createLeftElements()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            columnWidth:0.5,
            layout: 'anchor',
            defaults: {
                labelWidth: 155,
                anchor: '100%'
            },
            border:false,
            items:me.createRightElements()
        });

        return [
            leftContainer,
            rightContainer
        ] ;
    },

    /**
     * Creates the field set items which displayed in the left column of the base field set
     *
     * @return Array
     */
    createLeftElements: function() {
        var me = this, articleId = null, additionalText = null;

        if (me.article instanceof Ext.data.Model && me.article.getMainDetail().first() instanceof Ext.data.Model) {
            articleId = me.article.getMainDetail().first().get('id');
            additionalText = me.article.getMainDetail().first().get('additionalText');
        }

        me.numberField = Ext.create('Ext.form.field.Text', {
            name: 'mainDetail[number]',
            dataIndex: 'mainDetail[number]',
            fieldLabel: me.snippets.number,
            regex: new RegExp({$orderNumberRegex}),
            regexText: me.snippets.regexNumberValidation,
            allowBlank: false,
            enableKeyEvents: true,
            checkChangeBuffer: 700,
            labelWidth: 155,
            anchor: '100%',
            vtype: 'remote',
            validationUrl: '{url action="validateNumber"}',
            validationRequestParam: articleId,
            validationErrorMsg: me.snippets.numberValidation
        });

        var hideVariantTab = true;
        if(me.article !== Ext.undefined) {
            hideVariantTab = (me.article.get('id') === null || me.article.get('isConfigurator') === false || me.article.get('configuratorSetId') === null);
        }

        var showAdditionalText = (hideVariantTab) ? !Ext.isEmpty(additionalText, false) : false;
        me.mainDetailAdditionalText = Ext.create('Ext.form.field.Text', {
            name: 'mainDetail[additionalText]',
            translatable: false,
            labelWidth: 155,
            anchor: '100%',
            hidden: !showAdditionalText,
            fieldLabel: me.snippets.mainDetailAdditionalText
        });

        me.supplierCombo = Ext.create('Ext.form.field.ComboBox', {
            xtype: 'combo',
            name: 'supplierId',
            queryMode: 'local',
            store: me.supplierStore,
            valueField: 'id',
            displayField: 'name',
            emptyText: me.snippets.empty,
            allowBlank: false,
            fieldLabel: me.snippets.supplier,
            labelWidth: 155,
            anchor: '100%'
        });

        return [
            me.supplierCombo,
            {
                xtype: 'textfield',
                name: 'name',
                translatable: true,
                allowBlank: false,
                fieldLabel: me.snippets.name
            },
            me.mainDetailAdditionalText,
            me.numberField,
            {
                xtype: 'checkbox',
                name: 'active',
                fieldLabel: me.snippets.active,
                inputValue: true,
                uncheckedValue:false
            },
            {
                xtype: 'checkbox',
                name: 'isConfigurator',
                fieldLabel: me.snippets.configurator.fieldLabel,
                inputValue: true,
                uncheckedValue:false
            }
        ];
    },

    onStoresLoaded: function(article, stores) {
        var me = this;

        // Change the title
        me.article = article;
        me.changeTitle();

        // Bind the stores on the left side
        me.supplierCombo.bindStore(stores['suppliers']);

        // Bind the stores to the comboboxes on the right side
        me.taxComboBox.bindStore(stores['taxes']);
        me.templateComboBox.bindStore(stores['templates']);
        me.priceGroupComboBox.bindStore(stores['priceGroups']);

        me.numberField.validationRequestParam = article.getMainDetail().first().get('id');
    },

    /**
     * Creates the field set items which displayed in the right column of the base field set
     * @return Array
     */
    createRightElements: function() {
        var me = this;

        me.taxComboBox = Ext.create('Ext.form.field.ComboBox', {
            name: 'taxId',
            queryMode: 'local',
            emptyText: me.snippets.empty,
            fieldLabel: me.snippets.tax,
            allowBlank: false,
            valueField: 'id',
            displayField: 'name',
            editable: false,
            labelWidth: 155,
            anchor: '100%'
        });

        me.templateComboBox = Ext.create('Ext.form.field.ComboBox', {
            name: 'template',
            queryMode: 'local',
            fieldLabel: me.snippets.template,
            emptyText: me.snippets.empty,
            valueField: 'id',
            displayField: 'name',
            editable: false,
            labelWidth: 155,
            anchor: '100%'
        });

        me.priceGroupComboBox = Ext.create('Ext.form.field.ComboBox', {
            queryMode: 'local',
            name: 'priceGroupId',
            fieldLabel: me.snippets.priceGroup,
            emptyText: me.snippets.empty,
            valueField: 'id',
            displayField: 'name',
            editable: false,
            labelWidth: 155,
            anchor: '100%'
        });

        return [
            me.taxComboBox, me.templateComboBox, {
            xtype: 'checkbox',
            name: 'priceGroupActive',
            fieldLabel: me.snippets.priceGroupActive,
            inputValue: true,
            uncheckedValue:false,
            listeners: {
                change: function(checkbox, newValue) {
                    me.priceGroupComboBox.allowBlank = !newValue;
                    me.priceGroupComboBox.validateValue(me.priceGroupComboBox.getValue());
                }
            }
        }, me.priceGroupComboBox, {
            xtype: 'numberfield',
            name: 'mainDetail[purchasePrice]',
            dataIndex: 'mainDetail[purchasePrice]',
            fieldLabel: me.snippets.purchasePrice,
            minValue: 0,
            step: 0.01
        }]
    }
});
//{/block}
