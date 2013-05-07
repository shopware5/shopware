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
 * @package    Shopware_Config
 * @subpackage Config
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Performance backend module
 *
 * The settings controller handles the 'settings' tab
 */
//{block name="backend/performance/controller/settings"}
Ext.define('Shopware.apps.Performance.controller.Settings', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'settings', selector: 'performance-tabs-settings-main' },
    ],

    /**
     *
     */
    init: function () {
        var me = this;

        me.control({
            'performance-tabs-settings-main button[action=save-settings]': {
                click: function(button, event) {
                    me.onSave();
                }
            }
        });

        me.callParent(arguments);
    },

    /**
     * Callback function called when the users clicks the 'save' button on the settings form
     */
    onSave: function() {
        var me = this,
            settings = me.getSettings();


    }


});
//{/block}
