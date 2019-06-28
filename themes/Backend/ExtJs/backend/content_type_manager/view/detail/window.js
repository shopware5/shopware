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

// {namespace name="backend/content_type_manager/main"}
// {block name="backend/content_type_manager/view/detail/window"}
Ext.define('Shopware.apps.ContentTypeManager.view.detail.Window', {
    extend: 'Shopware.window.Detail',
    alias: 'widget.content-type-manager-detail-window',
    title : '{s name="detail/title"}{/s}',
    height: 700,
    width: 1000,

    /**
     * configure the window
     * @returns { Object }
     */
    configure: function () {
        return {
            hasOwnController: true,
            eventAlias: 'content-type-manager',
        }
    },

    createTabItems: function () {
        var parent = this.callParent(arguments);

        parent.push({
            xtype: 'content-type-manager-detail-fields',
            store: this.record.getFields(),
            fieldSelectionStore: this.subApp.getStore('Fields'),
            title: '{s name="detail/fields"}{/s}'
        });

        return parent;
    },
});
// {/block}
