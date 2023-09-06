/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */
Ext.define('Shopware.apps.SwagUpdate', {
    extend: 'Enlight.app.SubApplication',

    name:'Shopware.apps.SwagUpdate',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [ 'Main', 'Progress' ],
    stores: [ 'Changelog', 'Requirements', 'Plugins' ],
    models: [ 'Changelog', 'Requirement', 'Plugins' ],
    views: [ 'Window', 'Progress', 'NoUpdate', 'SecurityUpdateWindow' ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
});
