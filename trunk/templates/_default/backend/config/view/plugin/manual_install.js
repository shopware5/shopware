/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @package    Order
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/config/view/plugin}
//{block name="backend/config/view/plugins/manual_upload"}
Ext.define('Shopware.apps.Config.view.plugin.ManualInstall', {
    /**
     * Define that the plugin manager main window is an extension of the enlight application window
     * @string
     */
    extend:'Enlight.app.Window',
    /**
     * Set base css class prefix and module individual css class for css styling
     * @string
     */
    cls:Ext.baseCSSPrefix + 'config-plugin-manual-upload',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.config-plugin-manual-install',
    /**
     * Set no border for the window
     * @boolean
     */
    border:false,
    /**
     * True to automatically show the component upon creation.
     * @boolean
     */
    autoShow:true,
    /**
     * Set border layout for the window
     * @string
     */
    layout:'fit',
    /**
     * Define window width
     * @integer
     */
    width:400,
    /**
     * Define window height
     * @integer
     */
    height:200,
    /**
     * True to display the 'maximize' tool button and allow the user to maximize the window, false to hide the button and disallow maximizing the window.
     * @boolean
     */
    maximizable:true,
    /**
     * True to display the 'minimize' tool button and allow the user to minimize the window, false to hide the button and disallow minimizing the window.
     * @boolean
     */
    minimizable:true,

    /**
     * Don't create a footer button.
     * @boolean
     */
    footerBtn: false,

    /**
     * A flag which causes the object to attempt to restore the state of internal properties from a saved state on startup.
     */
    stateful:true,
    /**
     * The unique id for this object to use for state management purposes.
     */
    stateId:'shopware-config-plugin-manual-install-window',

    /**
     * Title of the window.
     * @string
     */
    title: '{s name=manual_install/active}Install plugin manually{/s}',

	snippets:{
		manual_install:{
			cancel: '{s name=manual_install/cancel}Cancel{/s}',
			upload_plugin: '{s name=manual_install/upload_plugin}Upload plugin{/s}',
			select_plugin: '{s name=manual_install/select_plugin}Select plugin{/s}'
		}
	},

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('uploadPlugin');

        me.formPanel = me.createFormPanel();
        me.items = me.formPanel;
        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'bottom',
            items: ['->', {
                text: me.snippets.manual_install.cancel,
                cls: 'secondary',
                handler: function() {
                    me.destroy();
                }
            }, {
                text: me.snippets.manual_install.upload_plugin,
                cls: 'primary',
                handler: function(btn) {
                    me.fireEvent('uploadPlugin', me, me.formPanel, btn);
                }
            }]
        }];

        me.callParent(arguments);
    },

    /**
     * Creates the form panel which contains the file upload
     * component where the user could select a file for it's
     * local data system.
     *
     * @public
     * @return [object] Ext.form.Panel
     */
    createFormPanel: function() {
        var me = this;

        me.fileUpload = Ext.create('Ext.form.field.File', {
            fieldLabel: me.snippets.manual_install.upload_plugin,
            name: 'plugin',
            labelWidth: 125,
            allowBlank: false,
            buttonConfig: {
                cls: 'primary small',
                text: me.snippets.manual_install.select_plugin
            }
        });

        return Ext.create('Ext.form.Panel', {
            border: 0,
            layout: 'anchor',
            url: '{url controller="Plugin" action="upload"}',
            bodyPadding: 10,
            defaults: { anchor: '100%' },
            cls: 'shopware-form',
            items: [ me.noticeFieldset, me.fileUpload ]
        });
    }
});
//{/block}