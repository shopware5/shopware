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

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/store/integrated_plugins_countries"}

Ext.define('Shopware.apps.FirstRunWizard.store.IntegratedPluginsCountries', {
    extend: 'Ext.data.Store',
    model : 'Shopware.apps.FirstRunWizard.model.IntegratedPluginsCountries',
    proxy: {
        type: 'ajax',
        url: '{url controller="FirstRunWizardPluginManager" action="getIntegratedPluginsCountries"}',
        reader: {
            type: 'json',
            root: 'data'
        }
    },
    listeners: {
        load: function (store) {
            store.insert(0, [{
                text: '{s name="recommendation/content/choose_country"}Please Choose{/s}',
                iso: null
            }]);
        }
    }
});

//{/block}
