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
 * @package    Blog
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/blog/view/blog}
/**
 * Shopware UI - Blog detail main window.
 *
 * Displays all Detail Blog Information
 */
//{block name="backend/blog/view/blog/detail"}
Ext.define('Shopware.apps.Blog.view.blog.detail.Main', {
    extend: 'Ext.container.Container',
    alias: 'widget.blog-blog-detail-main',
    border: 0,
    bodyPadding: 10,
    layout: {
        align: 'stretch',
        type: 'vbox'
    },

    /**
     * Initialize the Shopware.apps.Blog.view.blog.detail.mail and defines the necessary
     * default configuration
     */
    initComponent: function () {
        this.generalFieldset = Ext.create('Ext.panel.Panel', {
            title: '{s name=detail/main/field_set/general_data}General data{/s}',
            margin: '0 0 10 0',
            layout: {
                type: 'anchor'
            },
            bodyPadding: 10,
            flex: 5,
            closable: false,
            collapsible: true,
            autoScroll: true,
            defaults: {
                minWidth: 250,
                anchor: '100%',
                xtype: 'textfield',
                labelWidth: 155
            },
            items: this.createGeneralForm(),
        });

        this.contentFieldset = Ext.create('Ext.panel.Panel', {
            title: '{s name=detail/main/field_set/content_data}Content{/s}',
            layout: {
                type: 'anchor'
            },
            flex: 7,
            autoScroll: true,
            bodyPadding: 10,
            defaults: {
                anchor: '100%'
            },
            items: this.createContentForm()
        });

        this.items = [this.generalFieldset, this.contentFieldset];

        this.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents: function () {
        this.addEvents(
            /**
             * Event will be fired when the user changes the customer-account field
             *
             * @event mapCustomerAccount
             * @param [Ext.form.field.Field] this
             * @param [object] newValue
             * @param [object] oldValue
             * @param [object] eOpts
             */
            'mapCustomerAccount'
        );

        return true;
    },


    /**
     * creates the general form and layout
     *
     * @return [Array] computed form
     */
    createGeneralForm: function () {
        this.mainTitle = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=detail/main/field/title}Title{/s}',
            allowBlank: false,
            required: true,
            name: 'title',
            translatable: true,
            labelWidth: 155
        });

        this.shopsSelector = this.getShopsSelector();

        return [
            this.mainTitle,
            {
                xtype: 'combobox',
                name: 'authorId',
                fieldLabel: '{s name=detail/main/field/author}Author{/s}',
                store: Ext.create('Shopware.store.User').load(),
                valueField: 'id',
                editable: true,
                displayField: 'name',
                pageSize: 10,
            },
            {
                xtype: 'checkbox',
                fieldLabel: '{s name=detail/main/field/active}Active{/s}',
                inputValue: 1,
                uncheckedValue: 0,
                name: 'active',
                boxLabel: '{s name=detail/main/field/active/help}Blog article will be shown in the storefront{/s}'
            },
            this.shopsSelector
        ]
    },

    /**
     * creates the general form and layout
     *
     * @return [Array] computed form
     */
    createContentForm: function () {
        return [
            {
                xtype: 'textarea',
                minWidth: 250,
                height: 40,
                fieldLabel: '{s name=detail/main/field/short_description}Short description{/s}',
                allowBlank: false,
                required: true,
                name: 'shortDescription',
                translatable: true
            },
            {
                xtype: 'container',
                html: '{s name=detail/main/field/short_description/help}The short description will be displayed in the listing of the store front.{/s}',
                margin: '0 0 8 125',
                style: 'font-size: 11px; color: #999; font-style: italic;'
            },
            {
                xtype: 'tinymce',
                height: 370,
                name: 'description',
                translatable: true,
                translationLabel: '{s name=detail/main/field/description}{/s}'
            }
        ]
    },

    /**
     * @returns { Shopware.form.field.ShopGrid }
     */
    getShopsSelector: function() {
        var selectionFactory = Ext.create('Shopware.attribute.SelectionFactory');

        return Ext.create('Shopware.form.field.ShopGrid', {
            name: 'shopIds',
            fieldLabel: '{s name=detail/main/field/title/shop_selector/label}Limit to shop(s){/s}',
            helpText: '{s name=detail/main/field/title/shop_selector/helper}If set, it limits the blog\'s visibility to the configured shops. If this is left empty, the blog will be available in all shops.{/s}',
            allowSorting: false,
            height: 130,
            anchor: '100%',
            labelWidth: 155,
            store: selectionFactory.createEntitySearchStore('Shopware\\Models\\Shop\\Shop'),
            searchStore: selectionFactory.createEntitySearchStore('Shopware\\Models\\Shop\\Shop')
        });
    }
});
//{/block}
