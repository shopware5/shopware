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
 * @package    BetaFeedback
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/beta_feedback/view/main}

/**
 * Shopware UI - Beta Feedback Main Controller
 *
 * This file contains the business logic for the Beta Feedback module.
 */
//{block name="backend/beta_feedback/controller/main"}
Ext.define('Shopware.apps.BetaFeedback.controller.Main', {

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
        me.mainWindow = me.getView('main.Window').create();

	}
});
//{/block}
