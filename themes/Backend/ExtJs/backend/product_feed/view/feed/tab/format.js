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
 * @package    ProductFeed
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/product_feed/view/feed}

/**
 * Shopware UI - Tab View.
 *
 * Displays all tab format Information
 */
//{block name="backend/product_feed/view/feed/tab/format"}
Ext.define('Shopware.apps.ProductFeed.view.feed.tab.Format', {
    extend:'Ext.container.Container',
    alias:'widget.product_feed-feed-tab-format',
    title:'{s name=tab/title/format}Format{/s}',
    border: 0,
    padding: 10,
    cls: 'shopware-toolbar',
    layout: 'anchor',
    defaults:{
        anchor:'100%',
        labelStyle:'font-weight: 700;',
        xtype:'combobox'
    },
    //Data for the Format Comboboxes
    encoding:[
        [1, 'ISO-8859-1'],
        [2, 'UTF-8']
    ],
    fileFormat:[
        [1, '{s name=detail_format/field/file_format_csv}CSV{/s}'],
        [2, '{s name=detail_format/field/file_format_txt_tab}TXT with tab delimiter{/s}'],
        [3, '{s name=detail_format/field/file_format_XML}XML{/s}'],
        [4, '{s name=detail_format/field/file_format_txt_pipe}TXT with pipe delimiter{/s}']
    ],
    /**
     * Initialize the Shopware.apps.ProductFeed.view.feed.detail and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;

        me.items = me.getItems();

        me.callParent(arguments);
    },
    /**
     * creates all fields for the general form on the left side
     */
    getItems:function () {
        var me = this;
        return [
            {
                name:'encodingId',
                fieldLabel:'{s name=detail_format/field/encoding}Encoding{/s}',
                store:new Ext.data.SimpleStore({
                    fields:['id', 'text'], data:me.encoding
                }),
                valueField:'id',
                displayField:'text',
                value:1,
                mode:'local',
                editable: false
            },
            {
                name:'formatId',
                fieldLabel:'{s name=detail_format/field/file_format}File format{/s}',
                store:new Ext.data.SimpleStore({
                    fields:['id', 'text'], data:me.fileFormat
                }),
                valueField:'id',
                displayField:'text',
                value:1,
                mode:'local',
                editable: false
            }
        ];
    }

});
//{/block}
