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

// {block name="backend/content_type/view/detail/window"}
Ext.define('Shopware.apps.{$controllerName}.view.detail.Window', {
    extend: 'Shopware.window.Detail',
    alias: 'widget.{$controllerName}-detail-window',
    title : '{$type->getName()|snippet:"name":$type->getSnippetNamespaceBackend()}',
    height: 600,
    width: 1000,

    /**
     * configure the window
     * @returns { Object }
     */
    configure: function () {
        return {
            translationKey: '{$type->getTableName()}'
        }
    }
});
// {/block}
