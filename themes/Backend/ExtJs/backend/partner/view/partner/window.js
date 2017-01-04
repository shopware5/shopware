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
 * @package    Partner
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/partner/view/partner}

/**
 * Shopware UI - Partner detail main window.
 *
 * Displays all Detail Partner Information
 */
//{block name="backend/partner/view/partner/window"}
Ext.define('Shopware.apps.Partner.view.partner.Window', {
    extend: 'Enlight.app.Window',
    title: '{s name=window/detail_title}Partner configuration{/s}',
    alias: 'widget.partner-partner-window',
    border: false,
    autoShow: true,
    layout: 'fit',
    height: 630,
    /**
     * Display no footer button for the detail window
     * @boolean
     */
    footerButton:false,
    width: 925,

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Create our form panel and assign it to the namespace for later usage
        me.formPanel = me.createFormPanel();
        me.items = me.formPanel;

        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            cls: 'shopware-toolbar',
            ui: 'shopware-ui',
            items: me.createFormButtons()
        }];
        me.callParent(arguments);
    },

    /**
     * creates the form panel
     */
    createFormPanel: function(){
        var me = this;
        return Ext.create('Ext.form.Panel', {
            layout: {
                type: 'vbox',
                align : 'stretch'
            },
            defaults: { flex: 1 },
            unstyled: true,
            items: [{
                xtype:'partner-partner-detail',
                record: me.record,
                style:'padding: 10px'
            }]
        });
    },
    /**
     * creates the form buttons cancel and save
     */
    createFormButtons: function(){
        var me = this;
        return ['->',
            {
                text:'{s name=detail_general/button/cancel}Cancel{/s}',
                cls: 'secondary',
                scope:me,
                handler:function () {
                    this.destroy();
                }
            },
            {
                text:'{s name=detail_general/button/save}Save{/s}',
                action:'save',
                cls:'primary'
            }
        ];
    }
});
//{/block}
