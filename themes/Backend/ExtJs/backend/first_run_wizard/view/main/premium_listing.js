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
 * Shopware First Run Wizard - Premium Plugins Tab
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/view/main/premium_listing"}
Ext.define('Shopware.apps.FirstRunWizard.view.main.PremiumListing', {
    extend: 'Shopware.apps.PluginManager.view.list.PremiumPluginsPage',
    createListing: function() {
        var listing = this.callParent(arguments);
        listing.width = 632;
        listing.padding = 0;
        return listing;
    },
    createFilterPanel: function() {
        var panel = this.callParent(arguments);
        Ext.each(panel.items.items, function(item) {
            item.padding = "0 0 15 0";
        });

        panel.padding = "0 0 5 0";

        return panel;
    }
});

//{/block}