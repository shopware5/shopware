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
 * The settings component contains the settings elements for the article, like delivery time, supplier number,
 * the article stock, etc.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/detail/settings"}
Ext.define('Shopware.apps.Article.view.detail.Settings', {
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
    alias:'widget.article-settings-field-set',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-settings-field-set',
    /**
     * Contains the field set defaults.
     */
    defaults: {
        labelWidth: 155
    },
    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title:'{s name=detail/settings/title}Settings{/s}',
        supplierNumber:'{s name=detail/settings/supplier_number}Supplier number{/s}',
        avoidCustomerGroups: {
            label: '{s name=detail/settings/avoid_customer_groups_label}Avoid customer groups{/s}',
            support: '{s name=detail/settings/avoid_customer_groups_support}Here you have the opportunity to deactivate the article for different customer groups.{/s}'
        },
        emailNotification: {
            field: '{s name=detail/settings/email_notification_field}Email notification{/s}',
            box: '{s name=detail/settings/email_notification_box}Show notification feature{/s}'
        },
        deliveryTime: '{s name=detail/settings/delivery_time}Delivery time (days){/s}',
        stock: '{s name=detail/settings/stock}Stock{/s}',
        minStock: '{s name=detail/settings/min_stock}Minimum storage inventory{/s}',
        releaseDate: '{s name=detail/settings/release_date}Release date{/s}',
        createdAt: '{s name=detail/settings/created_at}Date of creation{/s}',
        pseudoSales: '{s name=detail/settings/pseudo_sales}Pseudo sales{/s}',
        weight: '{s name=detail/settings/weight_bw}Weight (bw){/s}',
        shippingFree: {
            field: '{s name=detail/settings/shipping_free_field}Free shipping{/s}',
            box: '{s name=detail/settings/shipping_free_box}Select article as free shipping{/s}'
        },
        highlight: {
            field: '{s name=detail/settings/highlight_field}Highlight article{/s}',
            box: '{s name=detail/settings/highlight_box}Highlight article in shop{/s}'
        },
        onSale: {
            field: '{s name=detail/settings/on_sale_field}On sale{/s}',
            box: '{s name=detail/settings/on_sale_box}If instock <= 0, the article is not available{/s}'
        },
        minimumOrder: '{s name=detail/settings/minimum_order}Minimum order{/s}',
        graduation: '{s name=detail/settings/graduation}Graduation{/s}',
        maximumOrder: '{s name=detail/settings/maximum_order}Maximum order{/s}',
        ean: '{s name=detail/settings/ean}EAN{/s}',
        width: '{s name=detail/settings/width}Width{/s}',
        height: '{s name=detail/settings/height}Height{/s}',
        len: '{s name=detail/settings/length}Length{/s}'
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

        me.title = me.snippets.title;
        me.items = me.createElements();
        me.callParent(arguments);
    },

    /**
     * Creates the both containers for the field set
     * to display the form fields in two columns.
     *
     * @return array Contains the left and right container
     */
    createElements:function () {
        var leftContainer, rightContainer, me = this, bottomContainer;

        bottomContainer = Ext.create('Ext.container.Container', {
            columnWidth:1,
            defaults: {
                labelWidth: 155,
                anchor: '100%'
            },
            layout: 'anchor',
            border:false,
            items: me.createBottomElements()
        });

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

        return [ leftContainer, rightContainer, bottomContainer ] ;
    },

    createBottomElements: function() {
        var me = this;
        me.avoidCustomerGroupsCombo = Ext.create('Ext.ux.form.field.BoxSelect', {
            forceSelection: true,
            delimiter: ', ',
            displayField: 'name',
            valueField: 'id',
            queryMode: 'local',
            name: 'avoidCustomerGroups',
            labelWidth: 155,
            width: '100%',
            anchor: '100%',
            fieldLabel: me.snippets.avoidCustomerGroups.label,
            supportText: me.snippets.avoidCustomerGroups.support
        });
        return [ me.avoidCustomerGroupsCombo ]
    },

    /**
     * Creates the field set items which displayed in the left column of the settings field set
     * @return Array
     */
    createLeftElements: function() {
        var me =this;

        return [
            {
                xtype: 'checkboxfield',
                name: 'notification',
                fieldLabel: me.snippets.emailNotification.field,
                boxLabel: me.snippets.emailNotification.box,
                inputValue: true,
                uncheckedValue: false
            }, {
                xtype: 'textfield',
                name: 'mainDetail[shippingTime]',
                translationName: 'shippingTime',
                translatable: true,
                fieldLabel: me.snippets.deliveryTime
            }, {
                xtype: 'numberfield',
                name: 'mainDetail[inStock]',
                decimalPrecision: 0,
                fieldLabel: me.snippets.stock
            }, {
                xtype: 'numberfield',
                name: 'mainDetail[stockMin]',
                decimalPrecision: 0,
                fieldLabel: me.snippets.minStock
            }, {
                xtype: 'datefield',
                name: 'mainDetail[releaseDate]',
                submitFormat: 'd.m.Y',
                fieldLabel: me.snippets.releaseDate
            }, {
                xtype: 'datefield',
                name: 'added',
                submitFormat: 'd.m.Y',
                fieldLabel: me.snippets.createdAt
            }, {
                xtype: 'numberfield',
                name: 'pseudoSales',
                fieldLabel: me.snippets.pseudoSales
            } , {
               xtype: 'numberfield',
               name: 'mainDetail[minPurchase]',
               decimalPrecision: 0,
               minValue: 1,
               value: 1,
               fieldLabel: me.snippets.minimumOrder
           }, {
               xtype: 'numberfield',
               name: 'mainDetail[purchaseSteps]',
               decimalPrecision: 0,
               fieldLabel: me.snippets.graduation
           }, {
               xtype: 'numberfield',
               name: 'mainDetail[maxPurchase]',
               decimalPrecision: 0,
               fieldLabel: me.snippets.maximumOrder
           }
        ];
    },

    /**
     * Creates the field set items which displayed in the right column of the settings field set
     * @return Array
     */
    createRightElements: function() {
        var me = this;

        return [
            {
                xtype: 'textfield',
                name: 'mainDetail[supplierNumber]',
                fieldLabel: me.snippets.supplierNumber
            }, {
                xtype: 'numberfield',
                name: 'mainDetail[weight]',
                decimalPrecision: 3,
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.weight
            }, {
                xtype: 'checkboxfield',
                name: 'mainDetail[shippingFree]',
                fieldLabel: me.snippets.shippingFree.field,
                boxLabel: me.snippets.shippingFree.box,
                inputValue: true,
                uncheckedValue:false
            }, {
                xtype: 'checkboxfield',
                name: 'highlight',
                fieldLabel: me.snippets.highlight.field,
                boxLabel: me.snippets.highlight.box,
                inputValue: true,
                uncheckedValue:false
            }, {
                xtype: 'checkboxfield',
                name: 'lastStock',
                fieldLabel: me.snippets.onSale.field,
                boxLabel: me.snippets.onSale.box,
                inputValue: true,
                uncheckedValue:false
            }, {
                xtype: 'textfield',
                name: 'mainDetail[ean]',
                fieldLabel: me.snippets.ean
            }, {
                xtype: 'numberfield',
                name: 'mainDetail[width]',
                decimalPrecision: 3,
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.width
            }, {
                xtype: 'numberfield',
                name: 'mainDetail[height]',
                decimalPrecision: 3,
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.height
            }, {
                xtype: 'numberfield',
                name: 'mainDetail[len]',
                decimalPrecision: 3,
                submitLocaleSeparator: false,
                fieldLabel: me.snippets.len
            }

        ];
    },

    onStoresLoaded: function(article, stores) {
        var me = this;

        me.customerGroupStore = stores['customerGroups'];
        me.avoidCustomerGroupsCombo.bindStore(me.customerGroupStore);
    }
});
//{/block}
