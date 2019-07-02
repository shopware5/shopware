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
Ext.define('Shopware.component.ValidatePassword', {

    /**
     * Defines that the component is globally available and initialized it itself
     * @boolean
     */
    singleton: true,

    /**
     * Start listening on a global shopware event to reauthorize
     *
     * @constructor
     */
    constructor: function() {
        var me = this;

        Ext.onReady(function() {
            me.registerEventListeners();
        });
    },

    registerEventListeners: function() {
        Shopware.app.Application.on('Shopware.ValidatePassword', this.onPasswordValidation);
    },

    /**
     * Opens a popup and asks for a password. The user has to enter his password to continue the intended callback,
     * provided as `successCallback` method. If the user closes this popup without entering a password, the
     * `abortCallback` method will be called.
     *
     * @param { function } successCallback
     * @param { function } abortCallback
     * @param { boolean } isRetryAttempt
     */
    onPasswordValidation: function(successCallback, abortCallback, isRetryAttempt) {
        var passwordPrompt = new Ext.window.MessageBox(),
            displayText = '{s name=window/enterPassword}Please enter your password{/s}:',
            onFailure = function() {
                Shopware.app.Application.fireEvent('Shopware.ValidatePassword', successCallback, abortCallback, true);
            };

        successCallback = !Ext.isFunction(successCallback) ? Ext.emptyFn : successCallback;
        abortCallback = !Ext.isFunction(abortCallback) ? Ext.emptyFn : abortCallback;

        if (isRetryAttempt === true) {
            displayText = '{s name=window/passwordInvalid}Your password is invalid.{/s} <br/><br/>' + displayText;
        }

        passwordPrompt.afterRender = Ext.MessageBox.afterRender;
        passwordPrompt.textField.inputType = 'password';

        passwordPrompt.prompt(
            '{s name=window/title}Password Validation{/s}',
            displayText,
            function (result, value) {
                if (result !== 'ok' || !value) {
                    abortCallback();
                    return;
                }

                Ext.Ajax.request({
                    url: '{url module=backend controller=Login action=validatePassword}',
                    params: {
                        password: value
                    },
                    success: function(response) {
                        var responseObject = JSON.parse(response.responseText);
                        if (responseObject.success === true) {
                            successCallback();
                        } else {
                            onFailure();
                        }
                    },
                    failure: function() {
                        onFailure();
                    }
                });
            }
        );
    }

});
