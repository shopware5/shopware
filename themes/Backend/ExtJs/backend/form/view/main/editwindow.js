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
 * @package    Form
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/form/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/form/view/main/editwindow"}
Ext.define('Shopware.apps.Form.view.main.Editwindow', {
    extend: 'Enlight.app.Window',
    alias : 'widget.form-main-editwindow',

    /*{if {acl_is_allowed privilege=createupdate}}*/
    title : '{s name=title_edit}Forms - Edit{/s}',
    /*{else}*/
    title : '{s name=title_details}Form details{/s}',
    /*{/if}*/

    layout: 'fit',
    height : '90%',
    width: 860,

    /**
     * Initialize the component
     * @return void
     */
    initComponent : function () {
        var me = this;

        me.items = [{
            xtype:'tabpanel',
            items:[{
                    xtype: 'form-main-formpanel',
                    record: me.formRecord,
                    shopStore: me.shopStore
                }, {
                    xtype: 'form-main-fieldgrid',
                    fieldStore: me.fieldStore,
                    disabled: true
                }
            ]
        }];

        me.callParent(arguments);
    }
});
//{/block}
