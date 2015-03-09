
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.controller.Plugin', {

    extend:'Ext.app.Controller',

    refs: [
        { ref: 'localListing', selector: 'plugin-manager-local-plugin-listing' }
    ],

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    init: function() {
        var me = this;

        Shopware.app.Application.on(me.getEventListeners());

        me.callParent(arguments);
    },

    getEventListeners: function() {
        var me = this;

        return {
            'install-plugin':              me.installPlugin,
            'uninstall-plugin':            me.uninstallPlugin,
            'secure-uninstall-plugin':     me.secureUninstallPlugin,
            'reinstall-plugin':            me.reinstallPlugin,
            'activate-plugin':             me.activatePlugin,
            'deactivate-plugin':           me.deactivatePlugin,
            'update-plugin':               me.updatePlugin,
            'execute-plugin-update':       me.executePluginUpdate,
            'update-dummy-plugin':         me.updateDummyPlugin,
            'upload-plugin':               me.uploadPlugin,
            'delete-plugin':               me.deletePlugin,
            'reload-plugin':               me.reloadPlugin,
            'store-login':                 me.login,
            'download-plugin-licence':     me.downloadPluginLicenceDirect,
            'reload-local-listing':        me.reloadLocalListing,
            'import-plugin-licence':       me.importPluginLicence,
            'save-plugin-configuration':   me.saveConfiguration,
            'buy-plugin':                  me.purchasePlugin,
            'rent-plugin':                 me.purchasePlugin,
            'download-free-plugin':        me.purchasePlugin,
            'request-plugin-test-version': me.purchasePlugin,
            'check-store-login':           me.checkLogin,
            'open-login':                  me.openLogin,
            'check-licence-plugin':        me.checkLicencePlugin,
            scope: me
        };
    },

    uploadPlugin: function(form, callback) {
        var me = this;

        form.submit({
            onSuccess: function(response) {
                var result = Ext.decode(response.responseText);

                if (result.success) {
                    Shopware.Notification.createGrowlMessage('', '{s name="plugin_file_uploaded"}{/s}');
                    if (Ext.isFunction(callback)) {
                        callback();
                    }
                } else {
                    me.displayErrorMessage(result);
                }
            }
        });
    },

    reloadLocalListing: function() {
        var me = this,
            localListing = me.getLocalListing();

        localListing.getStore().load();
    },

    saveConfiguration: function(plugin, form) {
        var me = this;

        form.onSaveForm(form, false, function() {

        });
    },

    updateDummyPlugin: function(plugin, callback) {
        var me = this;

        if (plugin.get('technicalName') == 'SwagLicense') {
            me.checkIonCube(plugin, function() {
                me.updateDummyPluginDirect(plugin, callback);
            });
        } else {
            me.updateDummyPluginDirect(plugin, callback);
        }
    },

    updateDummyPluginDirect: function(plugin, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_installed"}{/s}');

        me.sendAjaxRequest(
            '{url controller=PluginManager action=updateDummyPlugin}',
            { technicalName: plugin.get('technicalName') },
            callback
        );
    },

    purchasePlugin: function(plugin, price, callback) {
        var me = this;

        me.checkout(plugin, price, function(basket) {

            me.displayLoadingMask(plugin, '{s name="order_is_being_executed"}{/s}');

            me.sendAjaxRequest(
                '{url controller="PluginManager" action="purchasePlugin"}',
                {
                    orderNumber: plugin.get('code'),
                    price: basket.get('netPrice'),
                    bookingDomain: basket.get('bookingDomain'),
                    priceType: price.get('type')
                },
                function(response) {

                    me.checkoutWindow.hide();

                    me.downloadPluginLicence(plugin, function(downloadResponse) {

                        me.pluginBoughtEvent(plugin);

                        callback(downloadResponse);
                    });
                }
            );
        });
    },

    importPluginLicence: function(licence, callback) {
        var me = this;

        if (!licence.get('licenseKey')) {
            callback();
            return;
        }

        me.checkIonCube(licence, function() {

            me.checkLicencePlugin(licence, function () {

                me.displayLoadingMask(licence, '{s name="licence_is_being_imported"}{/s}');

                me.sendAjaxRequest(
                    '{url controller="PluginManager" action="importPluginLicence"}',
                    {
                        licenceKey: licence.get('licenseKey')
                    },
                    callback
                );

            }, true);
        });
    },

    downloadPluginLicenceDirect: function(licence, callback) {
        var me = this;

        me.checkIonCube(licence, function() {

            me.checkLicencePlugin(licence, function () {

                me.displayLoadingMask(licence, '{s name="plugin_is_being_downloaded"}{/s}');

                me.sendAjaxRequest(
                    '{url controller="PluginManager" action="downloadLicenceDirect"}',
                    {
                        domain    : licence.get('shop'),
                        binaryLink: licence.get('binaryLink'),
                        licenceKey: licence.get('licenseKey')
                    },
                    callback
                );

            });
        });
    },

    downloadPluginLicence: function(plugin, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_downloaded"}{/s}');

        me.sendAjaxRequest(
            '{url controller="PluginManager" action="downloadPluginLicence"}',
            { technicalName: plugin.get('technicalName') },
            callback
        );
    },

    checkout: function(plugin, price, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="open_basket"}{/s}');
        me.checkIonCube(plugin, function() {

            me.checkLicencePlugin(plugin, function() {

                me.checkLogin(function() {

                    var store = Ext.create('Shopware.apps.PluginManager.store.Basket');

                    var positions = [{
                        orderNumber: plugin.get('code'),
                        price: price.get('price'),
                        type: price.get('type'),
                        technicalName: plugin.get('technicalName')
                    }];

                    store.getProxy().extraParams = {
                        positions: Ext.encode(positions)
                    };

                    //add event listener to the model proxy to get access on thrown exceptions
                    store.getProxy().on('exception', function (proxy, response) {
                        response = Ext.decode(response.responseText);
                        me.displayErrorMessage(response);
                    }, me, { single: true });

                    store.load({
                        callback: function(records) {
                            var basket = records[0];

                            me.checkoutWindow = me.getView('account.Checkout').create({
                                basket: basket,
                                callback: callback
                            });

                            me.checkoutWindow.show();
                        }
                    });

                });
            });
        });
    },

    checkLicencePlugin: function(plugin, callback, force) {
        var me = this;

        if (plugin && !plugin.get('licenceCheck') && !force) {
            callback();
            return;
        }

        Ext.Ajax.request({
            url: '{url controller=PluginManager action=checkLicencePlugin}',
            method: 'POST',
            success: function(operation, opts) {
                var response = Ext.decode(operation.responseText);

                if (response.success === true) {
                    callback(response);
                    return;
                }

                var licence = Ext.create('Shopware.apps.PluginManager.model.Plugin', response.data);

                switch(response.state) {
                    case 'download':
                        me.confirmMessage(
                            '{s name="licence_plugin_required_title"}{/s}',
                            '{s name="licence_plugin_download_and_install"}{/s}',
                            function() {
                                me.updateDummyPlugin(licence, function () {
                                    me.installPlugin(licence, function () {
                                        me.activatePlugin(licence, callback);
                                    });
                                });
                            }
                        );
                        break;

                    case 'install':
                        me.confirmMessage(
                            '{s name="licence_plugin_required_title"}{/s}',
                            '{s name="licence_plugin_install_and_activate"}{/s}',
                            function() {
                                me.installPlugin(licence, function() {
                                    me.activatePlugin(licence, callback);
                                });
                            }
                        );

                        break;

                    case 'activate':
                        me.confirmMessage(
                            '{s name="licence_plugin_required_title"}{/s}',
                            '{s name="licence_plugin_activate"}{/s}',
                            function() {
                                me.activatePlugin(licence, callback);
                            }
                        );

                        break;
                }

            }
        });
    },

    checkIonCube: function(plugin, callback) {
        var me = this;

        if (!plugin.get('encrypted') && !plugin.get('licenceCheck') && !plugin.get('licenceKey')) {
            callback();
            return;
        }

        Ext.Ajax.request({
            url: '{url controller=PluginManager action=checkIonCubeLoader}',
            method: 'POST',
            success: function(operation, opts) {
                var response = Ext.decode(operation.responseText);

                if (response.success === false) {
                    Ext.Msg.alert(
                        '{s name="ion_cube_required_title"}{/s}',
                        '{s name="ion_cube_required_text"}{/s}'
                    );

                    return;
                }

                callback();
            }
        });

    },

    updatePlugin: function(plugin, callback) {
        var me = this;

        me.authenticateForUpdate(plugin, function() {
            me.displayLoadingMask(plugin, '{s name="download_update_and_install"}{/s}');

            me.sendAjaxRequest(
                '{url controller=PluginManager action=downloadUpdate}',
                { technicalName: plugin.get('technicalName') },
                function(response) {
                    me.executePluginUpdate(plugin, callback);
                }
            );
        });
    },

    authenticateForUpdate: function(plugin, callback) {
        var me = this;
        
        if (plugin.flaggedAsDummyPlugin()) {
            callback();
        } else {
            me.checkLogin(callback);
        }
    },

    executePluginUpdate: function(plugin, callback) {
        var me = this;

        me.sendAjaxRequest(
            '{url controller=PluginInstaller action=update}',
            { technicalName: plugin.get('technicalName') },
            callback
        );
    },

    checkLogin: function(callback) {
        var me = this;

        me.checkAccessToken(function(response) {
            if (response.success == false) {
                me.openLogin(callback);
            } else {

                if (response.hasOwnProperty('shopwareId')) {
                    me.fireRefreshAccountData(response.shopwareId);
                }

                callback();
                return;
            }
        });
    },

    checkAccessToken: function(callback) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=PluginManager action=getAccessToken}',
            method: 'POST',
            success: function (operation, opts) {
                var response = Ext.decode(operation.responseText);
                callback(response);
            }
        });
    },

    openLogin: function(callback) {
        var me = this;

        me.loginMask = Ext.create('Shopware.apps.PluginManager.view.account.Login', {
            callback: callback
        }).show();
    },

    login: function(shopwareId, password, callback) {
        var me = this;

        me.sendAjaxRequest(
            '{url controller=PluginManager action=login}',
            {
                shopwareId: shopwareId,
                password: password
            },
            function(response) {
                response.shopwareId = shopwareId;
                if (response.success == true) {
                    Ext.create('Shopware.notification.SubscriptionWarning').checkSecret();
                }
                me.fireRefreshAccountData(response);
                callback(response);
            }
        );
    },

    installPlugin: function(plugin, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_installed"}{/s}');

        me.sendAjaxRequest(
            '{url controller=PluginInstaller action=installPlugin}',
            { technicalName: plugin.get('technicalName') },
            function(response) {
                me.handleCrudResponse(response, plugin);
                callback(response);
            }
        );
    },

    uninstallPlugin: function(plugin, callback) {
        var me = this;

        if (plugin.allowSecureUninstall()) {
            me.confirmMessage(
                '',
                '{s name="uninstall_remove_data"}{/s}',
                function() {
                    me.doUninstall(plugin, callback);
                },
                function() {
                    me.secureUninstallPlugin(plugin, callback);
                }
            );
        } else {
            me.doUninstall(plugin, callback);
        }

    },

    doUninstall: function(plugin, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_uninstalled"}{/s}');

        me.sendAjaxRequest(
            '{url controller=PluginInstaller action=uninstallPlugin}',
            { technicalName: plugin.get('technicalName') },
            function(response) {
                me.handleCrudResponse(response, plugin);
                callback(response);
            }
        );
    },

    reinstallPlugin: function(plugin, callback) {
        var me = this,
            wasActive = plugin.get('active');

        me.secureUninstallPlugin(plugin, function() {
            me.installPlugin(plugin, function(response) {
                if (wasActive) {
                    me.activatePlugin(plugin, callback);
                } else {
                    callback(response);
                }
            });
        });
    },

    secureUninstallPlugin: function(plugin, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_uninstalled"}{/s}');

        me.sendAjaxRequest(
            '{url controller=PluginInstaller action=secureUninstallPlugin}',
            { technicalName: plugin.get('technicalName') },
            function(response) {
                me.handleCrudResponse(response, plugin);
                callback(response);
            }
        );
    },

    deletePlugin: function(plugin, callback) {
        var me = this;

        me.confirmMessage(
            '{s name="delete_plugin_title"}{/s}',
            '{s name="delete_plugin_confirm"}{/s} ' + plugin.get('label'),
            function() {
                me.displayLoadingMask(plugin, '{s name="plugin_is_being_deleted"}{/s}');
                me.sendAjaxRequest(
                    '{url controller=PluginInstaller action=deletePlugin}',
                    { technicalName: plugin.get('technicalName') },
                    callback
                );
            }
        );
    },

    activatePlugin: function(plugin, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_activated"}{/s}');

        me.sendAjaxRequest(
            '{url controller=PluginInstaller action=activatePlugin}',
            { technicalName: plugin.get('technicalName') },
            function(response) {
                me.handleCrudResponse(response, plugin);
                callback(response);
            }
        );
    },

    deactivatePlugin: function(plugin, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_deactivated"}{/s}');

        me.sendAjaxRequest(
            '{url controller=PluginInstaller action=deactivatePlugin}',
            { technicalName: plugin.get('technicalName') },
            function(response) {
                me.handleCrudResponse(response, plugin);
                callback(response);
            }
        );
    },

    handleCrudResponse: function(response, plugin) {

        if (response.hasOwnProperty('message')) {
            var message = response.message;

            if (Ext.isObject(message)) {
                Shopware.Notification.createStickyGrowlMessage(response.message);
            } else if (Ext.isString(message)) {
                Shopware.Notification.createStickyGrowlMessage({ text: response.message });
            }
        }

        if (response.hasOwnProperty('invalidateCache')) {
            this.clearCache(response.invalidateCache, plugin);
        }
    },

    clearCache: function(caches, plugin) {
        var me = this;

        var message = Ext.String.format(
            '{s name=clear_cache}{/s}',
            caches.join(', ') + '<br><br>'
        );

        me.confirmMessage(
            '',
            message,
            function() {
                if (plugin) {
                    me.displayLoadingMask(plugin, '{s name="cache_process"}{/s}');
                }

                var params = {};

                Ext.each(caches, function(cacheKey) {
                    params['cache[' + cacheKey + ']'] = 'on';
                });

                Ext.Ajax.request({
                    url:'{url controller="Cache" action="clearCache"}',
                    method: 'POST',
                    params: params,
                    callback: function() {
                        me.hideLoadingMask();
                    }
                });
            }
        );
    }
});
