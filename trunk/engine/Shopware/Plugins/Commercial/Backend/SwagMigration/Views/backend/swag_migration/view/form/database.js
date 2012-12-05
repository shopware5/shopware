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
 * Shopware UI - Migration database form
 * Form for the database and import settings
 */
//{namespace name=backend/swag_migration/main}
//{block name="backend/swag_migration/view/form/database"}
Ext.define('Shopware.apps.SwagMigration.view.form.Database', {
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
    alias: 'widget.migration-form-database',
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

        me.items = me.createItems();
        me.callParent(arguments);
    },

    /**
     * Returns an array with the fieldsets available in this view
     * @return Array
     */
    createItems: function() {
        var me = this;

        return [ me.createFormFieldProfileSelection(), me.createFormFieldDatabaseSettings(), me.createFormFieldClearShop() ];
    },


    /**
     * Creates the "initialize shop" field set which allows the user to delete categories and articles from the shop
     * @return
     */
    createFormFieldClearShop: function() {
        var me = this;

        var button = Ext.create('Ext.button.Button', {
            tooltip: '{s name=clearShop}Clear the local Shopware shop in order to have a clean start{/s}',
            name: 'deleteArticlesAndCategories',
            text: '{s name=deleteCategoriesAndArticles}Delete categories and articles{/s}',
            cls: 'primary',
            scope: this,
            handler  : function() {
                Ext.Msg.show({
                    title: '{s name=initShop}Initialize Shop{/s}',
                    msg: '{s name=initShop/AreYouSure}Are you sure? All categories and articles will be lost permanently.{/s}',
                    buttons: Ext.Msg.YESNO,
                    scope: this,
                    fn: function (btn) {
                        if (btn === "yes") {
                            Ext.Ajax.request({
                                url: '{url action="clearShop"}',
                                success: function (r, o){
                                    var res = r.responseText;
                                    Shopware.Notification.createGrowlMessage('{s name=deleted/successTitle}Success{/s}', '{s name=deleted/successMessage}Successfully delete all categories and articles{/s}', 'SwagMigration');

                                },
                                scope: this
                            });
                        }
                    },
                    icon: Ext.MessageBox.WARNING
                });
            }
        });


        me.clearShopForm = Ext.create('Ext.form.FieldSet', {
            autoHeight: true,
            title: '{s name=initShop}Initialize Shop{/s}',
            items: [
                button
            ]
        });

        return me.clearShopForm;

    },

    /**
     * Creates the database seleciton combobox
     * @return Ext.form.ComboBox
     */
    createDatabaseSelection: function() {
        var me = this;

        me.databaseSelection = Ext.create('Ext.form.ComboBox', {
            store: me.databaseStore,
            fieldLabel: '{s name=database}Database{/s}',
            name: 'database',
            displayField: 'name',
            allowBlank: false,
            mode: 'remote',
            disabled: true,
            emptyText: '{s name=selctProfileFirst}You need to select a profile first{/s}',
            listeners: {
                'beforequery': { fn: function(e) {
                    var form = this.getForm(),
                        values = form.getValues();

                    if (values.profile === Ext.undefined) {
                        return false;
                    }

                    // Add database settings to the request
                    e.combo.store.getProxy().extraParams = values;

                    // Load the store with the database settings from above
                    e.combo.store.load(function(data, operation, success) {
                        if (!success) {
                            var rawData = operation.request.proxy.reader.jsonData,
                                message = rawData.message;
                            Shopware.Notification.createGrowlMessage('{s name=getDatabases/errorTitle}Error{/s}', '{s name=getDatabases/errorMessage}Could not get databases{/s}' + '<br />' + message, 'SwagMigration');
                            e.combo.emptyText = '{s name=selectDatabaseWhenSettingsMatch}Select source database if above settings do match{/s}';
                            e.combo.select(null);
                        }
                    });
                }, scope: this }
            }
        });

        return me.databaseSelection;
    },

    /**
     * Creates the fieldset with the database settings
     * @return Ext.form.FieldSet
     */
    createFormFieldDatabaseSettings: function() {
        var me = this;

        me.createDatabaseSelection();

        me.databaseSettingsForm = Ext.create('Ext.form.FieldSet', {
            title: '{s name=databaseSettings}Database settings{/s}',
            autoHeight: true,
            defaults: { anchor: '100%' },
            defaultType: 'textfield',
            items :[{
                xtype: 'label',
                text: '{s name=dbDescription}Database settings for the shop you want to migrate{/s}'
            }, {
				fieldLabel: '{s name=dbUser}User{/s}',
				name: 'username',
				value: 'root'
			}, {
				fieldLabel: '{s name=dbPassword}Password{/s}',
				name: 'password',
				inputType: 'password',
				value: 'root'
			}, {
				fieldLabel: '{s name=dbServer}Server{/s}',
				name: 'host',
				value: 'localhost',
				allowBlank: false
			}, {
				fieldLabel: '{s name=dbPort}Port{/s}',
				name: 'port',
				value: 'default'
			}, {
				fieldLabel: '{s name=dbPrefix}Prefix{/s}',
				name: 'prefix',
				value: 'default'
			}, me.databaseSelection]
        });

        return me.databaseSettingsForm;

    },

    /**
     * Creates the fieldset with the profile selection
     * @return Ext.form.FieldSet
     */
    createFormFieldProfileSelection: function() {
        var me = this;

        me.profileSelection = Ext.create('Ext.form.ComboBox', {
            fieldLabel: '{s name=profile}Profile{/s}',
            name: 'profile',
            hiddenName: 'profile',
            valueField: 'id',
            displayField: 'name',
            typeAhead: true,
            triggerAction: 'all',
            xtype: 'combo',
            allowBlank: false,
            mode: 'remote',
            selectOnFocus: true,
            forceSelection : true,
            editable: false,
            store: me.profileStore,
            listeners: {
                change: function(combo, newValue, oldValue, eOpts) {
                    if (newValue) {
                        me.databaseSelection.setDisabled(false);
                        me.databaseSelection.emptyText = '{s name=selectDatabaseWhenSettingsMatch}Select source database if above settings do match{/s}';
                        me.databaseSelection.select(null);
                    }
                }
            }
        });

        me.profileSelectionForm = Ext.create('Ext.form.FieldSet', {
            title: '{s name=selectProfile}Select profile{/s}',
            autoHeight: true,
            defaults: { anchor: '100%' },
            items : [
                {
                    xtype: 'label',
                    text: '{s name=profileSelectDescription}Select the shop you want to migrate to Shopware{/s}'
                },
                me.profileSelection
            ]
        });

        return me.profileSelectionForm;

    }

});
//{/block}
