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
 *
 * @category   Shopware
 * @package    Systeminfo
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/systeminfo/view}

/**
 * Shopware UI - Grid for the shopware-files
 *
 * todo@all: Documentation
 */
//{block name="backend/systeminfo/view/systeminfo/filelist"}
Ext.define('Shopware.apps.Systeminfo.view.systeminfo.Filelist', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.grid.Panel',

    ui: 'shopware-ui',

    /**
     * ID to access the component out of other components
     */
    id: 'systeminfo-main-filelist',

    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('systeminfo-main-filelist')
    * @string
    */
    alias: 'widget.systeminfo-main-filelist',
    /**
    * The window uses a border layout, so we need to set
    * a region for the grid panel
    * @string
    */
    region: 'center',
    /**
    * The view needs to be scrollable
    * @string
    */
    autoScroll: true,

    border: 0,
    /**
    * Set the used store. You just need to set the store name
    * due to the fact that the store is defined in the same
    * namespace
    * @string
    */
    store: 'Files',

    /**
     * This function is called when the component is initiated
     * It creates the columns
     */
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
                header: '{s name=file_grid/column/name}Name{/s}',
                dataIndex: 'name',
                flex: 1
            },{
                header: '{s name=file_grid/column/required}Available{/s}',
                dataIndex: 'available',
                width: '65px',
                renderer: me.renderStatus
           },{
                header: '{s name=file_grid/column/status}Status{/s}',
                dataIndex: 'result',
                width: '65px',
                renderer: me.renderStatus
            }
        ];
        return columns;
    },

    /**
     * Function to render the status. 1 = a green tick, everything else = a red cross
     * @param value The value of the field
     */
    renderStatus: function(value){
        if(value==1){
            return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-tick"></div>')
        }else{
            return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-cross"></div>')
        }
    }
});
//{/block}
