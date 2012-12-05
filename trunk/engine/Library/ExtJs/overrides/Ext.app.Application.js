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
 * @package    ExtJS
 * @subpackage Application
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     Mitchel Simoens https://github.com/mitchellsimoens
 * @author     $Author$
 */

/**
 * Ext.app.Application
 *
 * Override the default ext application
 * to add our sub application functionality
 */
Ext.override(Ext.app.Application, {

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
	addSubApplication: function(subapp) {

		subapp.app = this;

		this.addController(subapp, true);

		return subapp;
	}
});
