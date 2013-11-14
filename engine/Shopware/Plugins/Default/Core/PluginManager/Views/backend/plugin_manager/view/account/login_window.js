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
 * @package    Order
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/view/account/login_window"}
Ext.define('Shopware.apps.PluginManager.view.account.LoginWindow', {
    /**
     * Define that the plugin manager main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'plugin-manager-account-login-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.plugin-manager-account-login-window',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'fit',
    /**
     * Define window width
     * @integer
     */
    width:400,
    /**
     * Define window height
     * @integer
     */
    height:200,
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:false,
    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:false,

    /**
     * Don't create a footer button.
     * @boolean
     */
    footerBtn: false,

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-plugin-manager-account-login-window',

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=account/login_window/title}Plugin Manager - Login{/s}',

	snippets:{
		shop_id: '{s name=account/login_window/shop_id}Shopware-ID{/s}',
		password: '{s name=account/login_window/password}Password{/s}',
		forgot_password: '{s name=account/login_window/forgot_password}Forgot password?{/s}',
		forgot_shop_id: '{s name=account/login_window/forgot_shop_id}Forgot Shopware ID?{/s}',
		create_account: '{s name=account/login_window/create_account}Create a shopware account for this shop{/s}'
	},

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('login');

        me.formPanel = me.createFormPanel();
        me.items = me.formPanel;
        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items: ['->', {
                text: '{s name=account/login_window/cancel}Cancel{/s}',
                cls: 'secondary',
                handler: function() {
                    me.destroy();
                }
            }, {
                text: '{s name=account/login_window/login}Login{/s}',
                cls: 'primary',
                handler: function() {
                    me.fireEvent('login', me, me.formPanel, me.targetParams);
                }
            }]
        }];

        me.callParent(arguments);
    },

    /**
     * Creates the form panel which contains the file upload
     * component where the user could select a file for it's
     * local data system.
     *
     * @public
     * @return [object] Ext.form.Panel
     */
    createFormPanel: function() {
        var me = this, shopwareId = '';

        if (me.account) {
            shopwareId = me.account.get('shopwareID');
        }

        me.shopwareId = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.shop_id,
            allowBlank: false,
            value: shopwareId,
            name: 'shopwareID'
        });

        me.password = Ext.create('Ext.form.field.Text', {
            fieldLabel: me.snippets.password,
            name: 'password',
            allowBlank: false,
            inputType: 'password'
        });

        me.forgotPassword = Ext.create('Ext.container.Container', {
            html: '<a target="_blank" href="http://account.shopware.de/shopware.php/sViewport,LostPassword">'+me.snippets.forgot_password+'</a>',
            cls: Ext.baseCSSPrefix + 'forgot-password-field'
        });
        me.forgotShopwareId = Ext.create('Ext.container.Container', {
            html: '<a target="_blank" href="http://account.shopware.de/shopware.php/sViewport,LostShopwareId">'+me.snippets.forgot_shop_id+'</a>',
            cls: Ext.baseCSSPrefix + 'forgot-shopware-id-field'
        });
        me.createAccount = Ext.create('Ext.container.Container', {
            html: '<a target="_blank" href="http://account.shopware.de/shopware.php/sViewport,CreateShopwareId">'+me.snippets.create_account+'</a>',
            cls: Ext.baseCSSPrefix + 'create-account-field'
        });

        return Ext.create('Ext.form.Panel', {
            border: 0,
            layout: 'anchor',
            bodyPadding: 10,
            defaults: { anchor: '100%' },
            cls: 'shopware-form',
            items: [ me.shopwareId, me.password, me.forgotPassword, me.forgotShopwareId, me.createAccount ]
        });
    }
});
//{/block}