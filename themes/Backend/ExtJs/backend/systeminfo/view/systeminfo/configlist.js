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
 * Shopware UI - Grid for the shopware-configs
 *
 * todo@all: Documentation
 *
 */
//{block name="backend/systeminfo/view/systeminfo/configlist"}
Ext.define('Shopware.apps.Systeminfo.view.systeminfo.Configlist', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.grid.Panel',

    ui: 'shopware-ui',

    /**
     * ID to access the component out of other components
     */
    id: 'systeminfo-main-configlist',

    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('systeminfo-main-configlist')
    * @string
    */
    alias: 'widget.systeminfo-main-configlist',
    /**
    * The window uses a border layout, so we need to set
    * a region for the grid panel
    * @string
    */
    region: 'center',
    autoScroll: true,
    /**
    * Set the used store. You just need to set the store name
    * due to the fact that the store is defined in the same
    * namespace
    * @string
    */
    store: 'Configs',

    initComponent: function(){
        this.columns = this.getColumns();

        var translations = [];
        translations['config'] = '{s name=systeminfo/groupingFeature_config}Settings{/s}';
        translations['core'] = '{s name=systeminfo/groupingFeature_core}General{/s}';
        translations['extension'] = '{s name=systeminfo/groupingFeature_extension}Extensions{/s}';
        translations['other'] = '{s name=systeminfo/groupingFeature_other}Other{/s}';

//        Row grouping
        this.groupingFeature = Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: Ext.create('Ext.XTemplate',
                '{literal}<div>{name:this.formatName}</div>{/literal}',
                {
                    formatName: function(name) {
                        return Ext.String.trim(translations[name]);
                    }
                })
        });
        this.features = [ this.groupingFeature ];

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
                header: '{s name=config_grid/column/name}Name{/s}',
                dataIndex: 'name',
                flex: 1
            },{
                header: '{s name=config_grid/column/required}Required{/s}',
                dataIndex: 'required',
                align: 'right',
                flex: 1
            },{
                header: '{s name=config_grid/column/version}Version{/s}',
                dataIndex: 'version',
                align: 'right',
                flex: 1,
                renderer: me.renderVersion
            },{
                header: '{s name=config_grid/column/status}Status{/s}',
                dataIndex: 'status',
                flex: 1,
                renderer: me.renderStatus
            }
        ];
        return columns;
    },

    /**
     * Function to render the status. 1 = a green tick, everything else = a red cross
     * @param value The value of the field
     */
    renderStatus: function(value, meta, record){
        if(value === 'ok') {
            return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-tick"></div>')
        } else if(value === 'warning') {
            return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-exclamation" title="' + record.get('notice')  + '"></div>')
        } else {
            return Ext.String.format('<div style="height: 16px; width: 16px" class="sprite-cross"></div>')
        }
    },

    /**
     * Function to render the version. "True" is displayed as 1 and "false" is displayed as 0
     * @param value The value of the field
     */
    renderVersion: function(value){
        if(value==true){
            return 1
        }else if(value==false){
            return 0
        }else{
            return value;
        }
    }
});
//{/block}
