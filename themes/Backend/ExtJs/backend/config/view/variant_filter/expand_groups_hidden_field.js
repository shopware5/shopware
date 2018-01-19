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

//{block name="backend/config/view/variant_filter/expand_groups_hidden_field"}
Ext.define('Shopware.apps.Config.view.variantFilter.ExpandGroupsHiddenField', {
    extend: 'Ext.form.field.Hidden',

    alias: 'widget.variant-filter-expand-groups-hidden-field',

    /**
     * @type { Shopware.apps.Config.view.variantFilter.ExpandGroupsGrid }
     */
    groupsGrid: null,

    /**
     * @param { Object } values
     */
    setValue: function (values) {
        this.callParent(arguments);

        var groupGrid = this.getGroupsGrid();
        if (groupGrid === null) {
            return;
        }

        groupGrid.expandGroupIds = values;
    },

    /**
     * @returns { Shopware.apps.Config.view.variantFilter.ExpandGroupsGrid|null }
     */
    getGroupsGrid: function () {
        if (this.groupsGrid === null) {
            var panel = this.up('panel'),
                groupsGrid;

            if (!panel) {
                return null;
            }

            groupsGrid = panel.down('variant-filter-expand-group-grid');

            if (groupsGrid.length === 0) {
                return null;
            }

            this.groupsGrid = groupsGrid;
        }

        return this.groupsGrid;
    }
});
//{/block}
