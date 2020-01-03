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
 * @package    PluginManager
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/plugin_manager/translation}
// {block name="backend/plugin_manager/controller/plugin"}
Ext.define('Shopware.apps.PluginManager.controller.Plugin', {

    extend: 'Ext.app.Controller',

    refs: [
        { ref: 'localListing', selector: 'plugin-manager-local-plugin-listing' }
    ],

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    snippets: {
        'licencePluginDownloadInstall': '{s name="licence_plugin_download_and_install"}The Shopware License Manager plugin is needed to install this plugin, but is currently not present in your system. <br><br><strong>Do you want to download and install the Shopware License Manager plugin?<strong>{/s}',
        'licencePluginDownloadActivate': '{s name="licence_plugin_install_and_activate"}The Shopware License Manager plugin is needed to install this plugin, but is currently not installed on your system. <br><br><strong>Do you want to install the Shopware License Manager plugin?<strong>{/s}',
        'licencePluginActivate': '{s name="licence_plugin_activate"}The Shopware License Manager plugin is needed to install this plugin, but is currently not active on your system. <br><br>Do you want to activate the Shopware License Manager plugin?<strong>{/s}',

        newRegistrationForm: {
            successTitle: '{s name=newRegistrationForm/successTitle}Shopware ID registration{/s}',
            successMessage: '{s name=newRegistrationForm/successMessage}Your Shopware ID has been successfully registered{/s}',
            waitTitle: '{s name=newRegistrationForm/waitTitle}Registering your Shopware ID{/s}',
            waitMessage: '{s name=newRegistrationForm/waitMessage}This process might take a few seconds{/s}'
        },

        domainRegistration: {
            successTitle: '{s name=domainRegistration/successTitle}Domain registration{/s}',
            successMessage: '{s name=domainRegistration/successMessage}Domain registration successful{/s}',
            waitTitle: '{s name=domainRegistration/waitTitle}Registering domain{/s}',
            waitMessage: '{s name=domainRegistration/waitMessage}This process might take a few seconds{/s}',
            validationFailed: "{s name=domainRegistration/validationFailed}<p>You have successfully logged in using your Shopware ID, but the domain validation process failed.<br><p>Please click <a href='https://docs.shopware.com/en/shopware-5-en/first-steps/shopware-account#link-your-shop' title='Shopware Account documentation' target='_blank'>here</a> to use manual domain validation.</p>{/s}"
        },

        login: {
            successTitle: '{s name=login/successTitle}Shopware ID{/s}',
            successMessage: '{s name=login/successMessage}Login successful{/s}',
            waitTitle: '{s name=login/waitTitle}Logging in...{/s}',
            waitMessage: '{s name=login/waitMessage}This process might take a few seconds{/s}'
        },

        growlMessage: '{s name=growlMessage}Plugin Manager{/s}'
    },

    init: function() {
        var me = this;

        Shopware.app.Application.on(me.getEventListeners());

        me.callParent(arguments);
    },

    getEventListeners: function() {
        var me = this;

        return {
            'install-plugin': me.installPlugin,
            'uninstall-plugin': me.uninstallPlugin,
            'secure-uninstall-plugin': me.secureUninstallPlugin,
            'reinstall-plugin': me.reinstallPlugin,
            'activate-plugin': me.activatePlugin,
            'deactivate-plugin': me.deactivatePlugin,
            'execute-plugin-update': me.executePluginUpdate,

            'download-plugin-licence': me.downloadPluginLicenceDirect,
            'update-plugin': me.updatePlugin,
            'update-dummy-plugin': me.updateDummyPlugin,
            'buy-plugin': me.purchasePlugin,
            'rent-plugin': me.purchasePlugin,
            'download-free-plugin': me.purchasePlugin,
            'request-plugin-test-version': me.purchasePlugin,

            'upload-plugin': me.uploadPlugin,
            'delete-plugin': me.deletePlugin,
            'expired-delete-plugin': me.deleteExpiredPlugin,
            'reload-plugin': me.reloadPlugin,
            'reload-local-listing': me.reloadLocalListing,
            'save-plugin-configuration': me.saveConfiguration,
            'store-login': me.login,
            'check-store-login': me.checkLogin,
            'open-login': me.openLogin,
            'destroy-login': me.destroyLogin,
            'store-register': me.register,
            'clear-all-cache': me.clearAllCache,
            scope: me
        };
    },

    uploadPlugin: function(form, callback) {
        var me = this;

        form.submit({
            onSuccess: function(response) {
                var result = Ext.decode(response.responseText);
                if (!result) {
                    result = Ext.decode(response.responseXML.body.childNodes[0].innerHTML);
                }

                if (result.success) {
                    Shopware.Notification.createGrowlMessage('', '{s name="plugin_file_uploaded"}Plugin uploaded{/s}');
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
        form.onSaveForm(form, false, function() {

        });
    },

    updatePlugin: function(plugin, callback) {
        var me = this,
            localListing = me.getLocalListing();

        me.authenticateForUpdate(plugin, function() {
            me.startPluginDownload(plugin, function() {
                me.displayLoadingMask(plugin, '{s name=execute_update}Plugin is being updated{/s}', false);
                localListing.getStore().load({
                    callback: function(records, operation, success) {
                        me.executePluginUpdate(plugin, function() {
                            Shopware.app.Application.fireEvent('load-update-listing', function(records) {
                                if (records.length === 0) {
                                    me.subApplication.getController('Navigation').displayLocalPluginPage();
                                }
                                me.hideLoadingMask();
                                callback();
                            });
                        });
                    }
                });
            }, true);
        });
    },

    updateDummyPlugin: function(plugin, callback) {
        this.startPluginDownload(plugin, callback);
    },

    startPluginDownload: function(plugin, callback, isUpdate) {
        var me = this;
        isUpdate = isUpdate || false;

        me.displayLoadingMask(plugin, '{s name="initial_download"}Initial plugin download{/s}');

        me.sendAjaxRequest(
            '{url controller=PluginManager action=metaDownload}',
            { technicalName: plugin.get('technicalName') },
            function(response) {
                me.hideLoadingMask();

                if (response.data.binaryVersion === plugin.get('version') && isUpdate) {
                    Shopware.Notification.createStickyGrowlMessage({
                        title: '{s name="title"}{/s}',
                        text: '{s name="subscriptionUpdate"}{/s}'
                    });
                    return;
                }

                var mask = me.createDownloadMask(plugin, response.data, function(fileName) {
                    me.sendAjaxRequest(
                        '{url controller=PluginManager action=extract}',
                        { technicalName: plugin.get('technicalName'), fileName: fileName },
                        function(extractResponse) {
                            me.sendAjaxRequest(
                                '{url controller=PluginManager action=refreshPluginList}',
                                { },
                                function() {
                                    callback(extractResponse);
                                }
                            );
                        }
                    );
                });

                mask.show();
                mask.startDownload(0);
            }
        );
    },

    purchasePlugin: function(plugin, price, callback) {
        var me = this;

        me.checkout(plugin, price, function(basket) {
            me.displayLoadingMask(plugin, '{s name="order_is_being_executed"}Order is being processed{/s}');

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

                    me.startPluginDownload(plugin, function() {
                        me.pluginBoughtEvent(plugin);
                        callback();
                    });
                }
            );
        });
    },

    downloadPluginLicenceDirect: function(licence, callback) {
        var me = this;

        me.startPluginDownload(licence);
    },

    checkout: function(plugin, price, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="open_basket"}Preparing order process{/s}');
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

            // add event listener to the model proxy to get access on thrown exceptions
            store.getProxy().on('exception', function (proxy, response) {
                response = Ext.decode(response.responseText);
                me.displayErrorMessage(response);
            }, me, { single: true });

            store.load({
                callback: function(records) {
                    if (records) {
                        var basket = records[0];

                        me.checkoutWindow = me.getView('account.Checkout').create({
                            basket: basket,
                            callback: callback
                        });

                        me.checkoutWindow.show();
                    }

                    me.hideLoadingMask();
                }
            });
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
                        '{s name="ion_cube_required_title"}Encrypted plugins{/s}',
                        '{s name="ion_cube_required_text"}The requested plugin is encrypted. You need the Ioncube Loader Extension to download the plugin{/s}'
                    );

                    return;
                }

                callback();
            }
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
            function(response) {
                me.handleCrudResponse(response, plugin, function() {
                    me.reloadMenu();
                }, me);
                callback(response);
            },
            // Error callback
            function(response) {
                // If a plugin update fails it will be disabled, however the list will not reload
                // so that the plugin is listed as activated even though it is disabled.

                // Reload menu to hide disabled menu items and reload the plugin listing so that
                // plugins are shown in the correct status of activated, disabled or not installed.
                me.reloadMenu();
                me.reloadLocalListing();

                // Standard error handling functionality which would be executed if no error
                // handler was specified.
                me.displayErrorMessage(response);
                me.hideLoadingMask();
            },
            300000
        );
    },

    checkLogin: function(callback) {
        var me = this;

        me.checkAccessToken(function(response) {
            if (response.success == false) {
                me.openLogin(callback);
            } else {
                if (response.hasOwnProperty('shopwareId')) {
                    me.fireRefreshAccountData(response);
                }

                callback();
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

    destroyLogin: function(comp) {
        var me = this;

        comp.destroy();
        me.loginMask = null;
    },

    openLogin: function(callback) {
        var me = this;

        if (!me.loginMask) {
            me.loginMask = Ext.create('Shopware.apps.PluginManager.view.account.LoginWindow', {
                callback: callback
            }).show();
        }
    },

    login: function(params, callback) {
        var me = this;

        me.splashScreen = Ext.Msg.wait(
            me.snippets.login.waitMessage,
            me.snippets.login.waitTitle
        );

        me.sendAjaxRequest(
            '{url controller=PluginManager action=login}',
            params,
            function(response) {
                response.shopwareId = params.shopwareID;
                me.splashScreen.close();

                if (response.success == true) {
                    Ext.create('Shopware.notification.SubscriptionWarning').checkSecret();

                    Shopware.Notification.createGrowlMessage(
                        me.snippets.login.successTitle,
                        me.snippets.login.successMessage,
                        me.snippets.growlMessage
                    );

                    me.fireRefreshAccountData(response);

                    if (params.registerDomain !== false) {
                        me.submitShopwareDomainRequest(params, callback);
                    } else {
                        me.destroyLogin(me.loginMask);
                        callback(response);
                    }
                }
            },
            function(response) {
                me.splashScreen.close();
                me.displayErrorMessage(response, callback);
            }
        );
    },

    register: function(registerData, callback) {
        var me = this;

        me.submitShopwareIdRequest(
            registerData,
            '{url controller="firstRunWizard" action="registerNewId"}',
            callback
        );
    },

    submitShopwareIdRequest: function(params, url, callback) {
        var me = this;

        me.splashScreen = Ext.Msg.wait(
            me.snippets.newRegistrationForm.waitMessage,
            me.snippets.newRegistrationForm.waitTitle
        );

        Ext.Ajax.request({
            url: url,
            method: 'POST',
            params: params,
            callback: function(options, success, response) {
                var result = Ext.JSON.decode(response.responseText, true);

                if (!result || result.success == false) {
                    response = Ext.decode(response.responseText);
                    me.displayErrorMessage(response);

                    me.splashScreen.close();
                } else if (result.success) {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.newRegistrationForm.successTitle,
                        me.snippets.newRegistrationForm.successMessage,
                        me.snippets.growlMessage
                    );

                    Ext.create('Shopware.notification.SubscriptionWarning').checkSecret();

                    if (params.registerDomain !== false) {
                        me.submitShopwareDomainRequest(params, callback);
                    }

                    response.shopwareId = params.shopwareID;
                    me.fireRefreshAccountData(response);
                    callback(response);
                }
            }
        });
    },

    submitShopwareDomainRequest: function(params, callback) {
        var me = this;

        me.splashScreen = Ext.Msg.wait(
            me.snippets.domainRegistration.waitMessage,
            me.snippets.domainRegistration.waitTitle
        );

        Ext.Ajax.request({
            url: '{url controller="firstRunWizard" action="registerDomain"}',
            method: 'POST',
            params: params,
            success: function(response) {
                var result = Ext.JSON.decode(response.responseText);

                if (!result || result.success == false) {
                    response = Ext.decode(response.responseText);
                    me.displayErrorMessage({ message: me.snippets.domainRegistration.validationFailed });
                    me.displayErrorMessage(response);

                    me.splashScreen.close();
                } else if (result.success) {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.domainRegistration.successTitle,
                        me.snippets.domainRegistration.successMessage,
                        me.snippets.growlMessage
                    );
                    callback(response);
                }
            }
        });
    },

    installPlugin: function(plugin, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_installed"}Plugin is being installed{/s}', false);

        me.sendAjaxRequest(
            '{url controller=PluginInstaller action=installPlugin}',
            { technicalName: plugin.get('technicalName') },
            function(response) {
                me.handleCrudResponse(response, plugin);
                callback(response);
            },
            null,
            300000
        );
    },

    uninstallPlugin: function(plugin, callback) {
        var me = this;

        if (plugin.allowSecureUninstall()) {
            me.confirmMessage(
                '',
                '{s name="uninstall_remove_data"}The plugin will be uninstalled. Do you also like to remove the saved data of the plugin?{/s}',
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

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_uninstalled"}Plugin is being uninstalled{/s}', false);

        me.sendAjaxRequest(
            '{url controller=PluginInstaller action=uninstallPlugin}',
            { technicalName: plugin.get('technicalName') },
            function(response) {
                me.handleCrudResponse(response, plugin, function() {
                    me.reloadMenu();
                }, me);
                callback(response);
            },
            null,
            300000
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

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_uninstalled"}Plugin is being uninstalled{/s}', false);

        me.sendAjaxRequest(
            '{url controller=PluginInstaller action=secureUninstallPlugin}',
            { technicalName: plugin.get('technicalName') },
            function(response) {
                me.handleCrudResponse(response, plugin, function() {
                    me.reloadMenu();
                }, me);
                callback(response);
            },
            null,
            300000
        );
    },

    deletePlugin: function(plugin, callback) {
        var me = this;

        me.confirmMessage(
            '{s name="delete_plugin_title"}Delete plugin{/s}',
            '{s name="delete_plugin_confirm"}Are you sure you want to delete the plugin:{/s} ' + plugin.get('label'),
            function() {
                me.displayLoadingMask(plugin, '{s name="plugin_is_being_deleted"}Plugin is being deleted{/s}');
                me.sendAjaxRequest(
                    '{url controller=PluginInstaller action=deletePlugin}',
                    { technicalName: plugin.get('technicalName') },
                    callback
                );
            }
        );
    },

    deleteExpiredPlugin: function(plugin, callback) {
        var me = this;

        if (plugin.get('installationDate')) {
            me.uninstallPlugin(plugin, function () {
                me.displayLoadingMask(plugin, '{s name="plugin_is_being_deleted"}Plugin is being deleted{/s}');
                me.sendAjaxRequest(
                    '{url controller=PluginInstaller action=deletePlugin}',
                    { technicalName: plugin.get('technicalName') },
                    callback
                );
            });
        } else {
            me.displayLoadingMask(plugin, '{s name="plugin_is_being_deleted"}Plugin is being deleted{/s}');
            me.sendAjaxRequest(
                '{url controller=PluginInstaller action=deletePlugin}',
                { technicalName: plugin.get('technicalName') },
                callback
            );
        }
    },

    activatePlugin: function(plugin, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_activated"}Plugin is being activated{/s}');

        me.sendAjaxRequest(
            '{url controller=PluginInstaller action=activatePlugin}',
            { technicalName: plugin.get('technicalName') },
            function(response) {
                me.handleCrudResponse(response, plugin, function() {
                    me.reloadMenu();
                }, me);
                callback(response);
            }
        );
    },

    deactivatePlugin: function(plugin, callback) {
        var me = this;

        me.displayLoadingMask(plugin, '{s name="plugin_is_being_deactivated"}Plugin is being deactivated{/s}');

        me.sendAjaxRequest(
            '{url controller=PluginInstaller action=deactivatePlugin}',
            { technicalName: plugin.get('technicalName') },
            function(response) {
                me.handleCrudResponse(response, plugin, function() {
                    me.reloadMenu();
                }, me);
                callback(response);
            }
        );
    },

    handleCrudResponse: function(response, plugin, callback, scope) {
        response = response.result;

        callback = callback || Ext.emptyFn;
        scope = scope || this;

        if (!response) {
            return;
        }

        var message = this.getResponseMessage(response);

        if (Ext.isObject(message)) {
            Shopware.Notification.createStickyGrowlMessage(message);
        } else if (Ext.isString(message)) {
            Shopware.Notification.createStickyGrowlMessage({ text: message });
        }

        Shopware.app.Application.fireEvent('plugin-state-changed', plugin);

        var caches = this.getResponseCacheClearTask(response);
        if (caches !== null) {
            this.clearCache(caches, plugin, callback, scope);
        } else {
            Ext.callback(callback, scope);
        }
    },

    getResponseMessage: function(response) {
        if (response.hasOwnProperty('message')) {
            return response.message;
        }

        if (response.hasOwnProperty('scheduled') && response.scheduled.hasOwnProperty('message')) {
            return response.scheduled.message;
        }
        return null;
    },

    getResponseCacheClearTask: function(response) {
        if (response.hasOwnProperty('invalidateCache')) {
            return response.invalidateCache;
        }
        if (response.hasOwnProperty('scheduled') && response.scheduled.hasOwnProperty('cache')) {
            return response.scheduled.cache;
        }
        return null;
    },

    clearAllCache: function () {
        var me = this;

        var getCaches = Ext.Ajax.request({
            async: false,
            url: '{url controller=PluginManager action=getAllCaches}',
            method: 'GET'
        });

        var response = Ext.decode(getCaches.responseText);

        me.clearCache(response.caches);
    },

    clearCache: function(caches, plugin, callback, scope) {
        var me = this;

        var message = Ext.String.format(
            '{s name=clear_cache}This plugin needs a new initialisation in the following caches: [0]Clear cache?{/s}',
            '<br><br>- ' + caches.join('<br>- ') + '<br><br>'
        );

        me.confirmMessage(
            '',
            message,
            function() {
                if (plugin) {
                    me.displayLoadingMask(plugin, '{s name="cache_process"}Cache will be cleared{/s}');
                }

                var params = {};

                Ext.each(caches, function(cacheKey) {
                    params['cache[' + cacheKey + ']'] = 'on';
                });

                Ext.Ajax.request({
                    url: '{url controller="Cache" action="clearCache"}',
                    method: 'POST',
                    params: params,
                    callback: function() {
                        if (caches.indexOf('theme') >= 0 || caches.indexOf('frontend') >= 0) {
                            Shopware.app.Application.fireEvent('shopware-theme-cache-warm-up-request');
                        }
                        if (Ext.isFunction(callback)) {
                            Ext.callback(callback, scope);
                        }
                        me.hideLoadingMask();
                    }
                });
            },
            function() {
                Ext.callback(callback, scope);
            }
        );
    },

    reloadMenu: function() {
        Shopware.app.Application.fireEvent('reload-main-menu');
    }
});
// {/block}
