
Ext.define('Shopware.apps.Theme.controller.Detail', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'listingView', selector: 'theme-listing dataview' },
        { ref: 'shopCombo', selector: 'theme-list-window combobox[name=shop]' }
    ],

    init: function() {
        var me = this;

        me.control({
            'theme-detail-window': {
                saveConfig: me.saveConfig
            },
            'theme-list-window': {
                'configure-theme': me.onConfigureTheme
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
                    elements: me.createThemeConfiguration(record, shop),
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
        var me = this, elements = [], data;

        theme.getElements().each(function(element) {
            data = element.data;
            delete data.id;

            if (!data.fieldLabel) {
                data.fieldLabel = data.name;
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
            console.log("data", data);

            elements.push(data);
        });
        return elements;
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

        theme = me.updateShopValues(
            theme,
            shop,
            formPanel.getForm().getValues()
        );

        theme.save({
            callback: function() {
                if (window instanceof Ext.window.Window) {
                    window.destroy();
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
     * @param values
     * @returns mixed
     */
    updateShopValues: function(theme, shop, values) {
        var me = this, configValue;

        theme.getElements().each(function(element) {
            configValue = me.getShopConfigValue(element, shop);

            configValue.set(
                'value',
                values[element.get('name')]
            );
        });

        return theme;
    },

    getShopConfigValue: function(element, shop) {
        var me = this,
            valueObject = null;

        element.getConfigValues().each(function(configValue) {
            if (configValue.get('shopId') == shop.get('id')) {
                valueObject = configValue;
                return false;
            }
        });

        if (valueObject == null) {
            valueObject = Ext.create('Shopware.apps.Theme.model.ConfigValue', {
                shopId: shop.get('id'),
                elementId: element.get('id')
            });

            if (!element.getConfigValues() instanceof Ext.data.Store) {
                element['configValuesStore'] = Ext.create('Ext.data.Store', {
                    model: 'Shopware.apps.Theme.model.ConfigValue'
                });
            }
            element.getConfigValues().add(valueObject);
        }

        return valueObject;
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