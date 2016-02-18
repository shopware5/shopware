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
 */

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/theme/main}

//{block name="backend/theme/controller/detail"}

Ext.define('Shopware.apps.Theme.controller.Detail', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'detailWindow', selector: 'theme-detail-window' },
        { ref: 'listingView', selector: 'theme-listing dataview' },
        { ref: 'shopCombo', selector: 'theme-list-window combobox[name=shop]' }
    ],

    init: function() {
        var me = this;

        me.control({
            'theme-config-set-window': {
                'assign-config-sets': me.onAssignConfigSets
            },
            'theme-detail-window': {
                saveConfig: me.saveConfig,
                'load-config-sets': me.onLoadConfigSets
            },
            'theme-listing-info-panel': {
                'configure-theme': me.onConfigureTheme
            }
        });

        Shopware.app.Application.on('theme-save-successfully', function(controller, result, window, record) {
            window.destroy();
            me.getListingView().getStore().load();
        });

        Shopware.app.Application.on('theme-save-exception', function(controller, data, window, record, form) {
            Shopware.Notification.createGrowlMessage(
                '{s name="application"}Theme manager{/s}',
                data.message,
                'Theme manager'
            );
        });
    },

    onAssignConfigSets: function(window, theme, formPanel) {
        var me = this, data = { };

        formPanel.getForm().getFields().each(function(field) {
            if (!field.value) {
                return false;
            }
            var item = field.store.getById(field.value);

            data = Ext.apply(data, item.get('values'));
        });

        var detailForm = me.getDetailWindow().formPanel;

        detailForm.getForm().getFields().each(function(field) {
            if(data.hasOwnProperty(field.name)) {
                field.setValue(data[field.name]);
            }
        });

        window.destroy();
    },


    onLoadConfigSets: function(window, theme) {
        var me = this;

        var store = Ext.create('Shopware.apps.Theme.store.ConfigSets');

        store.load({
            params: {
                templateId: theme.get('id')
            },
            callback: function() {
                var window = me.getView('config_sets.Window').create({
                    store: store
                }).show();
            }
        });
    },


    /**
     * Event listener of the toolbar "configure button".
     * Opens the theme configuration window.
     */
    onConfigureTheme: function() {
        var me = this, shop, theme;

        shop = me.getSelectedShop();
        theme = me.getSelectedTheme();

        if (!(shop instanceof Ext.data.Model)) {
            return;
        }
        if (!(theme instanceof Ext.data.Model)) {
            return;
        }

        theme.reload({
            params: {
                shopId: shop.get('id')
            },
            callback: function(record) {
                me.getView('detail.Window').create({
                    configLayout: me.createThemeConfiguration(record, shop),
                    theme: record,
                    shop:  shop
                }).show();
            }
        });
    },

    /**
     * Helper function which generates the theme configuration form fields
     * for the passed theme.
     *
     * @param theme
     * @param shop
     * @returns { Array }
     */
    createThemeConfiguration: function(theme, shop) {
        var me = this, elements = [], element;

        theme.getLayout().each(function(container) {
            element = me.createConfigContainer(container, shop);
            elements.push(element);
        });

        return elements;
    },

    /**
     * Creates the whole config container for the theme
     * configuration panel.
     * @param container
     * @param shop
     */
    createConfigContainer: function(container, shop) {
        var me = this, items = [],
            data = container.data;

        delete data.id;

        if (container.getElements() instanceof Ext.data.Store) {
            container.getElements().each(function(child) {
                var element = me.createConfigElement(child, shop);
                items.push(element);
            });
        }
        if (container.getChildren() instanceof Ext.data.Store) {
            container.getChildren().each(function(child) {
                var element = me.createConfigContainer(child, shop);
                items.push(element);
            });
        }

        data.items = items;

        if (Ext.isObject(data.attributes)) {
            data = Ext.apply(data, { }, data.attributes);
        }
        delete data.attributes;

        return data;
    },

    createConfigElement: function(element, shop) {
        var me = this,
            data = element.data;

        delete data.id;

        if (!data.fieldLabel) {
            data.fieldLabel = data.name;
        }

        if (data.xtype == "theme-select-field") {
            data.store = me.createSelectStore(data.selection);
            data.valueField = 'value';
            data.displayField = 'text';
        }

        if (element['getConfigValuesStore'] instanceof Ext.data.Store) {
            data.value = me.getElementShopValue(element, shop);
        }

        if (data.xtype == "theme-checkbox-field" && data.value) {
            data.checked = true;
        }

        if (data.value == Ext.undefined) {
            data.value = null;
        }

        if (Ext.isObject(data.attributes)) {
            data = Ext.apply(data, { }, data.attributes);
        }

        return data;
    },

    /**
     *
     * @param values
     * @returns { Ext.data.Store }
     */
    createSelectStore: function(values) {
        return Ext.create('Ext.data.Store', {
            fields: ['text', 'value'],
            data: values,
            queryMode: 'local'
        });
    },

    /**
     * Helper function which returns the theme config value
     * for the passed element and shop.
     *
     * @param element
     * @param shop
     * @returns mixed
     */
    getElementShopValue: function(element, shop) {
        var value = element.get('defaultValue');

        element.getConfigValues().each(function(shopValue) {
            if (shopValue instanceof Ext.data.Model) {
                if (shopValue.get('shopId') == shop.get('id')) {
                    value = shopValue.get('value');
                    return false;
                }
            }
        });

        return value;
    },

    /**
     * Event listener which called over the detail window
     * save button.
     * This function updates the theme configuration for the passed shop.
     *
     * @param theme
     * @param shop
     * @param formPanel
     * @param window
     */
    saveConfig: function(theme, shop, formPanel, window) {
        var me = this;

        if (window instanceof Ext.window.Window) {
            window.setLoading(true);
        }

        theme = me.updateShopValues(
            theme,
            shop,
            formPanel.getForm().getFields(),
            formPanel.getForm().getValues()
        );

        theme.save({
            callback: function() {
                Shopware.Notification.createGrowlMessage(
                    '{s name="application"}Theme manager{/s}',
                    '{s name="save_message"}Theme configuration saved{/s}',
                    'Theme manager'
                );

                if (window instanceof Ext.window.Window) {
                    window.destroy();
                }

                if (me.getSelectedTheme().get('enabled') == 1) {
                    Shopware.app.Application.fireEvent('shopware-theme-cache-warm-up-request', shop.get('id'));
                }
            }
        });

    },

    /**
     * Helper function which updates the shop theme configuration
     * with the passed values object.
     *
     * @param theme
     * @param shop
     * @param formFields
     * @param formValues
     * @returns { Shopware.apps.Theme.model.Theme }
     */
    updateShopValues: function(theme, shop, formFields, formValues) {
        var data = [];

        var store = Ext.create('Ext.data.Store', {
            model: 'Shopware.apps.Theme.model.ConfigValue'
        });

        formFields.each(function(field) {
            var model = Ext.create('Shopware.apps.Theme.model.ConfigValue', {
                shopId: shop.get('id'),
                elementId: field.elementId,
                elementName: field.name,
                value: formValues[field.name]
            });

            store.add(model);
        });

        theme['getConfigValuesStore'] = store;

        return theme;
    },

    /**
     * Returns the selected theme model of the theme listing
     *
     * @returns { Shopware.apps.Theme.model.Theme }
     */
    getSelectedTheme: function() {
        var me = this;

        if (!(me.getListingView())) {
            return null;
        }

        var selModel = me.getListingView().getSelectionModel();

        if (selModel.getSelection().length > 0) {
            return selModel.getSelection().shift();
        } else {
            return null;
        }
    },

    /**
     * Returns the selected shop model of the toolbar combo box.
     *
     * @returns { Shopware.apps.Base.model.Shop }
     */
    getSelectedShop: function() {
        var me = this;

        if (!(me.getShopCombo())) {
            return null;
        }

        return me.getShopCombo().getStore().getById(
            me.getShopCombo().getValue()
        );
    }


});

//{/block}
