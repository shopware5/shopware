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
 * @package    Order
 * @subpackage View
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/view/detail/window"}
Ext.define('Shopware.apps.PluginManager.view.detail.Window', {
    /**
     * Define that the plugin manager main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'plugin-manager-detail-window',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.plugin-manager-detail-window',
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
    width:800,
    /**
     * Define window height
     * @integer
     */
    height:'90%',
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:false,
    /**
     * True to display the 'close' tool button and allow the user to close the window, false to hide the button and disallow closing the window.
     * @boolean
     */
    closable: true,
    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:false,
    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-plugin-manager-detail-window',

	snippets:{
		buy_install_plugin: '{s name=detail/window/buy_install_plugin}Buy & install plugin{/s}',
		detail_site: '{s name=detail/window/detail_site}Detail site{/s}',
		install_plugin: '{s name=detail/window/install_plugin}Install plugin{/s}',
		cancel: '{s name=detail/window/cancel}Cancel{/s}',
		save_plugin_settings: '{s name=detail/window/save_plugin_settings}Save plugin settings{/s}',
		description: '{s name=detail/window/description}Description{/s}',
		settings: '{s name=detail/window/settings}Settings{/s}',
		report_error: '{s name=detail/window/report_error}Report an error{/s}'
	},

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this, buttonText = me.snippets.buy_install_plugin;

        me.addEvents('installPlugin', 'saveConfiguration', 'pluginTabChanged');

        if(me.record && me.record.data && me.record.get('name')) {
            me.title = me.snippets.detail_site+' - ' + me.record.get('name');
        } else {
            me.title = me.snippets.detail_site+' - ' + me.plugin.get('label');
        }
        if (me.record && me.record.getDetail() && me.record.getDetail().first() && me.record.getDetail().first().get('price') === 0) {
            buttonText = me.snippets.install_plugin;
        }

        me.tabPanel = me.createTabPanel();
        me.items = me.tabPanel;

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items: ['->', {
                text: me.snippets.cancel,
                cls: 'secondary',
                handler: function() {
                    me.destroy();
                }
            }
        /*{if {acl_is_allowed privilege=install}}*/
			,	{
                text: me.flag === 'community' ? buttonText : me.snippets.save_plugin_settings,
                cls: 'primary',
                handler: function(button) {
                    me.fireEvent((me.flag === 'community' ? 'installPlugin' : 'saveConfiguration'), me, button)
                }
            }
        /*{/if}*/]
        }];

        me.callParent(arguments);
    },

    /**
     * Creates a tab panel which holds off the different sections
     * of the detail page.
     *
     * @public
     * @return [object] Ext.tab.Panel
     */
    createTabPanel: function() {
        var me = this;
        var tabs = [];

        me.productWrapper = null;

        /** {if $storeApiAvailable} */
        if(me.record && me.record.get('name')) {
            tabs.push({
                title: me.snippets.description,
                xtype: 'plugin-manager-detail-description',
                article: me.record,
                voteStore: me.voteStore
            });
        } else {
           me.productWrapper = Ext.create('Ext.container.Container', {
               title: me.snippets.description,
               plugin: me.plugin,
               name: 'product-wrapper',
               voteStore: me.voteStore,
               layout: 'fit'
           });
           tabs.push(me.productWrapper);
        }
        /** {/if} */
        tabs.push({
            title: me.snippets.settings,
            xtype: 'plugin-manager-detail-settings',
            plugin: me.plugin,
            disabled: me.flag === 'community',
            hidden: me.flag === 'community'
        });

        if(me.flag === 'community') {
            tabs.push({
               title: me.snippets.report_error,
               disabled: true
           });
        }

        return Ext.create('Ext.tab.Panel', {
            xtype: 'tabpanel',
            /** {if $storeApiAvailable} */
            activeTab: (me.flag === 'community' || (me.record && !me.record.get('name')) ? 0 : 1),
            /** {else} */
            activeTab: 0,
            /** {/if} */
            plain: true,
            items: tabs,
            listeners: {
                beforetabchange: function(tabPanel, newCard, oldCard, eOpts ) {
                    me.fireEvent('pluginTabChanged', me, tabPanel, newCard, oldCard);
                }
            }
        });
    }
});
//{/block}
