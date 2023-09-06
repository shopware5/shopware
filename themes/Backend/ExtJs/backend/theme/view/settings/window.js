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

//{namespace name="backend/theme/main"}

//{block name="backend/theme/view/settings/window"}

Ext.define('Shopware.apps.Theme.view.settings.Window', {
    extend: 'Shopware.window.Detail',
    alias: 'widget.theme-settings-window',
    title: '{s name="settings_window"}System configuration{/s}',
    height: 365,
    width: 344,
    modal: true
});

//{/block}
