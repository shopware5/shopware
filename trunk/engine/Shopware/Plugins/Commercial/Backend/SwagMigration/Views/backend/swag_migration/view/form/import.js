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
 * @package    Migration
 * @subpackage Form
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Migration import form
 * Lets the user select data to be imported and starts the actual import
 */
//{namespace name=backend/swag_migration/main}
//{block name="backend/swag_migration/view/form/import"}
Ext.define('Shopware.apps.SwagMigration.view.form.Import', {
    /**
     * Define that the billing field set is an extension of the Ext.form.FieldSet
     * @string
     */
    extend: 'Ext.form.Panel',
    bodyStyle: 'padding:10px',
    /**
     * The Ext.container.Container.layout for the fieldset's immediate child items.
     * @object
     */
    layout: 'anchor',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias: 'widget.migration-form-import',
    /**
     * Set css class for this component
     * @string
     */
    cls: 'shopware-form',



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

        me.addEvents(
            /**
             * Fired when the supplier combo changes in order to trigger form validation
             */
            'validate'
        );

        me.items = me.createItems();
        me.callParent(arguments);
    },

    /**
     * Returns an array with the fieldsets available in this view
     * @return Array
     */
    createItems: function() {
        var me = this;

        me.basePath = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=articleImagesPath}Shop path for the article images (e.g. http://www.example.org/old_shop or /var/www/old_shop){/s}',
            name: 'basepath',
            value: '',
            labelWidth: 500,
            allowBlank: false,
            listeners: {
                change: function() {
                    me.fireEvent('validate');
                }
            }
        });

        me.fieldSet = {
            xtype:'fieldset',
            title: '{s name=importSettings}Import settings{/s}',
            autoHeight: true,
            buttonAlign: 'right',
            //defaults: { width: 250 },
            defaults: {
                anchor: '100%',
                labelWidth: 500,
                checked: true
            },
            defaultType: 'textfield',
            items :[{
                fieldLabel: '{s name=importProducts}Import products{/s}',
                name: 'import_products',
                xtype: 'checkbox'
            }, {
                fieldLabel: '{s name=importTranslations}Import translations{/s}',
                name: 'import_translations',
                xtype: 'checkbox'
            }, {
                fieldLabel: '{s name=importPrices}Import customer group prices{/s}',
                name: 'import_prices',
                xtype: 'checkbox'
            }, {
                fieldLabel: '{s name=importArticleImages}Import product images{/s}',
                name: 'import_images',
                xtype: 'checkbox',
                listeners: {
                    change: function(checkBox, newValue, oldValue, eOpts) {
                        // if the product images are going to be imported, the basePath field is mandatory
                        me.basePath.allowBlank = !newValue;
                        me.fireEvent('validate');
                    }
                }
            }, {
                fieldLabel: '{s name=importCategories}Import categories{/s}',
                name: 'import_categories',
                xtype: 'checkbox'
            }, {
                fieldLabel: '{s name=importCustomers}Import customers{/s}',
                name: 'import_customers',
                xtype: 'checkbox'
            }, {
                fieldLabel: '{s name=importRatings}Import ratings{/s}',
                name: 'import_ratings',
                xtype: 'checkbox'
            }, {
                fieldLabel: '{s name=importOrders}Import orders{/s}',
                name: 'import_orders',
                xtype: 'checkbox'
            }, {
                fieldLabel: '{s name=finish}Finish import{/s}',
                name: 'finish_import',
                xtype: 'checkbox'
            }, {
                fieldLabel: '{s name=defaultSupplier}Default supplier{/s}',
                name: 'supplier',
                hiddenName: 'supplier',
                valueField: 'name',
                displayField: 'name',
                triggerAction: 'all',
                xtype: 'combo',
                allowBlank: false,
                mode: 'remote',
                selectOnFocus: true,
                store: Ext.create('Shopware.apps.Base.store.Supplier'),
                listeners: {
                    change: function() {
                        me.fireEvent('validate');
                    }
                }
            },
                me.basePath
            ]
        };

        return me.fieldSet;
    }

});
//{/block}
