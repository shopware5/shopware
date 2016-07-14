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
 * @package    Deprecated
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Deprecated Application
 *
 * This component represents our compability
 * layer for the deprecated modules
 * in shopware.
 *
 * It's contains methods to load the module
 * configuration from the server side,
 * creates the iframe within the window
 * and terminates if the window needs a
 * tabpanel or a toolbar with buttons
 */

//{namespace name=backend/deprecated/view/window}
//{block name="backend/deprecated/view/window"}
Ext.define('Shopware.apps.Deprecated.view.main.Window', {

	// Default configuration options
	extend: 'Enlight.app.Window',
	alias: 'widgets.swagwindow',
	maximizable: true,
	bodyBorder: 0,
	border: 0,
	layout: 'fit',

	// Custom configuration options
	moduleName: null,
	controllerName: null,
	actionName: 'index',
	requestConfig: {},
	moduleConfig: {},
	requestUrl: '{url action=include fullPath=false}',


	/**
	 * Initialize our special window component
	 */
	initComponent: function() {
        var me = this;

        me.items = [];

        me.callParent(me);
		if(me.moduleName && !me.controllerName) {
			me.loadConfiguration(me.moduleName);
		}

		if(!me.moduleName && me.controllerName) {
			me.loadControllerAction(me.controllerName);
		}

	},

	/**
	 * Load the configuation of the deprecated module
	 *
	 * @param moduleName
	 */
	loadConfiguration: function(moduleName) {
		var me = this,
			request = null;

		// Handling the passed options
		me.requestConfig.includeDir = moduleName;

		// Send AJAX request
		request = Ext.Ajax.request({
			url: me.requestUrl,
			params: me.requestConfig,
			timeout: 2000,
			async: false,
			success: function(response) {

				// We are getting a object as a string
				me.moduleConfig = Ext.decode(response.responseText);

				// Initialize the deprecated module
				me.initModule(moduleName);

			},
			failure: function(response) {
				//me.growl.open('Fehler aufgetreten', 'Das angegebene Modul kann nicht geladen werden');
			}
		});
	},

	/**
	 * Load a controller based module configuration from the
	 * server side. In comparison to the loadConfiguration methids
	 * needs this method the controller name of the module and
	 * the requested url needs to built up dynamically
	 *
	 * @param controllerName
	 */
	loadControllerAction: function(controllerName) {
		var me = this,
			request = null,
			params = {};

		params.target_action = this.actionName;

		request = Ext.Ajax.request({
			url: '{url controller=index}/' + controllerName + '/skeleton',
			params: params,
            async: false,
			success: function(response) {

				// We are getting a object as a string
				me.moduleConfig = Ext.decode(response.responseText);

				// Initialize the deprecated module
				me.initModule(controllerName, me.moduleConfig.init.url);

			},
			failure: function(response) {
				//me.growl.open('Fehler aufgetreten', 'Das angegebene Modul kann nicht geladen werden');
			}
		})
	},

	/**
	 * Initialize the deprecated module
	 *
	 * @param moduleName
	 */
	initModule: function(moduleName, actionUrl) {
		var me = this,
			config = me.moduleConfig.init,
			url = null,
            container;

        Ext.suspendLayouts();
		me.tabs = (me.moduleConfig.tabs) ? me.moduleConfig.tabs : null;
		me.btns = (me.moduleConfig.buttons) ? me.moduleConfig.buttons : null;

		// Set the basic window proportions
		me.width = ~~config.width || 'auto';
		me.height = ~~config.height || 'auto';
		me.minWidth = ~~config.minwidth || 'auto';
		me.minHeight = ~~config.minheight || 'auto';

		if(!config.width) { me.width = ~~config.minwidth; }
		if(!config.height) { me.height = ~~config.minheight; }

		me.title = config.title || '{s name="window/fallback_title"}Deprecated module{/s}';


		// Check how to load the request module
		switch(me.moduleConfig.init.loader) {
			case 'iframe':
			case 'iframe2':
			case 'action':
                // Build up the iframe url
                url = (actionUrl ? actionUrl : me.requestUrl + '?includeDir=' + escape(moduleName) + '&include=' + escape(config.url));
                break;
            case 'extern':
                url = config.url;
				break;
            case 'none':
			default:
				break;
		}


        if(!this.tabs) {
            container = Ext.create('Ext.panel.Panel', {
                // We need to hack the iframe to inject it into the Ext.window.Window
                html: '<ifr' + 'ame id="iframe-' + Ext.id() + '" border="0" src="'+ url +'" style="min-width: 100%; min-height: 100%"></ifr' + 'ame>'
            });
            me.add(container);
        } else {
            me.handleTabs();
        }
		//if(this.btns) { me.handleButtons(); }
        Ext.resumeLayouts(true);
	},

	/**
	 * Creates the necessary tabs if the module configuration
	 * needs them.
	 * Note that the tabs are placed in a Ext.tab.Panel
	 */
	handleTabs: function() {
		var me = this;

		me.tabPnl = Ext.create('Ext.tab.Panel', {
			activeTab: 0,
			bodyBorder: 0
		});
        me.add(me.tabPnl);

		var tabs = {};

		Ext.each(this.tabs, function(tab, idx) {

			// Refactor the content
			var content = tab.content,
                newTab;
			content = content.replace(/div/,"iframe");
			content = content.replace(/\<\/div\>/,"");
            content = content.replace(/http.*\/engine\/backend\/modules\//, me.requestUrl + '?include=');

			newTab = Ext.create('Ext.container.Container', {
				bodyBorder: 0,
				border: 0,
				title: tab.title,
				cls: tab.id,
				disabled: (~~(1 * tab.active)) ? false : true,
				html: content
			});
            me.tabPnl.setActiveTab(0);
			me.tabPnl.add(newTab);
			tabs[tab.id] = newTab;
		});
	},

	/**
	 * Creates the necessary buttons if the module configuration.
	 *
	 * Note that the buttons are placed as dockedItems in a
	 * Ext.toolbarToolbar
	 */
	handleButtons: function() {
		var me = this,
			toolbar = null;

		// Create toolbar which holds the button(s)
		toolbar = Ext.create('Ext.toolbar.Toolbar', {
			dock: 'bottom'
		});

		Ext.each(this.btns, function(button, idx) {

			// TODO - Bind event handler
			var btn = Ext.create('Ext.button.Button', {
				id: button.id || Ext.id(),
				text: button.title,
				scope: this,
				handler: function(el) {
					var active = me.tabPnl.getActiveTab();
					var iframe = active.body.dom.children[0];
					iframe.contentWindow.sWrapper(button.remotecall);
				}
			});
			toolbar.add(btn);
		});
        me.addDocked(toolbar);
	}
});
//{/block}
