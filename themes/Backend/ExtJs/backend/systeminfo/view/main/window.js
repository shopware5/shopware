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
 * Shopware UI - General window systeminfo
 *
 * todo@all: Documentation
 */
//{block name="backend/systeminfo/view/main/window"}
Ext.define('Shopware.apps.Systeminfo.view.main.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window_title}System-Information{/s}',
    cls: Ext.baseCSSPrefix + 'systeminfo-window',
    alias: 'widget.systeminfo-main-window',
    autoShow: true,
    layout: 'fit',
    stateful:true,
    stateId:'shopware-systeminfo-window',
    height: '90%',
    width: 925,
    overflow: 'hidden',

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        var tabPanel = me.createTabPanel();

        me.items = [tabPanel];
        me.callParent(arguments);
    },

    /**
     * Creates the tabPanel
     * @return [Ext.tab.Panel]
     */
    createTabPanel: function(){
        var me = this;
        var tabPanel = Ext.create('Ext.tab.Panel', {
            items: [
                {
                    xtype: 'container',
                    overflowY: 'scroll',
                    title: '{s name=window/tabpanel/config_tab/title}Server-Configs{/s}',
                    items: [{
                        xtype: 'systeminfo-main-timezone'
                    },{
                        xtype: 'systeminfo-main-configlist'
                    }]
                },{
                    xtype: 'container',
                    overflowY: 'scroll',
                    title: '{s name=window/tabpanel/path_tab/title}Shopware-Paths{/s}',
                    items:[{
                        xtype: 'systeminfo-main-pathlist'
                    }]
                },{
                    xtype: 'container',
                    overflowY: 'scroll',
                    title: '{s name=window/tabpanel/file_tab/title}Shopware-Files{/s}',
                    items:[{
                        xtype: 'systeminfo-main-filelist'
                    }]
                },{
                    xtype: 'container',
                    overflowY: 'scroll',
                    title: '{s name=window/tabpanel/version_tab/title}Version-info{/s}',
                    items:[{
                        xtype: 'systeminfo-main-versionlist'
                    }]
                },{
                    xtype: 'container',
                    layout: 'fit',
                    title: '{s name=window/tabpanel/optimizer_tab/title}Optimizer{/s}',
                    items:[{
                        xtype: 'systeminfo-main-optimizerlist'
                    }]
                },{
                    xtype: 'container',
                    layout: 'fit',
                    overflowY: 'hidden',
                    title: '{s name=window/tabpanel/info_tab/title}PHP-Info{/s}',
                    items:[{
                        xtype: 'systeminfo-main-phpinfo'
                    }]
                }
            ]
        });

        return tabPanel;
    }
});
//{/block}
