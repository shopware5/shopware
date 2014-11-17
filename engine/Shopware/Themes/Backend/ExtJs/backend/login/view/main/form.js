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
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/login/view/main}

/**
 * Shopware UI - Login - Form View
 *
 * todo@all: Documentation
 */
//{block name="backend/login/view/main/form"}
Ext.define('Shopware.apps.Login.view.main.Form', {
    extend: 'Ext.form.Panel',
    plain: true,
    frame: false,
    border: false,
    alias: 'widget.login-main-form',
    bodyStyle: 'border-bottom-color: transparent',
    preventHeader: true,
    defaults: {
        labelWidth: 100,
        width: 370
    },

    /**
     * Initializes the view
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        if(Ext.ieVersion === 0 || Ext.ieVersion >= 9) {
            // Create the headline
            me.headline = Ext.create('Ext.container.Container', {
                html: '<h1>{s name=title/login}Login Shopware Backend{/s}</h1>'
            });

            // Username field
            me.userName = Ext.create('Ext.form.field.Text', {
                name: 'username',
                allowBlank: true,
                emptyText: '{s name=field/username}Username{/s}'
            });

            // Passwort field
            me.password = Ext.create('Ext.form.field.Text', {
                inputType: 'password',
                name: 'password',
                allowBlank: true,
                emptyText: '{s name=field/password}Password{/s}'
            });

            // Language switcher
            me.language = Ext.create('Ext.form.field.ComboBox', {
                type: 'remote',
                name: 'locale',
                store: me.localeStore,
                queryMode: 'local',
                emptyText: '{s name=field/locale/empty_text}Select other language...{/s}',
                displayField: 'name',
                valueField: 'id',
                cls: Ext.baseCSSPrefix + 'form-combo'
            });

            me.items = [ me.headline, me.userName, me.password, me.language ];

            //set the focus on the first textbox
            me.userName.focus(false, 125);

            me.dockedItems = [{
                xtype: 'toolbar',
                dock: 'bottom',
                ui: 'shopware-ui',
                cls: 'shopware-toolbar',
                style: 'background: transparent;box-shadow: none',
                items: ['->',{
                    xtype: 'button',
                    cls: 'primary',
                    text: '{s name=button/login}Login{/s}',
                    action: 'login',
                    margin: '0 48 0 0'
                }]
            }];
        } else {
            me.headline = Ext.create('Ext.container.Container', {
                html: '<h1>{s name=title/login}Login Shopware Backend{/s}</h1>'
            });

            me.items = [me.headline, {
                xtype: 'box',
                cls: Ext.baseCSSPrefix + 'ie-notice',
                html: me.getIEWarning()
            }];
        }

        me.callParent(arguments);

        // Show hint if the browser is not Google Chrome
        if(!Ext.isChrome) {
            me.chromeHint = Ext.create('Ext.container.Container', {
                cls: Ext.baseCSSPrefix + 'google-chrome-hint',
                html: me.getInfoTemplate().applyTemplate({
                    link: '<a href="http://www.google.com/chrome" target="_blank">Google Chrome</a>'
                })
            });
            me.add(me.chromeHint);
        }
    },

    getIEWarning: function() {
        return new Ext.Template(
            '<div class="inner">',
                '<h2 class="teaser">{s name=content/ie/teaser}{/s}</h2>',
                '<p>{s name=content/ie/text}{/s}</p>',
                '<ul class="browsers">',
                    '<li class="chrome"><a href="{s name=content/ie/link/chrome}http://www.google.com/chrome{/s}" target="_blank"></a></li>',
                    '<li class="firefox"><a href="{s name=content/ie/link/firefox}http://www.mozilla.org/de/firefox/new/{/s}" target="_blank"></a></li>',
                    '<li class="safari"><a href="{s name=content/ie/link/safari}http://www.apple.com/safari/{/s}" target="_blank"></a></li>',
                    '<li class="ie"><a href="{s name=content/ie/link/ie}http://windows.microsoft.com/de-DE/internet-explorer/downloads/ie{/s}" target="_blank"></a></li>',
                '</ul>',
            '</div>'
        )
    },

    getInfoTemplate: function() {
        return new Ext.Template(
            '<div class="inner">',
                '<a href="http://www.google.com/chrome" class="logo-chrome" target="_blank">&nbsp;</a>',
                '<div class="right-content">{s name=content/google_chrome_hint}For optimum browser performance we recommend using [link].{/s}</div>',
                '<div class="x-clear"></div>',
            '</div>'
        );
    }
});
//{/block}
