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
 *
 * @category   Shopware
 * @package    Systeminfo
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/systeminfo/view"}

/**
 * Shopware UI - Grid for the plugin-versions
 *
 * todo@all: Documentation
 *
 */
//{block name="backend/systeminfo/view/systeminfo/versionlist"}
Ext.define('Shopware.apps.Systeminfo.view.systeminfo.Versionlist', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.grid.Panel',

    ui: 'shopware-ui',

    /**
     * ID to access the component out of other components
     */
    id: 'systeminfo-main-versionlist',

    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('systeminfo-main-versionlist')
    * @string
    */
    alias: 'widget.systeminfo-main-versionlist',
    /**
    * The window uses a border layout, so we need to set
    * a region for the grid panel
    * @string
    */
    region: 'center',

    border: 0,
    /**
    * The view needs to be scrollable
    * @string
    */
    autoScroll: true,
    header: false,
    /**
    * Set the used store. You just need to set the store name
    * due to the fact that the store is defined in the same
    * namespace
    * @string
    */
    store: 'Versions',

    initComponent: function(){
        this.columns = this.getColumns();
        this.callParent(arguments);
    },

    /**
     * Creates the columns
     * @return array columns Contains the columns
     */
    getColumns: function(){
        var me = this;

        var columns = [
            {
                header: '{s name="version_grid/column/name"}Name{/s}',
                dataIndex: 'name',
                flex: 1
            }, {
                header: '{s name="version_grid/column/version"}Version{/s}',
                dataIndex: 'version',
                align: 'right',
                flex: 1
            }
        ];
        return columns;
    }
});
//{/block}
