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

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/theme/main}

//{block name="backend/theme/controller/settings"}

Ext.define('Shopware.apps.Theme.controller.Settings', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'listingWindow', selector: 'theme-list-window' }
    ],

    init: function () {
        var me = this;

        me.control({
            'theme-list-window': {
                'open-settings': me.openSettings
            }
        });

        Shopware.app.Application.on('settings-save-successfully', function(controller, result, window, record) {
            window.destroy();
        });

        me.callParent(arguments);
    },

    openSettings: function() {
        var me = this;

        Ext.create('Shopware.apps.Theme.model.Settings').reload({
            callback: function(record) {
                me.settingsWindow = me.getView('settings.Window').create({
                    record: record
                }).show();
            }
        });
    }
});

//{/block}
