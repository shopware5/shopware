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

//{namespace name=backend/index/controller/main}

/**
 * Ext.app.Application
 *
 * Override the default ext application
 * to add our sub application functionality
 */
//{block name="extjs/overrides/application"}
Ext.override(Ext.app.Application, {

    loadingMessage: '{s name=application/loading}Loading [0] ...{/s}',

	/**
	 * Adds a new controller to the application
	 *
	 * @param controller
	 * @param skipInit
	 */
	addController: function(controller, skipInit) {

		if (Ext.isDefined(controller.name)) {
			var name = controller.name;
			delete controller.name;

			controller.id = controller.id || name;

			controller = Ext.create(name, controller);
		}

		var me          = this,
			controllers = me.controllers;

		controllers.add(controller);


		if (!skipInit) {
			controller.init();
		}

		return controller;
	},

	/**
	 * Remove a controller from the application
	 *
	 * @param controller
	 * @param removeListeners
	 */
	removeController: function(controller, removeListeners) {
		removeListeners = removeListeners || true;

		var me          = this,
			controllers = me.controllers;

		controllers.remove(controller);

		if (removeListeners) {
			var bus = me.eventbus;

			bus.uncontrol([controller.id]);
		}
	},

	/**
	 * Adds a new sub application to the
	 * main application
	 *
	 * @param subapp
	 */
	addSubApplication: function(subapp, skipInit, fn, showLoadMask) {
		skipInit = skipInit === true;
		subapp.app = this;

        if(subapp.hasOwnProperty('showLoadMask')) {
            showLoadMask = subapp.showLoadMask;
        }

        showLoadMask = (showLoadMask === undefined) ? true : showLoadMask;
        if(showLoadMask) {
            this.moduleLoadMask = new Ext.LoadMask(Ext.getBody(), {
                msg: Ext.String.format(this.loadingMessage, (subapp.localizedName) ? subapp.localizedName : subapp.name),
                hideModal: true
            });
            this.moduleLoadMask.show();
        }

        fn = fn || Ext.emptyFn;
        Ext.require(subapp.name, Ext.bind(function() {
            this.addController(subapp, skipInit);
            fn(subapp);
        }, this));
	},

    /**
      * Helper method which returns all open windows.
      *
      * @private
      * @param [boolean] deprecated Wheather or not to include Shopware.apps.Deprecated.view.main.Window in the listing
      * @return [array] active windows
      */
    getActiveWindows: function(deprecated) {
        var activeWindows = [];

        if (deprecated === undefined) {
            deprecated = true;
        }

        Ext.each(Ext.WindowManager.zIndexStack, function (item) {
            if (typeof(item) !== 'undefined') {
                var className = item.$className;
                if ((className == 'Ext.window.Window' || className == 'Enlight.app.Window' || className == 'Ext.Window' || (deprecated && className == 'Shopware.apps.Deprecated.view.main.Window')) && className != "Ext.window.MessageBox") {
                    activeWindows.push(item);
                }

                className = item.alternateClassName;
                if ((className == 'Ext.window.Window' || className == 'Enlight.app.Window' || className == 'Ext.Window' || (deprecated && className == 'Shopware.apps.Deprecated.view.main.Window')) && className != "Ext.window.MessageBox") {
                    activeWindows.push(item);
                }
            }
        });

        return activeWindows;
    }
});
//{/block}