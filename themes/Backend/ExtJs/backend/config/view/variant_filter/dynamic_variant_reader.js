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

//{block name="backend/config/view/variant_filter/dynamic_variant_reader"}
Ext.define('Shopware.apps.Config.view.variantFilter.DynamicVariantReader', {
    extend: 'Shopware.model.DynamicReader',

    /**
     * Will be assigned while creating this reader.
     *
     * @type { Shopware.apps.Config.view.variantFilter.ExpandGroupsGrid }
     */
    groupsGrid: null,

    readRecords: function (data) {
        var me = this;
        Ext.each(data.data, function (item) {
            var separator = me.groupsGrid.separator;

            item.expandGroup = false;
            if (!me.groupsGrid.expandGroupIds) {
                return;
            }

            if (me.groupsGrid.expandGroupIds.indexOf(separator + item.id + separator) !== -1) {
                item.expandGroup = true;
            }
        });

        return this.callParent(arguments);
    }
});
//{/block}
