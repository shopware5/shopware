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
 * @package    Login
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Login - Login Controller
 *
 * todo@all: Documentation
 */
Ext.define('Shopware.apps.Login.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({

            // Event listener to submit the form using the "ENTER"-key
            'login-main-form textfield': {
                specialkey: function(field, event) {
                    var form = field.up('form'),
                        btn = form.down('button');

                    if(event.getKey() !== event.ENTER) {
                        return false;
                    }
                    me.onLogin(btn);
                }
            },
            'login-main-form button[action=login]': {
                click: me.onLogin
            }
        });

        this.mainWindow = this.getView('Main').create({
            items: [ this.getView('main.Form').create({
                localeStore: this.getStore('Locale')
            }) ]
        }).show();
    },

    /**
     * Event listener method which handles the login process
     *
     * @param [object] btn - pressed Ext.button.Button
     * @return void
     */
    onLogin: function(btn) {
        var me = this,
            win = btn.up('window'),
            formPnl = win.down('form'),
            form = formPnl.getForm(),
            values = form.getValues();

        if(!form.isValid() || !values.password.length || !values.username.length) {
            return false;
        }
        form.submit({
            url: '{url action=login}',
            waitMsg: '{s name=wait/message}Login...{/s}',
            success: function(form, action) {
                window.location.href = window.location.href;
            },
            failure: function(form, action) {
                var lockedUntil, message;
                if(action.result.lockedUntil) {
                    action.result.lockedUntil = new Date(action.result.lockedUntil);
                    message = "{s name=failure/locked_message}Der Account ist bis zum [lockedUntil:date] um [lockedUntil:date('H:i:s')] Uhr gesperrt.{/s}";
                    message = new Ext.Template(message);
                    message = message.applyTemplate(action.result);
                } else {
                    message = '{s name=failure/input_message}Bitte überprüfen Sie Ihre Eingabe und probieren es erneut.{/s}';
                }
                Ext.Msg.alert('{s name=failure/title}Login fehlgeschlagen{/s}', '{s name=failure/message}Ihr Login war nicht erfolgreich. {/s}' + message);
                return false;
            }
        });
    }
});
