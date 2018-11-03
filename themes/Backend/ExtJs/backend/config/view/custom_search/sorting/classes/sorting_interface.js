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

Ext.define('Shopware.apps.Config.view.custom_search.sorting.classes.SortingInterface', {

    /**
     * @api Displayed in combo box selection
     * @return { string }
     */
    getLabel: function() {
        throw 'Unimplemented method.';
    },

    /**
     * @api called to create grid record for existing data set
     * @param { string } sortingClass
     * @param { object } parameters
     * @return { boolean }
     */
    supports: function(sortingClass, parameters) {
        throw 'Unimplemented method.';
    },

    /**
     * @api called to create grid record for existing data set
     * @param { string } sortingClass
     * @param { object } parameters
     * @param { function } callback({ object }) Expects an object as parameter which added to store: label, class, parameters are required in the object
     */
    load: function(sortingClass, parameters, callback) {
        throw 'Unimplemented method.';
    },

    /**
     * @api called to create a new record for the grid, display create window if parameters required.
     * @param { function } callback({ object }) Expects an object as parameter which added to store: label, class, parameters are required in the object
     */
    create: function(callback) {
        throw 'Unimplemented method.';
    }
});
