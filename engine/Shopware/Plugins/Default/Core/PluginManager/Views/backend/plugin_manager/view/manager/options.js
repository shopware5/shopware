/**
 * Shopware 4
 * Copyright © shopware AG
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
 * @package    Plugin Manager
 * @subpackage View
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */

//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/view/manager/option/window"}
Ext.define('Shopware.apps.PluginManager.view.manager.Options', {
    /**
     * Define that the plugin manager main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'plugin-manager-options-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.plugin-manager-manager-options',
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
    width:480,
    /**
     * Define window height
     * @integer
     */
    height:150,
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
    stateId:'shopware-plugin-manager-manager-options-window',

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=manager/options/title}Plugin installed{/s}',

    bodyPadding: 10,

	snippets:{
		activate_plugin: '{s name=manager/options/activate_plugin}Activate plugin{/s}',
		configure_plugin: '{s name=manager/options/configure_plugin}Configure plugin{/s}',
		back: '{s name=manager/options/back}Back{/s}',
		successful_install: '{s name=manager/options/successful_install}The plugin [0] have been installed successfully. \n<br>\n<br>\nHow do you want to proceed?{/s}'
	},

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('activatePlugin', 'configurePlugin');

        me.items = me.createElements();
        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items: [
        /*{if {acl_is_allowed privilege=update}}*/
                {
                    xtype: 'button',
                    text: me.snippets.activate_plugin,
                    cls: 'secondary',
                    handler: function() {
                        me.fireEvent('activatePlugin', me);
                    }
                },
                {
                    xtype: 'button',
                    margin: '0 10',
                    cls: 'secondary',
                    text: me.snippets.configure_plugin,
                    handler: function() {
                        me.fireEvent('configurePlugin', me);
                    }
                },
        /*{/if}*/
                {
                    xtype: 'button',
                    text: me.snippets.back,
                    cls: 'secondary',
                    handler: function() {
                        me.destroy()
                    }
                }
            ]
        }];

        me.callParent(arguments);
    },

    createElements: function() {
        var me = this, text = '';

        text = '<center>' + me.snippets.successful_install + '</center>';
        text = Ext.String.format(text, me.record.get('label'));

        me.noticeContainer = Ext.create('Ext.container.Container', {
            html: text,
            style: 'color: #999; font-style: italic;'
        });

        return [ me.noticeContainer ];
    }
});
//{/block}
